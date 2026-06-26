<?php

namespace App\Http\Middleware;

use App\Models\ApprovalQueue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Note: pendingApprovalsCount uses a closure so it is only resolved
     * when actually needed (Inertia lazy evaluation), and recomputed on
     * every full page visit so the sidebar badge stays accurate.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'pendingApprovalsCount' => fn () => $request->user()
                ? ApprovalQueue::where('status', 'pending')->count()
                : 0,
            'notifications' => fn () => $request->user()
                ? $this->notifications()
                : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * A compact, actionable notification feed for the header bell:
     * pending review drafts + recently opened urgent tickets, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function notifications(): array
    {
        $approvals = ApprovalQueue::where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'recipient_email', 'subject', 'created_at'])
            ->map(fn (ApprovalQueue $a) => [
                'id' => 'approval-'.$a->id,
                'type' => 'approval',
                'title' => 'Draft awaiting review',
                'detail' => $a->subject ?: ($a->recipient_email ?? 'Pending draft'),
                'route' => 'approvals.index',
                'time' => optional($a->created_at)->diffForHumans() ?? 'just now',
                'sort' => optional($a->created_at)->timestamp ?? 0,
            ]);

        $tickets = Ticket::whereIn('priority', ['HIGH', 'CRITICAL'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'subject', 'priority', 'created_at'])
            ->map(fn (Ticket $t) => [
                'id' => 'ticket-'.$t->id,
                'type' => 'ticket',
                'title' => strtoupper($t->priority).' ticket opened',
                'detail' => $t->subject ?: 'New ticket',
                'route' => 'tickets.index',
                'time' => optional($t->created_at)->diffForHumans() ?? 'just now',
                'sort' => optional($t->created_at)->timestamp ?? 0,
            ]);

        return $approvals->concat($tickets)
            ->sortByDesc('sort')
            ->take(6)
            ->values()
            ->map(fn ($n) => collect($n)->except('sort')->all())
            ->all();
    }
}
