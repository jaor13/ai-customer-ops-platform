# RAG Evaluation Harness

A lightweight, dependency-minimal evaluation harness for the platform's
Retrieval-Augmented Generation pipeline. It runs a **golden question set**
through the deployed RAG endpoint and reports standard information-retrieval
metrics — turning *"the RAG feels accurate"* into measured, regression-catching numbers.

> Built to answer the question every RAG hiring manager asks: **"How do you know your retrieval is accurate?"**

## Why this exists

The retrieval layer is where most production RAG systems quietly fail: a re-chunk,
an embedding-model swap, or a document edit can drop recall without any error being
thrown. This harness is the safety net — a repeatable check you run after any change
to ingestion, chunking, the embedding model, fusion weights, or the reranker.

## What it measures

| Metric | What it tells you |
|---|---|
| **Hit Rate @k (Recall@k)** | Did the correct source document appear in the top-k retrieved chunks? |
| **MRR** (Mean Reciprocal Rank) | How highly was the first correct source ranked? (rewards good ordering) |
| **Precision@k** | What fraction of the top-k chunks were from the right document? |
| **Answer accuracy** | Lightweight lexical check that the generated answer contains the expected facts |
| **Hallucination resistance** | For out-of-scope questions, did the system correctly *refuse* instead of inventing an answer? |
| **Stale-version leaks** | Did any **superseded** document version slip past the `status=active` / version filter? (versioning-correctness guard) |
| **Avg latency** | End-to-end query latency (includes hybrid search + RRF + reranking) |

The **stale-version** and **hallucination-resistance** checks are the ones that
matter most for this system specifically — they verify the two hardest production
RAG problems (document rot and confident wrong answers) are actually handled.

## How it works

```
golden_set.json ──► evaluate.py ──HTTP POST {question}──► n8n "TEST - RAG Question Tester"
                                                              │
                                                              ▼
                                          03 Knowledge Base - Query sub-workflow
                                   (Ollama dense + BM25 sparse → RRF fusion → rerank)
                                                              │
        metrics ◄── parse {answer, sources_used[]} ◄─────────┘
```

The harness only depends on the endpoint contract `{question} -> {answer, sources_used[]}`,
so it works against the n8n test webhook, a local tunnel, or any compatible endpoint.

## Setup

```bash
cd rag-eval
python -m venv .venv && .venv\Scripts\activate   # Windows
# source .venv/bin/activate                       # macOS/Linux
pip install -r requirements.txt
```

## Usage

```bash
# Point at your RAG tester webhook
python evaluate.py --endpoint https://<your-n8n-host>/webhook/test-rag

# Or set it once via environment variable
set RAG_ENDPOINT=https://<your-n8n-host>/webhook/test-rag   # Windows
python evaluate.py

# Use in CI: exit non-zero if quality drops below 80%
python evaluate.py --fail-under 0.8
```

### Options

| Flag | Default | Purpose |
|---|---|---|
| `--endpoint` | `$RAG_ENDPOINT` | RAG test webhook URL |
| `--golden` | `golden_set.json` | Path to the golden question set |
| `--k` | `5` | Cutoff for Hit Rate / Precision @k |
| `--output-dir` | `results` | Where JSON + markdown reports are written |
| `--secret` | `$RAG_SECRET` | Optional `X-Webhook-Secret` header value |
| `--timeout` | `60` | Per-request timeout (s). The reranker cold-starts ~30s on first call |
| `--fail-under` | _none_ | Exit non-zero if overall pass rate is below this fraction (for CI) |

## The golden set

`golden_set.json` holds the labelled cases. Each case maps a question to the
`doc_key`(s) that *should* ground the answer:

```json
{
  "id": "pricing-growth",
  "question": "What is the price of the Growth tier?",
  "expected_doc_keys": ["nexus_crm_pricing"],
  "expected_version": 2,
  "answer_must_include": ["149"]
}
```

- **`expected_version`** — if set, the harness fails the case when a chunk from a
  *different* version of that document is retrieved. This directly tests that
  re-uploading a document (v1 → v2) correctly supersedes the old version.
- **Negative cases** (`"is_negative": true`, empty `expected_doc_keys`) assert the
  system refuses to answer out-of-scope questions instead of hallucinating.

The cases ship pre-aligned to the **fictional "Nexus CRM" sample knowledge base**
used for this portfolio project (`nexus_crm_pricing`, `nexus_crm_faq`,
`nexus_crm_refund_policy`, `nexus_crm_billing_sop`) — public, made-up demo content,
not real or customer data. Edit `expected_doc_keys` if your
`knowledge_documents.doc_key` values differ. Matching is case-insensitive and substring-based.

## Output

Each run prints a console summary and writes two timestamped files to `results/`:

- `eval_<timestamp>.json` — full machine-readable results (for trend tracking / CI)
- `eval_<timestamp>.md` — a human-readable report with a per-case table

`results/` is git-ignored by default; commit a baseline report if you want to
track quality over time.

## Notes

- This is a **retrieval-quality** harness (the layer that usually fails), with a
  lightweight lexical answer check. For full generation-quality scoring you can
  layer an LLM-as-judge or a framework like RAGAS / TruLens on top of the same
  golden set — the `raw_context` and `answer` are already returned by the endpoint.
- Keep the golden set small and high-signal (15–40 cases). Its job is to catch
  regressions fast, not to be exhaustive.
