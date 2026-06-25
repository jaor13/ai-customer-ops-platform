# n8n Workflows

This project's automation layer runs on **n8n**. Eight workflows handle lead capture, AI scoring, email triage, knowledge-base ingestion and retrieval, outbound sending, and monitoring. The exported JSON in this folder can be imported into any n8n instance (**Import from File**) to inspect the node graphs.

**Design principle:** n8n is a *stateless* automation/ingestion worker. The Laravel app owns the document lifecycle and is the sole writer to PostgreSQL. Qdrant holds a filterable vector mirror; PostgreSQL is the source of truth.

---

## 1. Lead Capture — Website Form
**Trigger:** Webhook `POST /lead`
Captures a website lead, deduplicates by email, then runs it through AI scoring and a knowledge-grounded welcome draft. Calls the **Lead Scoring** sub-workflow (score + Hot/Warm/Cold category), then the **Knowledge Base — Query** sub-workflow to ground a personalized welcome email, and queues that draft for human approval. Responds to the form immediately and finishes the AI work in the background.

## 2. Lead Capture — Lead Scoring (sub-workflow)
**Trigger:** Execute Workflow
Scores a lead 1–100 and assigns a Hot/Warm/Cold category from company, source, and contact completeness using a JSON-mode LLM call. Returns the result to the caller.

## 3. Email Triage — Incoming Gmail
**Trigger:** Gmail Trigger (polling)
Classifies each inbound email's **category** and **priority**, looks up or creates the customer, opens a ticket, and drafts a reply grounded in the knowledge base (via the Query sub-workflow). The draft is queued for human approval as a `ticket_reply`. Falls back gracefully if retrieval returns nothing.

## 4. Knowledge Base — Ingestion
**Trigger:** Webhook `POST /ingest-document` (Header Auth)
Receives already-extracted document text + metadata from the Laravel app, chunks it, generates a **dense embedding** (self-hosted Ollama) and a **BM25 sparse vector**, and upserts both into Qdrant as named vectors with a metadata mirror. Stateless — returns chunk counts and point IDs for Laravel to record.

## 5. Knowledge Base — Query (sub-workflow)
**Trigger:** Execute Workflow
The retrieval core. Embeds the query (dense) and encodes it (sparse), runs Qdrant's **hybrid Query API** (dense + sparse prefetch fused with Reciprocal Rank Fusion, filtered to active documents), then **reranks** the candidates with a self-hosted cross-encoder (`bge-reranker-v2-m3`). Applies an authority tiebreak, effective-date guard, de-duplication, and a token budget before returning the top grounded context + sources.

## 6. Approval — Send Approved Emails
**Trigger:** Schedule
Sends human-approved drafts (both lead-welcome and ticket-reply types) via Gmail, marks them sent, logs the send, and advances the linked ticket's status (`open → pending_response`). One sender handles both draft types via the unified approval-queue columns.

## 7. Ops — Health Check & Alert
**Trigger:** Schedule
Periodically checks Qdrant, Ollama, PostgreSQL, and the count of failed ingestions, and emails an alert if anything is unhealthy. Each check is fail-isolated so one failure never halts the run.

## 8. TEST — RAG Question Tester
**Trigger:** Webhook `POST /test-rag`
A debugging helper: sends a question through the Query sub-workflow and an LLM, returning the answer plus the exact sources used (document, version, category, score). Used to validate retrieval quality.

---

## Key techniques demonstrated

- **Hybrid retrieval** — dense (semantic) + sparse (BM25 keyword) vectors fused with RRF, so exact tokens like prices and SKUs aren't missed.
- **Cross-encoder reranking** — a self-hosted reranker reorders candidates by true query relevance, fail-open so retrieval never breaks.
- **Grounded generation + human-in-the-loop** — every AI draft is reviewed before it sends.
- **Versioning & authority** — active-only retrieval and authority weighting keep stale or unofficial content from surfacing.
- **Separation of concerns** — stateless n8n workers, a single source-of-truth database owned by the app.
