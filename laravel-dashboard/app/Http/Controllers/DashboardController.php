<?php

namespace App\Http\Controllers;

use App\Models\ApprovalQueue;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Operations dashboard — real stats overview + recent activity.
     */
    public function index(): Response
    {
        $hasRealData = Lead::exists() || Ticket::exists() || ApprovalQueue::exists();

        if (! $hasRealData) {
            return Inertia::render('Dashboard', [
                'stats' => $this->mockStats(),
                'leadCaptureSeries' => $this->mockSeries(),
                'activities' => $this->mockActivities(),
                'demoMode' => true,
            ]);
        }

        return Inertia::render('Dashboard', [
            'stats' => $this->stats(),
            'leadCaptureSeries' => $this->leadCaptureSeries(),
            'activities' => $this->recentActivity(),
            'demoMode' => false,
        ]);
    }

    /**
     * Real headline metrics — all derived from the database.
     *
     * @return array<string, int>
     */
    private function stats(): array
    {
        return [
            'leadsCount' => Lead::count(),
            'hotLeadsCount' => Lead::where('category', 'Hot')->count(),
            'leadsThisWeek' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
            'openTicketsCount' => Ticket::where('status', 'open')->count(),
            'urgentTicketsCount' => Ticket::whereIn('priority', ['HIGH', 'CRITICAL'])
                ->whereIn('status', ['open', 'pending_response'])
                ->count(),
            'pendingApprovalsCount' => ApprovalQueue::where('status', 'pending')->count(),
            'sentCount' => ApprovalQueue::where('status', 'sent')->count(),
            'customersCount' => Customer::count(),
        ];
    }

    /**
     * Lead captures per day for the last 7 days (zero-filled), for the chart.
     *
     * @return array<int, array{day: string, count: int}>
     */
    private function leadCaptureSeries(): array
    {
        $start = now()->startOfDay()->subDays(6);

        $counts = Lead::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $series = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $series[] = [
                'day' => $date->format('D'),
                'count' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Most recent events across leads, tickets, and reviewed approvals.
     */
    private function recentActivity(): array
    {
        return collect()
            ->merge(
                Lead::orderByDesc('created_at')->limit(4)->get()->map(fn (Lead $l) => [
                    'id' => 'lead-'.$l->id,
                    'type' => 'lead',
                    'title' => 'Lead Captured & Scored',
                    'desc' => "{$l->name} from ".($l->company ?? 'unknown')
                        .($l->score !== null ? " scored {$l->score} ({$l->category})" : ''),
                    'time' => optional($l->created_at)->diffForHumans() ?? 'Just now',
                    'sort' => optional($l->created_at)->timestamp ?? 0,
                ]),
            )
            ->merge(
                Ticket::orderByDesc('created_at')->limit(4)->get()->map(fn (Ticket $t) => [
                    'id' => 'ticket-'.$t->id,
                    'type' => 'ticket',
                    'title' => 'Incoming Ticket Triaged',
                    'desc' => 'Inquiry from '.($t->source_email ?? 'unknown')
                        .' classified as '.($t->category ?? 'General').' / '.strtoupper($t->priority ?? 'MEDIUM'),
                    'time' => optional($t->created_at)->diffForHumans() ?? 'Just now',
                    'sort' => optional($t->created_at)->timestamp ?? 0,
                ]),
            )
            ->merge(
                ApprovalQueue::where('status', '!=', 'pending')
                    ->whereNotNull('reviewed_at')
                    ->orderByDesc('reviewed_at')
                    ->limit(4)
                    ->get()
                    ->map(fn (ApprovalQueue $a) => [
                        'id' => 'app-'.$a->id,
                        'type' => 'approval',
                        'title' => match ($a->status) {
                            'approved' => 'Draft Approved',
                            'sent' => 'Draft Approved & Sent',
                            'rejected' => 'Draft Rejected',
                            default => 'Draft Reviewed',
                        },
                        'desc' => 'Reviewed by '.($a->reviewed_by ?? 'reviewer'),
                        'time' => optional($a->reviewed_at)->diffForHumans() ?? 'Just now',
                        'sort' => optional($a->reviewed_at)->timestamp ?? 0,
                    ]),
            )
            ->sortByDesc('sort')
            ->take(6)
            ->values()
            ->map(fn ($item) => collect($item)->except('sort')->all())
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function mockStats(): array
    {
        return [
            'leadsCount' => 1524,
            'hotLeadsCount' => 28,
            'leadsThisWeek' => 96,
            'openTicketsCount' => 42,
            'urgentTicketsCount' => 7,
            'pendingApprovalsCount' => 3,
            'sentCount' => 311,
            'customersCount' => 218,
        ];
    }

    /**
     * @return array<int, array{day: string, count: int}>
     */
    private function mockSeries(): array
    {
        return [
            ['day' => 'Mon', 'count' => 12],
            ['day' => 'Tue', 'count' => 18],
            ['day' => 'Wed', 'count' => 15],
            ['day' => 'Thu', 'count' => 29],
            ['day' => 'Fri', 'count' => 36],
            ['day' => 'Sat', 'count' => 45],
            ['day' => 'Sun', 'count' => 52],
        ];
    }

    private function mockActivities(): array
    {
        return [
            ['id' => 1, 'type' => 'lead', 'title' => 'VIP Lead Qualified', 'desc' => 'sarah.j@vaporscale.com scored 98 (Hot)', 'time' => '12 mins ago'],
            ['id' => 2, 'type' => 'ticket', 'title' => 'Email Triaged', 'desc' => 'Ticket from marcus.t@solosaas.io classified as Sales / MEDIUM', 'time' => '1 hour ago'],
            ['id' => 3, 'type' => 'system', 'title' => 'Vector Sync Complete', 'desc' => '14 chunks indexed from DB_Tuning_Guide.pdf', 'time' => '2 hours ago'],
            ['id' => 4, 'type' => 'approval', 'title' => 'AI Draft Approved & Sent', 'desc' => 'Response to hello@nexustech.org sent', 'time' => '4 hours ago'],
            ['id' => 5, 'type' => 'lead', 'title' => 'Lead Captured', 'desc' => 'New webhook lead from solosaas.io', 'time' => '5 hours ago'],
        ];
    }
}
