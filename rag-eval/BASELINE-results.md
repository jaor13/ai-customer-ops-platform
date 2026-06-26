# RAG Evaluation Report

_Generated 2026-06-26T15:37:10+00:00_

## Summary

| Metric | Value |
|---|---|
| Hit Rate @5 (Recall@5) | 100.0% |
| MRR | 0.958 |
| Precision @5 | 78.3% |
| Answer accuracy | 100.0% |
| Hallucination resistance | 100.0% |
| Stale-version leaks | 0 |
| Overall pass rate | 100.0% |
| Overall pass rate | 100.0% |
| Avg latency | 38.84s |
| Cases | 14 (12 retrieval, 2 negative) |

## Per-case results

| Case | Expected | Retrieved (top-k) | Hit | Rank | Answer | Result |
|---|---|---|---|---|---|---|
| pricing-starter | nexus_crm_pricing | nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| pricing-growth | nexus_crm_pricing | nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_faq, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| pricing-enterprise | nexus_crm_pricing | nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| refund-trial | nexus_crm_refund_policy | nexus_crm_refund_policy, nexus_crm_faq, nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| refund-eligibility | nexus_crm_refund_policy | nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_billing_sop, nexus_crm_refund_policy | ✅ | 1 | ✅ | PASS |
| refund-cancel-how | nexus_crm_refund_policy | nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_refund_policy, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| faq-data-export | nexus_crm_faq | nexus_crm_faq, nexus_crm_faq, nexus_crm_faq, nexus_crm_faq, nexus_crm_faq | ✅ | 1 | ✅ | PASS |
| faq-user-roles | nexus_crm_faq | nexus_crm_faq, nexus_crm_faq, nexus_crm_faq, nexus_crm_pricing, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| faq-integrations | nexus_crm_faq | nexus_crm_faq, nexus_crm_pricing, nexus_crm_faq, nexus_crm_pricing, nexus_crm_pricing | ✅ | 1 | ✅ | PASS |
| sop-billing-complaint | nexus_crm_billing_sop | nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_pricing, nexus_crm_billing_sop | ✅ | 1 | ✅ | PASS |
| sop-escalation | nexus_crm_billing_sop | nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_refund_policy | ✅ | 1 | ✅ | PASS |
| sop-credit | nexus_crm_billing_sop | nexus_crm_refund_policy, nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_billing_sop, nexus_crm_billing_sop | ✅ | 2 | ✅ | PASS |
| out-of-scope-weather | (none) | nexus_crm_faq, nexus_crm_refund_policy, nexus_crm_faq, nexus_crm_refund_policy, nexus_crm_pricing | - | - | ✅ | PASS |
| out-of-scope-sports | (none) | nexus_crm_pricing, nexus_crm_pricing, nexus_crm_pricing, nexus_crm_billing_sop, nexus_crm_faq | - | - | ✅ | PASS |
