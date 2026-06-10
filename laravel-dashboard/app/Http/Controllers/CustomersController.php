<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class CustomersController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'name', 'email', 'phone', 'company', 'created_at'])
            ->map(fn ($c) => [
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
}
