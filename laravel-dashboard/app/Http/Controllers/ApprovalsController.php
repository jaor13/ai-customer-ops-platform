<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveDraftRequest;
use App\Http\Requests\RejectDraftRequest;
use App\Models\ApprovalQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalsController extends Controller
{
    /**
     * Display the approvals queue with the full split-view payload.
     */
    public function index(): Response
    {
        $hasAny = ApprovalQueue::exists();

        if (! $hasAny) {
            return Inertia::render('Approvals/Index', [
                'approvals' => $this->mockApprovals(),
                'demoMode' => true,
            ]);
        }

        $approvals = ApprovalQueue::with(['ticket.customer', 'customer', 'lead'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(
                fn (ApprovalQueue $item) => $item->type === 'lead_welcome'
                ? $this->mapLeadWelcome($item)
                : $this->mapTicketReply($item)
            );

        return Inertia::render('Approvals/Index', [
            'approvals' => $approvals,
            'demoMode' => false,
        ]);
    }

    /**
     * Shape a Phase 1 lead-welcome draft (no ticket — uses lead + the
     * recipient_email/subject columns written by the lead-capture workflow).
     */
    private function mapLeadWelcome(ApprovalQueue $item): array
    {
        $lead = $item->lead;
        $category = ucfirst(strtolower($lead?->category ?? 'lead'));

        $body = $lead
            ? "New inbound lead — the AI drafted a personalized welcome grounded in the knowledge base.\n\n"
            .'Company: '.($lead->company ?: '—')."\n"
            .'Lead score: '.($lead->score !== null ? $lead->score : '—')." ({$category})\n"
            .'Source: '.($lead->source ?: '—')
            : 'New lead welcome draft.';

        return [
            'id' => $item->id,
            'type' => 'lead_welcome',
            'customer_name' => $lead?->name ?? 'New Lead',
            'customer_email' => $item->recipient_email ?? $lead?->email ?? 'unknown@example.com',
            'subject' => $item->subject ?? 'Welcome',
            'body' => $body,
            'draft_body' => $item->draft_body,
            'edited_body' => $item->edited_body,
            'context_sources' => $item->context_sources ?? [],
            'priority' => $this->leadPriority($lead?->category),
            'category' => $category,
            'status' => $item->status,
            'created_at' => optional($item->created_at)->diffForHumans() ?? 'Just now',
        ];
    }

    /**
     * Shape a Phase 2 ticket-reply draft (derived from the related ticket).
     */
    private function mapTicketReply(ApprovalQueue $item): array
    {
        $customer = $item->customer ?? optional($item->ticket)->customer;

        return [
            'id' => $item->id,
            'type' => 'ticket_reply',
            'customer_name' => $customer?->name ?? 'Unknown',
            'customer_email' => $item->recipient_email ?? $customer?->email ?? 'unknown@example.com',
            'subject' => $item->subject ?? $item->ticket?->subject ?? 'Inquiry',
            'body' => $item->ticket?->body ?? 'No message body provided.',
            'draft_body' => $item->draft_body,
            'edited_body' => $item->edited_body,
            'context_sources' => $item->context_sources ?? [],
            'priority' => strtoupper($item->ticket?->priority ?? 'MEDIUM'),
            'category' => $item->ticket?->category ?? 'General',
            'status' => $item->status,
            'created_at' => optional($item->created_at)->diffForHumans() ?? 'Just now',
        ];
    }

    /**
     * Map a lead score-category (Hot/Warm/Cold) to a UI priority level.
     */
    private function leadPriority(?string $category): string
    {
        return match (strtolower((string) $category)) {
            'hot' => 'HIGH',
            'warm' => 'MEDIUM',
            'cold' => 'LOW',
            default => 'MEDIUM',
        };
    }

    /**
     * Approve a queued draft (optionally with edits).
     */
    public function approve(ApproveDraftRequest $request, ApprovalQueue $approval): RedirectResponse
    {
        $approval->update([
            'status' => 'approved',
            'edited_body' => $request->validated('edited_body'),
            'reviewed_by' => Auth::user()?->name ?? 'System',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Draft approved and queued for dispatch.');
    }

    /**
     * Reject a queued draft.
     */
    public function reject(RejectDraftRequest $request, ApprovalQueue $approval): RedirectResponse
    {
        $approval->update([
            'status' => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason') ?? 'Rejected by reviewer',
            'reviewed_by' => Auth::user()?->name ?? 'System',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Draft archived.');
    }

    private function mockApprovals(): array
    {
        return [
            [
                'id' => 101,
                'type' => 'ticket_reply',
                'customer_name' => 'Sarah Jenkins',
                'customer_email' => 'sarah.j@vaporscale.com',
                'subject' => 'API Limits & Custom Webhooks',
                'body' => "Hello AI Ops Team,\n\nDoes your platform support custom webhooks? We need to route ~10k emails a day and trigger downstream actions, with no latency on critical transaction notifications.",
                'draft_body' => "Hi Sarah,\n\nYes, we fully support custom webhooks. Our platform integrates with n8n and Laravel, allowing you to route 10k+ emails daily with sub-second latency. Would you like to schedule a quick demo this week to set up a test pipeline?\n\nBest,\nAI Ops Team",
                'edited_body' => null,
                'context_sources' => [
                    ['name' => 'API_Specs_v2.pdf', 'confidence' => '98%'],
                    ['name' => 'Webhook_Limits_Sheet.pdf', 'confidence' => '91%'],
                ],
                'priority' => 'HIGH',
                'category' => 'Sales',
                'status' => 'pending',
                'created_at' => '12 minutes ago',
            ],
            [
                'id' => 102,
                'type' => 'lead_welcome',
                'customer_name' => 'Marcus Thorne',
                'customer_email' => 'marcus.t@solosaas.io',
                'subject' => 'Pre-Seed Startup Discount Inquiry',
                'body' => 'Do you offer discounts for pre-seed startups? We are in the YC batch and trying to keep infra costs lean before our seed round.',
                'draft_body' => "Hi Marcus,\n\nThanks for reaching out! We offer a pre-seed startup tier with 40% off annual plans for the first year. Apply here: ai-ops.app/startups.\n\nWarm regards,\nAI Ops Team",
                'edited_body' => null,
                'context_sources' => [
                    ['name' => 'Pricing_Sheet.pdf', 'confidence' => '91%'],
                    ['name' => 'Startup_Program.pdf', 'confidence' => '85%'],
                ],
                'priority' => 'MEDIUM',
                'category' => 'Sales',
                'status' => 'pending',
                'created_at' => '1 hour ago',
            ],
            [
                'id' => 103,
                'type' => 'ticket_reply',
                'customer_name' => 'Dave Miller',
                'customer_email' => 'dave.miller@nexustech.org',
                'subject' => 'Database Sync Timeout error at midnight',
                'body' => 'Getting sync timeout errors at midnight during heavy load. Running Qdrant inside Docker on AWS EC2. Any recommendations?',
                'draft_body' => "Hi Dave,\n\nThis is typically the midnight cron schedule. Please check the DB connection pool limits and retry using our Qdrant sync script. Let us know if this resolves it.\n\nBest,\nAI Ops Support",
                'edited_body' => null,
                'context_sources' => [
                    ['name' => 'DB_Tuning_Guide.pdf', 'confidence' => '95%'],
                ],
                'priority' => 'HIGH',
                'category' => 'Technical',
                'status' => 'pending',
                'created_at' => '3 hours ago',
            ],
        ];
    }
}
