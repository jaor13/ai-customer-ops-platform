<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterLeadsRequest;
use App\Models\Lead;
use Inertia\Inertia;
use Inertia\Response;

class LeadsController extends Controller
{
    /**
     * List leads with score/category/source/status filters + a search box.
     */
    public function index(FilterLeadsRequest $request): Response
    {
        $filters = $request->filters();

        $leads = Lead::query()
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ilike', $term)
                        ->orWhere('email', 'ilike', $term)
                        ->orWhere('company', 'ilike', $term);
                });
            })
            ->when($filters['category'] !== '', fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['source'] !== '', fn ($q) => $q->where('source', $filters['source']))
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'name', 'email', 'company', 'source', 'status', 'score', 'category', 'created_at'])
            ->map(fn (Lead $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'company' => $l->company,
                'source' => $l->source,
                'status' => $l->status,
                'score' => $l->score,
                'category' => $l->category,
                'created_at' => optional($l->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => $filters,
            'options' => [
                'categories' => $this->distinctValues('category'),
                'sources' => $this->distinctValues('source'),
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
        return Lead::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    /**
     * Pipeline summary — single query with conditional aggregates.
     *
     * @return array<string, int>
     */
    private function stats(): array
    {
        $row = Lead::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(*) FILTER (WHERE category = 'Hot') AS hot")
            ->selectRaw("COUNT(*) FILTER (WHERE category = 'Warm') AS warm")
            ->selectRaw("COUNT(*) FILTER (WHERE category = 'Cold') AS cold")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'hot' => (int) ($row->hot ?? 0),
            'warm' => (int) ($row->warm ?? 0),
            'cold' => (int) ($row->cold ?? 0),
        ];
    }
}
