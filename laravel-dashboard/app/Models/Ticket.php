<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'customer_id',
        'subject',
        'body',
        'category',
        'priority',
        'status',
        'source_email',
        'gmail_thread_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvalQueue(): HasMany
    {
        return $this->hasMany(ApprovalQueue::class);
    }
}
