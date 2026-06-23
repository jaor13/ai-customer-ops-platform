<?php

namespace App\Actions;

use App\Jobs\ProcessKnowledgeDocument;
use App\Models\KnowledgeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ingestion front door. Validates-already, stores the original on the
 * configured disk, applies the versioning lifecycle (a re-upload of the same
 * doc_key supersedes prior versions), records the metadata row, then queues
 * text extraction + the n8n hand-off. See docs/12 §4 and §6.
 */
class IngestKnowledgeDocumentAction
{
    /**
     * @param  array<string, mixed>  $data  Validated payload from StoreKnowledgeDocumentRequest.
     */
    public function execute(array $data, UploadedFile $file, ?string $uploadedBy = null): KnowledgeDocument
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $docKey = $this->resolveDocKey($data['doc_key'] ?? null, $file->getClientOriginalName());
        $checksum = hash_file('sha256', $file->getRealPath());

        $document = DB::transaction(function () use ($data, $file, $extension, $docKey, $checksum, $uploadedBy) {
            // Next version for this logical document, locking existing rows so
            // two concurrent uploads can't claim the same version number.
            $latestVersion = KnowledgeDocument::where('doc_key', $docKey)
                ->lockForUpdate()
                ->max('version');
            $version = ($latestVersion ?? 0) + 1;

            // Supersede every prior version so retrieval (status='active') stops
            // surfacing stale content the moment the new version lands.
            if ($latestVersion !== null) {
                KnowledgeDocument::where('doc_key', $docKey)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'status' => 'superseded', 'updated_at' => now()]);
            }

            // Persist the original so it can be re-processed / re-OCR'd later.
            $storedPath = $file->storeAs(
                "knowledge-base/{$docKey}/v{$version}",
                Str::uuid().'.'.$extension,
                config('services.knowledge_base.disk', 's3'),
            );

            return KnowledgeDocument::create([
                'doc_key' => $docKey,
                'version' => $version,
                'is_active' => true,
                'status' => 'processing', // → 'active' once n8n confirms ingestion, else 'failed'
                'category' => $data['category'],
                'authority_weight' => $data['authority_weight'],
                'department' => $data['department'] ?? null,
                'effective_from' => $data['effective_from'] ?? null,
                'effective_to' => $data['effective_to'] ?? null,
                'filename' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'checksum_sha256' => $checksum,
                'uploaded_by' => $uploadedBy,
            ]);
        });

        ProcessKnowledgeDocument::dispatch($document->id);

        return $document;
    }

    /**
     * Use the caller-supplied key, otherwise slug the filename (without
     * extension) into a stable logical key shared across versions.
     */
    private function resolveDocKey(?string $provided, string $originalName): string
    {
        if (filled($provided)) {
            return Str::slug($provided, '_');
        }

        $base = pathinfo($originalName, PATHINFO_FILENAME);

        return Str::slug($base, '_') ?: 'document';
    }
}
