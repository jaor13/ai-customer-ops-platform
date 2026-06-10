<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class TicketsController extends Controller
{
    /**
     * List tickets with eager-loaded customer to avoid N+1.
     */
    public function index(): Response
    {
        $tickets = Ticket::with('customer:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'customer_name' => $t->customer?->name,
                'source_email' => $t->source_email,
                'subject' => $t->subject,
                'category' => $t->category,
                'priority' => strtoupper($t->priority ?? 'MEDIUM'),
                'status' => $t->status,
                'created_at' => optional($t->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }
}
