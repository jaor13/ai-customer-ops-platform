# AI Customer Operations Platform

An AI-powered customer operations stack that captures leads, triages inbound email, drafts knowledge-grounded replies, and keeps a human in the loop for every outbound message — all from a single dashboard.

> **About this project**
> I built this on my own to go deep on **n8n workflow automation** and **production-grade RAG** (retrieval-augmented generation) — not just a toy "embed → search → prompt" demo, but the harder parts: hybrid retrieval, cross-encoder reranking, document versioning, authority ranking, and self-hosted models across two servers. It's a learning project and portfolio piece, so the goal was to make engineering decisions the way a real team would, and to document the *why* behind each one.

---

## Why it's more than a "simple RAG"

Most RAG demos stop at: chunk a document, embed it, do a vector top-K, stuff it into a prompt. That breaks quickly on real content. This system addresses the failure modes that actually bite in production:

| Problem with naive RAG | What this project does |
|---|---|
| Vector search misses exact tokens (prices like `$149`, SKUs, plan names) | **Hybrid search** — dense vectors **+** BM25 sparse vectors fused with Reciprocal Rank Fusion (RRF) in Qdrant's Query API |
| Top-K by cosine similarity is a weak ranking | **Cross-encoder reranking** — a self-hosted `bge-reranker-v2-m3` re-reads the query against each candidate and reorders by true relevance |
| Old/edited documents silently pollute answers forever | **Versioning + active-only retrieval** — re-uploading a doc supersedes the old version; retrieval filters `status = active` |
| Two similar docs, which one wins? | **Authority weighting** — official sources outrank casual ones even at slightly lower similarity |
| Long chunks overflow the model context | **Token-budget guard** — context is capped and de-duplicated before it reaches the LLM |
| The AI can confidently send a wrong answer | **Human-in-the-loop** — every draft is reviewed/edited/approved by a person before it sends |

The retrieval pipeline, end to end:

```
query
  ├─► Ollama  (nomic-embed-text, 768-dim dense vector)   ── VPS 2
  └─► BM25 sparse encoder (FastEmbed)                      ── VPS 1
        │
        ▼
   Qdrant Query API  →  prefetch(dense) + prefetch(sparse)  →  RRF fusion
        │              (filter: status = active, top-20)
        ▼
   bge-reranker-v2-m3 via llama.cpp /v1/rerank  ── VPS 2   →  top-5 (fail-open)
        │
        ▼
   authority tiebreak + effective-date guard + dedup + token budget
        │
        ▼
   grounded context → LLM draft → human approval → send
```

---

## Evaluation

Retrieval quality is **measured, not assumed.** A small Python evaluation harness
(`rag-eval/`) runs a labelled golden set of questions through the live pipeline and
reports standard information-retrieval metrics — the safety net that catches a quality
regression after any change to chunking, the embedding model, fusion, or the reranker.

Latest run (14 cases against the deployed system, all passing):

| Metric | Result |
|---|---|
| Hit Rate @5 (Recall@5) | 100% |
| MRR | 0.96 |
| Precision @5 | 78% |
| Answer accuracy | 100% |
| Hallucination resistance (out-of-scope refusals) | 100% |
| Stale-version leaks | 0 |

Beyond raw recall, the harness guards the two hardest production-RAG failure modes:

- **Versioning correctness** — pricing cases assert `version = 2`; the run confirmed
  **zero superseded v1 chunks** reached retrieval after a v1→v2 supersede.
