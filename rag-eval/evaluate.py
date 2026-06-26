#!/usr/bin/env python3
"""
RAG evaluation harness for the AI Customer Operations Platform.

Sends a golden set of questions through the deployed RAG pipeline
(n8n "TEST - RAG Question Tester" webhook -> 03 Knowledge Base - Query
sub-workflow: hybrid dense+BM25 search, RRF fusion, cross-encoder rerank)
and measures retrieval quality with standard information-retrieval metrics:

  - Hit Rate @k (a.k.a. Recall@k for single-relevant-doc queries)
  - MRR  (Mean Reciprocal Rank)
  - Precision@k
  - Answer-correctness (lightweight lexical check on the generated answer)
  - Hallucination-resistance (negative/out-of-scope cases must refuse)

The harness is endpoint-agnostic: point it at any URL that accepts
{"question": "..."} and returns {"answer": "...", "sources_used": [...]}.

Usage:
    python evaluate.py --endpoint https://n8n.example.com/webhook/test-rag
    python evaluate.py --endpoint http://localhost:5678/webhook/test-rag --k 5
    python evaluate.py --golden golden_set.json --output-dir results

Environment variables (override CLI defaults):
    RAG_ENDPOINT   default endpoint URL
    RAG_SECRET     optional value sent as the X-Webhook-Secret header
"""

from __future__ import annotations

import argparse
import json
import os
import sys
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

try:
    import requests
except ImportError:  # pragma: no cover
    sys.exit("Missing dependency. Run:  pip install -r requirements.txt")


# --------------------------------------------------------------------------- #
# Source / matching helpers
# --------------------------------------------------------------------------- #
def extract_doc_keys(sources: list[dict[str, Any]]) -> list[str]:
    """Return the ordered list of doc_key values from the RAG sources array."""
    keys: list[str] = []
    for src in sources:
        key = src.get("doc_key") or src.get("docKey") or src.get("document_id")
        keys.append(str(key).lower() if key is not None else "")
    return keys


def key_matches(expected: list[str], actual: str) -> bool:
    """Case-insensitive substring match between an expected key and a returned doc_key."""
    actual = (actual or "").lower()
    for exp in expected:
        exp = (exp or "").lower()
        if exp and (exp in actual or actual in exp):
            return True
    return False


def first_relevant_rank(expected: list[str], retrieved: list[str]) -> int:
    """1-based rank of the first retrieved doc that matches an expected key, else 0."""
    for i, key in enumerate(retrieved, start=1):
        if key_matches(expected, key):
            return i
    return 0


def stale_versions(expected_keys: list[str], expected_version: int | None,
                   sources: list[dict[str, Any]]) -> list[int]:
    """Return the versions of any retrieved chunk that matches an expected doc_key
    but carries a version other than expected_version (i.e. superseded/stale content
    leaked past the active-version filter). Empty list = clean."""
    if expected_version is None:
        return []
    bad: list[int] = []
    for src in sources:
        key = str(src.get("doc_key") or src.get("document_id") or "")
        if key_matches(expected_keys, key):
            ver = src.get("version")
            if ver is not None and int(ver) != int(expected_version):
                bad.append(int(ver))
    return bad


def answer_check(answer: str, must_include: list[str], mode: str) -> bool:
    """Lexical answer-correctness check. mode='all' (default) or 'any'."""
    if not must_include:
        return True
    answer_l = (answer or "").lower()
    hits = [t for t in must_include if t.lower() in answer_l]
    return len(hits) == len(must_include) if mode == "all" else len(hits) > 0


# --------------------------------------------------------------------------- #
# Core evaluation
# --------------------------------------------------------------------------- #
def query_rag(endpoint: str, question: str, secret: str | None, timeout: int) -> dict[str, Any]:
    """POST a question to the RAG endpoint and return the parsed JSON response."""
    headers = {"Content-Type": "application/json"}
    if secret:
        headers["X-Webhook-Secret"] = secret
    resp = requests.post(endpoint, json={"question": question}, headers=headers, timeout=timeout)
    resp.raise_for_status()
    return resp.json()


