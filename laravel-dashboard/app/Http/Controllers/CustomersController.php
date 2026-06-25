<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Interaction;
use Inertia\Inertia;
use Inertia\Response;

class CustomersController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'name', 'email', 'phone', 'company', 'created_at'])
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'company' => $c->company,
                'created_at' => optional($c->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Customer detail + activity timeline assembled from `interactions`
     * (matched by customer_id or the originating lead_id) and tickets.
     */
    public function show(Customer $customer): Response
    {
        $customer->loadMissing('lead:id,score,category,source');

        // Interactions may be recorded against the customer OR, before the
        // customer record existed, against the originating lead.
        $timeline = Interaction::query()
            ->where('customer_id', $customer->id)
            ->when($customer->lead_id, fn ($q) => $q->orWhere('lead_id', $customer->lead_id))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'event_type', 'description', 'metadata', 'created_at'])
            ->map(fn (Interaction $i) => [
                'id' => $i->id,
                'event_type' => $i->event_type,
                'description' => $i->description,
                'metadata' => $i->metadata,
                'time' => optional($i->created_at)->diffForHumans() ?? '—',
                'date' => optional($i->created_at)->toDayDateTimeString() ?? '',
            ]);

        $tickets = $customer->tickets()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'subject', 'category', 'priority', 'status', 'created_at'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'category' => $t->category,
                'priority' => strtoupper($t->priority ?? 'MEDIUM'),
                'status' => $t->status,
                'created_at' => optional($t->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'company' => $customer->company,
                'created_at' => optional($customer->created_at)->toDayDateTimeString() ?? '—',
                'lead' => $customer->lead ? [
                    'score' => $customer->lead->score,
                    'category' => $customer->lead->category,
                    'source' => $customer->lead->source,
                ] : null,
            ],
            'timeline' => $timeline,
            'tickets' => $tickets,
        ]);
    }
}
