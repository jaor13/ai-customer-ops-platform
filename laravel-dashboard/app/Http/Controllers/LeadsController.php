<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Inertia\Inertia;
use Inertia\Response;

class LeadsController extends Controller
{
    /**
     * List leads with key columns for the pipeline view.
     */
    public function index(): Response
    {
        $leads = Lead::orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'name', 'email', 'company', 'source', 'status', 'score', 'category', 'created_at'])
            ->map(fn ($l) => [
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
        ]);
    }
}
