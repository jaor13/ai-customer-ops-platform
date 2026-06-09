<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeDocument extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'filename',
        'original_name',
        'department',
        'version',
        'chunk_count',
        'qdrant_ids',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'qdrant_ids' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
