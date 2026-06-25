<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterTicketsRequest;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class TicketsController extends Controller
{
    /**
     * List tickets with search + category/priority/status filters.
     */
    public function index(FilterTicketsRequest $request): Response
    {
        $filters = $request->filters();

        $tickets = Ticket::with('customer:id,name')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(function ($q) use ($term) {
                    $q->where('subject', 'ilike', $term)
                        ->orWhere('body', 'ilike', $term)
                        ->orWhere('source_email', 'ilike', $term);
                });
            })
            ->when($filters['category'] !== '', fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['priority'] !== '', fn ($q) => $q->where('priority', $filters['priority']))
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn (Ticket $t) => [
                'id' => $t->id,
                'customer_name' => $t->customer?->name,
                'source_email' => $t->source_email,
                'subject' => $t->subject,
                'preview' => $t->body ? mb_substr($t->body, 0, 140) : null,
                'category' => $t->category,
                'priority' => strtoupper($t->priority ?? 'MEDIUM'),
                'status' => $t->status,
                'created_at' => optional($t->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'options' => [
                'categories' => $this->distinctValues('category'),
                'priorities' => ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'],
                'statuses' => $this->distinctValues('status'),
            ],
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Distinct non-null values for a filterable column (sorted, for dropdowns).
     *
     * @return array<int, string>
     */
    private function distinctValues(string $column): array
    {
        return Ticket::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    /**
     * Ticket summary — single query with conditional aggregates.
     *
     * @return array<string, int>
     */
    private function stats(): array
    {
        $row = Ticket::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'open') AS open")
            ->selectRaw("COUNT(*) FILTER (WHERE priority IN ('HIGH','CRITICAL')) AS urgent")
            ->selectRaw("COUNT(*) FILTER (WHERE status = 'resolved') AS resolved")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'open' => (int) ($row->open ?? 0),
            'urgent' => (int) ($row->urgent ?? 0),
            'resolved' => (int) ($row->resolved ?? 0),
        ];
    }
}
