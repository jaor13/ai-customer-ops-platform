<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeDocument extends Model
{
    protected $fillable = [
        // identity & versioning
        'doc_key',
        'version',
        'is_active',
        'status',
        // classification & authority
        'category',
        'authority_weight',
        'department',
        // effective dating
        'effective_from',
        'effective_to',
        // file provenance
        'filename',
        'original_name',
        'source_type',
        'mime_type',
        'file_size',
        'checksum_sha256',
        'page_count',
        'ocr_used',
        // ingestion results
        'chunk_count',
        'qdrant_ids',
        'ingest_error',
        // audit
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'qdrant_ids' => 'array',
            'is_active' => 'boolean',
            'ocr_used' => 'boolean',
            'version' => 'integer',
            'authority_weight' => 'decimal:2',
            'file_size' => 'integer',
            'page_count' => 'integer',
            'chunk_count' => 'integer',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
