<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'approval_queue_id',
        'customer_id',
        'to_email',
        'subject',
        'body',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function approvalQueue(): BelongsTo
    {
        return $this->belongsTo(ApprovalQueue::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