def evaluate_case(case: dict[str, Any], endpoint: str, k: int, secret: str | None,
                  timeout: int) -> dict[str, Any]:
    """Run one golden case and compute its per-query metrics."""
    expected = [str(x) for x in case.get("expected_doc_keys", [])]
    is_negative = bool(case.get("is_negative", False))
    must_include = case.get("answer_must_include", [])
    match_mode = case.get("answer_match_mode", "all")
    expected_version = case.get("expected_version")

    started = time.time()
    error = None
    answer = ""
    sources: list[dict[str, Any]] = []
    retrieved: list[str] = []
    try:
        data = query_rag(endpoint, case["question"], secret, timeout)
        answer = data.get("answer", "") or ""
        sources = data.get("sources_used", []) or []
        retrieved = extract_doc_keys(sources)
    except Exception as exc:  # noqa: BLE001 - report any failure as a failed case
        error = str(exc)
    latency = round(time.time() - started, 2)

    topk = retrieved[:k]
    rank = first_relevant_rank(expected, topk)
    answer_ok = answer_check(answer, must_include, match_mode)
    stale = stale_versions(expected, expected_version, sources[:k])
    version_ok = len(stale) == 0

    if is_negative:
        # For out-of-scope questions, "good" = refused to answer (answer_ok True)
        # and ideally retrieved nothing relevant. Retrieval metrics are not scored.
        hit = None
        precision = None
        rr = None
        passed = answer_ok and error is None
    else:
        hit = rank > 0
        n_relevant = sum(1 for key in topk if key_matches(expected, key))
        precision = round(n_relevant / max(len(topk), 1), 3)
        rr = round(1.0 / rank, 3) if rank else 0.0
        passed = hit and answer_ok and version_ok and error is None

    return {
        "id": case.get("id", case["question"][:40]),
        "question": case["question"],
        "expected_doc_keys": expected,
        "expected_version": expected_version,
        "retrieved_doc_keys": topk,
        "is_negative": is_negative,
        "hit": hit,
        "rank": rank,
        "reciprocal_rank": rr,
        "precision_at_k": precision,
        "answer_ok": answer_ok,
        "version_ok": version_ok,
        "stale_versions": stale,
        "passed": passed,
        "latency_s": latency,
        "error": error,
        "answer_preview": (answer[:160] + "...") if len(answer) > 160 else answer,
    }


def aggregate(results: list[dict[str, Any]], k: int) -> dict[str, Any]:
    """Roll per-query results up into headline metrics."""
    positives = [r for r in results if not r["is_negative"]]
    negatives = [r for r in results if r["is_negative"]]

    n_pos = len(positives)
    hit_rate = sum(1 for r in positives if r["hit"]) / n_pos if n_pos else 0.0
    mrr = sum(r["reciprocal_rank"] for r in positives) / n_pos if n_pos else 0.0
    precision = sum(r["precision_at_k"] for r in positives) / n_pos if n_pos else 0.0
    answer_acc = sum(1 for r in positives if r["answer_ok"]) / n_pos if n_pos else 0.0

    n_neg = len(negatives)
    refusal_rate = sum(1 for r in negatives if r["answer_ok"]) / n_neg if n_neg else None

    n_all = len(results)
    overall_pass = sum(1 for r in results if r["passed"]) / n_all if n_all else 0.0
    latencies = [r["latency_s"] for r in results if r["error"] is None]
    avg_latency = round(sum(latencies) / len(latencies), 2) if latencies else None
    errors = sum(1 for r in results if r["error"])
    version_violations = sum(1 for r in positives if not r.get("version_ok", True))

    return {
        "k": k,
        "total_cases": n_all,
        "retrieval_cases": n_pos,
        "negative_cases": n_neg,
        "errors": errors,
        f"hit_rate_at_{k}": round(hit_rate, 3),
        "mrr": round(mrr, 3),
        f"precision_at_{k}": round(precision, 3),
        "answer_accuracy": round(answer_acc, 3),
        "hallucination_resistance": round(refusal_rate, 3) if refusal_rate is not None else None,
        "stale_version_violations": version_violations,
        "overall_pass_rate": round(overall_pass, 3),
        "avg_latency_s": avg_latency,
    }


