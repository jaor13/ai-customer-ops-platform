<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalQueue extends Model
{
    public $timestamps = false;

    protected $table = 'approval_queue';

    protected $fillable = [
        'ticket_id',
        'customer_id',
        'draft_body',
        'edited_body',
        'context_sources',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'context_sources' => 'array',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
