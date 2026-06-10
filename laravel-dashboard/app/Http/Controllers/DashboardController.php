<?php

namespace App\Http\Controllers;

use App\Models\ApprovalQueue;
use App\Models\Lead;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Operations dashboard — stats overview + recent activity.
     */
    public function index(): Response
    {
        $hasRealData = Lead::exists() || Ticket::exists() || ApprovalQueue::exists();

        if (! $hasRealData) {
            return Inertia::render('Dashboard', [
                'stats' => $this->mockStats(),
                'activities' => $this->mockActivities(),
                'demoMode' => true,
            ]);
        }

        $stats = [
            'leadsCount' => Lead::count(),
            'openTicketsCount' => Ticket::where('status', 'open')->count(),
            'pendingApprovalsCount' => ApprovalQueue::where('status', 'pending')->count(),
            'hotLeadsCount' => Lead::where('score', '>=', 75)->count(),
            'responseTime' => '1.8s',
            'accuracyRate' => '98.6%',
        ];

        $activities = collect()
            ->merge(
                Lead::orderByDesc('created_at')->limit(3)->get()->map(fn ($l) => [
                    'id' => 'lead-'.$l->id,
                    'type' => 'lead',
                    'title' => 'Lead Captured & Scored',
                    'desc' => "{$l->name} from ".($l->company ?? 'unknown')." scored {$l->score} ({$l->category})",
                    'time' => optional($l->created_at)->diffForHumans() ?? 'Just now',
                    'sort' => optional($l->created_at)->timestamp ?? 0,
                ]),
            )
            ->merge(
                Ticket::orderByDesc('created_at')->limit(3)->get()->map(fn ($t) => [
                    'id' => 'ticket-'.$t->id,
                    'type' => 'ticket',
                    'title' => 'Incoming Ticket Triaged',
                    'desc' => "Inquiry from {$t->source_email} classified as {$t->priority}",
                    'time' => optional($t->created_at)->diffForHumans() ?? 'Just now',
                    'sort' => optional($t->created_at)->timestamp ?? 0,
                ]),
            )
            ->merge(
                ApprovalQueue::where('status', '!=', 'pending')
                    ->orderByDesc('reviewed_at')
                    ->limit(3)
                    ->get()
                    ->map(fn ($a) => [
                        'id' => 'app-'.$a->id,
                        'type' => 'approval',
                        'title' => $a->status === 'approved' ? 'Draft Approved & Sent' : 'Draft Rejected',
                        'desc' => "Reviewed by {$a->reviewed_by}",
                        'time' => optional($a->reviewed_at)->diffForHumans() ?? 'Just now',
                        'sort' => optional($a->reviewed_at)->timestamp ?? 0,
                    ]),
            )
            ->sortByDesc('sort')
            ->take(6)
            ->values()
            ->map(fn ($item) => collect($item)->except('sort')->all());

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'activities' => $activities,
            'demoMode' => false,
        ]);
    }

    private function mockStats(): array
    {
        return [
            'leadsCount' => 1524,
            'openTicketsCount' => 42,
            'pendingApprovalsCount' => 3,
            'hotLeadsCount' => 28,
            'responseTime' => '1.8s',
            'accuracyRate' => '98.6%',
        ];
    }

    private function mockActivities(): array
    {
        return [
            ['id' => 1, 'type' => 'lead', 'title' => 'VIP Lead Qualified', 'desc' => 'sarah.j@vaporscale.com scored 98', 'time' => '12 mins ago'],
            ['id' => 2, 'type' => 'ticket', 'title' => 'Email Triaged', 'desc' => 'Ticket from marcus.t@solosaas.io classified as Sales / MEDIUM', 'time' => '1 hour ago'],
            ['id' => 3, 'type' => 'system', 'title' => 'Vector Sync Complete', 'desc' => '14 chunks indexed from DB_Tuning_Guide.pdf', 'time' => '2 hours ago'],
            ['id' => 4, 'type' => 'approval', 'title' => 'AI Draft Approved', 'desc' => 'Response to hello@nexustech.org sent', 'time' => '4 hours ago'],
            ['id' => 5, 'type' => 'lead', 'title' => 'Lead Captured', 'desc' => 'New webhook lead from solosaas.io', 'time' => '5 hours ago'],
        ];
    }
}
