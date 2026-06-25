<?php

use App\Http\Controllers\ApprovalsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — overview only
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Approvals — review queue
    Route::get('/approvals', [ApprovalsController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{approval}/approve', [ApprovalsController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{approval}/reject', [ApprovalsController::class, 'reject'])->name('approvals.reject');

    // Pipeline
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::get('/tickets', [TicketsController::class, 'index'])->name('tickets.index');
    Route::patch('/tickets/{ticket}', [TicketsController::class, 'update'])->name('tickets.update');
    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomersController::class, 'show'])->name('customers.show');

    // Resources
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::post('/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('knowledge-base.store');
    Route::put('/knowledge-base/{knowledgeDocument}', [KnowledgeBaseController::class, 'update'])->name('knowledge-base.update');
    Route::delete('/knowledge-base/{knowledgeDocument}', [KnowledgeBaseController::class, 'destroy'])->name('knowledge-base.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