- **Hallucination resistance** — out-of-scope questions (e.g. "what's the weather in
  Tokyo?") must be **refused**, not answered. 100% refusal in the latest run.

During development the harness immediately earned its keep: it flagged a pricing case
whose expected answer was the **old V1 price ($49)** while the system correctly returned
the **current V2 price ($69)** — catching stale test data and proving the supersede
pipeline works end to end. See [`rag-eval/`](rag-eval/) for metric definitions and usage.

---

## Architecture

Two VPS, fully self-hosted models (zero per-call AI cost for embeddings & reranking):

- **VPS 1 (app server):** Laravel dashboard, n8n, PostgreSQL, Qdrant, the BM25 sparse encoder, Nginx + SSL.
- **VPS 2 (inference server):** Ollama (embeddings) + a llama.cpp reranker. Ports firewalled so only VPS 1 can reach them.

```
                        ┌──────────────────────── VPS 1 (app) ────────────────────────┐
  Website form ──POST──►│  n8n  ──►  PostgreSQL                                         │
  Gmail inbox  ──poll──►│   │         ▲                                                 │
                        │   │         │      Laravel (Inertia + Vue) ──► Nginx/SSL ─────┼──► Dashboard
                        │   ├──► Qdrant (hybrid vectors)                                │
                        │   └──► BM25 sparse encoder (FastAPI)                          │
                        └───────────────┬───────────────────────────┬─────────────────┘
                                        │ embeddings                 │ rerank
                              ┌─────────▼───────── VPS 2 (inference) ─▼─────────┐
                              │  Ollama (nomic-embed-text)   llama.cpp (bge)    │
                              └─────────────────────────────────────────────────┘
```

The division of responsibility is deliberate: **n8n is a stateless ingestion/automation worker; Laravel owns the document lifecycle and is the sole writer to PostgreSQL.** Qdrant stores a filterable metadata mirror per chunk; PostgreSQL is the source of truth.

---

## Features

- **Lead capture & scoring** — website form → dedupe → AI lead score (Hot/Warm/Cold) → personalized, KB-grounded welcome draft.
- **Email triage** — Gmail trigger → AI category + priority classification → customer lookup/create → ticket → RAG-grounded reply draft.
- **Knowledge base (RAG)** — upload PDF/DOCX/TXT/MD (with OCR fallback), versioned and authority-ranked, served via hybrid search + reranking.
- **Approvals queue** — review, edit, approve, or reject every AI draft. Shows the exact source chunks used (doc / version / category / score).
- **Operations dashboard** — real-time stats, 7-day lead chart, recent-activity feed, per-customer activity timeline.
- **Live UI** — list pages and the notification bell refresh automatically (Inertia polling, tab-aware) without manual reloads.

## Tech stack

| Layer | Technology |
|-------|-----------|
| Dashboard | Laravel 13 (PHP 8.4) + Inertia.js v2 + Vue 3 + Tailwind CSS |
| Workflow engine | n8n (self-hosted) |
| Database | PostgreSQL 15 |
| Vector store | Qdrant 1.18 (named dense + sparse vectors, RRF fusion) |
| Embeddings | Ollama `nomic-embed-text` (768-dim), self-hosted |
| Sparse vectors | FastEmbed BM25 (FastAPI microservice) |
| Reranker | `bge-reranker-v2-m3` (GGUF) served by llama.cpp `--reranking` |
| Chat completions | OpenRouter (free-tier models, e.g. `gpt-oss-120b:free`) |
| Deployment | Docker Compose on DigitalOcean (2 VPS) |

## Project structure

```
├── laravel-dashboard/   # Main application (Laravel 13 + Inertia + Vue)
├── docker/              # Docker Compose, Nginx, Dockerfile, sparse-encoder, Ollama+reranker
└── README.md
```

## Getting started (local)

```bash
cd laravel-dashboard

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

# two terminals:
php artisan serve
npm run dev
```

### Docker deployment

```bash
cd docker
cp .env.example .env     # fill in real values (never commit this file)
docker compose up -d
```

The VPS 2 inference services (Ollama + reranker) are deployed from `docker/ollama/docker-compose.yml`.

## Configuration

All secrets live in `.env` files (git-ignored) and in n8n's encrypted credential store — **never** in the repo. Copy the provided `.env.example` templates and fill in your own values:

| Variable | Purpose |
|----------|---------|
| `POSTGRES_USER` / `POSTGRES_PASSWORD` | Database credentials |
| `LARAVEL_APP_KEY` | Laravel app key (`php artisan key:generate`) |
| `OPENROUTER_API_KEY` | Chat completions (entered as an n8n credential) |
| `OLLAMA_HOST` | Embeddings endpoint (VPS 2, firewalled to VPS 1) |
| `QDRANT_HOST` | Qdrant endpoint (internal Docker network) |
| `N8N_INGEST_URL` / `N8N_WEBHOOK_SECRET` | Authenticated ingestion webhook |

## What I learned building this

- **Hybrid retrieval & RRF** — why dense vectors alone fail on exact terms, and how sparse (BM25) + dense fusion fixes it.
- **Cross-encoders vs bi-encoders** — that a reranker is *not* an embedding model, why Ollama can't serve one, and how to serve a GGUF reranker with llama.cpp's `/v1/rerank` instead.
- **Safe infra upgrades** — migrating Qdrant across multiple major versions (RocksDB → Gridstore) without losing data, using volume checks + snapshots.
- **RAG data hygiene** — document versioning, authority weighting, active-only filtering, and effective-date guards to stop a knowledge base from quietly rotting.
- **Workflow design** — keeping n8n stateless and giving one system (Laravel) ownership of the source of truth to avoid race conditions.
- **Human-in-the-loop UX** — designing an approval flow that's fast to review but impossible to skip.

## Scope & honesty notes

This is a **portfolio / learning project**, not a commercial product:

- Runs on free-tier OpenRouter chat models (swappable for paid models with one config change).
- Single-tenant; no multi-tenant isolation.
- Outbound email and the Gmail trigger run against a dedicated test inbox.
- Built to demonstrate the engineering, not to be sold as-is.

## License

Personal portfolio project. Not licensed for redistribution.