# --------------------------------------------------------------------------- #
# Reporting
# --------------------------------------------------------------------------- #
def print_console(summary: dict[str, Any], results: list[dict[str, Any]], k: int) -> None:
    """Human-readable console report."""
    print("\n" + "=" * 70)
    print("  RAG EVALUATION RESULTS")
    print("=" * 70)
    print(f"  Hit Rate @{k} (Recall@{k}) : {summary[f'hit_rate_at_{k}']:.1%}")
    print(f"  MRR                       : {summary['mrr']:.3f}")
    print(f"  Precision @{k}             : {summary[f'precision_at_{k}']:.1%}")
    print(f"  Answer accuracy           : {summary['answer_accuracy']:.1%}")
    hr = summary["hallucination_resistance"]
    print(f"  Hallucination resistance  : {hr:.1%}" if hr is not None else
          "  Hallucination resistance  : n/a")
    print(f"  Overall pass rate         : {summary['overall_pass_rate']:.1%}")
    if summary.get("stale_version_violations"):
        print(f"  Stale-version leaks       : {summary['stale_version_violations']} "
              f"(superseded chunks reached retrieval!)")
    al = summary["avg_latency_s"]
    print(f"  Avg latency               : {al}s" if al is not None else
          "  Avg latency               : n/a")
    if summary["errors"]:
        print(f"  Errors (failed requests)  : {summary['errors']}")
    print("-" * 70)

    print(f"  {'CASE':<24}{'HIT':<5}{'RANK':<6}{'ANS':<5}{'RESULT'}")
    print("-" * 70)
    for r in results:
        hit = "-" if r["hit"] is None else ("yes" if r["hit"] else "NO")
        rank = r["rank"] if r["rank"] else "-"
        ans = "ok" if r["answer_ok"] else "NO"
        result = "PASS" if r["passed"] else ("ERR" if r["error"] else "FAIL")
        print(f"  {r['id'][:23]:<24}{hit:<5}{str(rank):<6}{ans:<5}{result}")
    print("=" * 70 + "\n")


def write_reports(summary: dict[str, Any], results: list[dict[str, Any]],
                  out_dir: Path, k: int) -> tuple[Path, Path]:
    """Persist machine-readable JSON and a human-readable markdown report."""
    out_dir.mkdir(parents=True, exist_ok=True)
    stamp = datetime.now(timezone.utc).strftime("%Y%m%d_%H%M%S")

    json_path = out_dir / f"eval_{stamp}.json"
    json_path.write_text(json.dumps(
        {"generated_at": stamp, "summary": summary, "results": results},
        indent=2), encoding="utf-8")

    md = [
        "# RAG Evaluation Report",
        "",
        f"_Generated {datetime.now(timezone.utc).isoformat(timespec='seconds')}_",
        "",
        "## Summary",
        "",
        "| Metric | Value |",
        "|---|---|",
        f"| Hit Rate @{k} (Recall@{k}) | {summary[f'hit_rate_at_{k}']:.1%} |",
        f"| MRR | {summary['mrr']:.3f} |",
        f"| Precision @{k} | {summary[f'precision_at_{k}']:.1%} |",
        f"| Answer accuracy | {summary['answer_accuracy']:.1%} |",
        f"| Hallucination resistance | "
        f"{summary['hallucination_resistance']:.1%} |" if summary['hallucination_resistance']
        is not None else "| Hallucination resistance | n/a |",
        f"| Stale-version leaks | {summary.get('stale_version_violations', 0)} |",
        f"| Overall pass rate | {summary['overall_pass_rate']:.1%} |",
        f"| Overall pass rate | {summary['overall_pass_rate']:.1%} |",
        f"| Avg latency | {summary['avg_latency_s']}s |",
        f"| Cases | {summary['total_cases']} "
        f"({summary['retrieval_cases']} retrieval, {summary['negative_cases']} negative) |",
        "",
        "## Per-case results",
        "",
        "| Case | Expected | Retrieved (top-k) | Hit | Rank | Answer | Result |",
        "|---|---|---|---|---|---|---|",
    ]
    for r in results:
        hit = "-" if r["hit"] is None else ("✅" if r["hit"] else "❌")
        rank = r["rank"] if r["rank"] else "-"
        ans = "✅" if r["answer_ok"] else "❌"
        res = "PASS" if r["passed"] else ("ERR" if r["error"] else "FAIL")
        exp = ", ".join(r["expected_doc_keys"]) or "(none)"
        got = ", ".join(r["retrieved_doc_keys"]) or "(none)"
        md.append(f"| {r['id']} | {exp} | {got} | {hit} | {rank} | {ans} | {res} |")
    md.append("")

    md_path = out_dir / f"eval_{stamp}.md"
    md_path.write_text("\n".join(md), encoding="utf-8")
    return json_path, md_path


# --------------------------------------------------------------------------- #
# CLI
# --------------------------------------------------------------------------- #
def main() -> int:
    parser = argparse.ArgumentParser(
        description="Evaluate the RAG pipeline against a golden question set.")
    parser.add_argument("--endpoint", default=os.getenv("RAG_ENDPOINT"),
                        help="RAG test webhook URL (or set RAG_ENDPOINT).")
    parser.add_argument("--golden", default="golden_set.json",
                        help="Path to the golden set JSON (default: golden_set.json).")
    parser.add_argument("--k", type=int, default=5,
                        help="Cutoff for Hit Rate / Precision @k (default: 5).")
    parser.add_argument("--output-dir", default="results",
                        help="Directory for JSON + markdown reports (default: results).")
    parser.add_argument("--secret", default=os.getenv("RAG_SECRET"),
                        help="Optional X-Webhook-Secret header value (or set RAG_SECRET).")
    parser.add_argument("--timeout", type=int, default=60,
                        help="Per-request timeout in seconds (default: 60).")
    parser.add_argument("--delay", type=float, default=0.0,
                        help="Seconds to sleep between requests. Use a few seconds "
                             "(e.g. 5) to avoid free-tier LLM rate limits.")
    parser.add_argument("--fail-under", type=float, default=None,
                        help="Exit non-zero if overall pass rate is below this "
                             "fraction (e.g. 0.8). Useful for CI.")
    args = parser.parse_args()

    if not args.endpoint:
        parser.error("No endpoint provided. Use --endpoint or set RAG_ENDPOINT.")

    golden_path = Path(args.golden)
    if not golden_path.exists():
        parser.error(f"Golden set not found: {golden_path}")
    golden = json.loads(golden_path.read_text(encoding="utf-8"))
    cases = golden.get("cases", [])
    if not cases:
        parser.error("Golden set has no 'cases'.")

    print(f"Evaluating {len(cases)} cases against {args.endpoint} (k={args.k}) ...")
    results = []
    for i, case in enumerate(cases, start=1):
        r = evaluate_case(case, args.endpoint, args.k, args.secret, args.timeout)
        flag = "PASS" if r["passed"] else ("ERR" if r["error"] else "FAIL")
        print(f"  [{i}/{len(cases)}] {r['id'][:32]:<34} {flag}")
        results.append(r)
        if args.delay and i < len(cases):
            time.sleep(args.delay)

    summary = aggregate(results, args.k)
    print_console(summary, results, args.k)
    json_path, md_path = write_reports(summary, results, Path(args.output_dir), args.k)
    print(f"Reports written:\n  {json_path}\n  {md_path}")

    if args.fail_under is not None and summary["overall_pass_rate"] < args.fail_under:
        print(f"\nFAILED: overall pass rate {summary['overall_pass_rate']:.1%} "
              f"< threshold {args.fail_under:.1%}")
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
