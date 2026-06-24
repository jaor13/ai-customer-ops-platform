<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use App\Services\DocumentTextExtractor;
use App\Services\QdrantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Extracts text (with OCR fallback) off the request thread and hands the
 * ready text + metadata to n8n's ingestion webhook. n8n chunks/embeds/upserts
 * to Qdrant and writes back chunk_count/qdrant_ids/status. See docs/12 §6.
 */
class ProcessKnowledgeDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $documentId) {}

    public function handle(DocumentTextExtractor $extractor, QdrantService $qdrant): void
    {
        $document = KnowledgeDocument::find($this->documentId);

        if ($document === null) {
            return;
        }

        $disk = config('services.knowledge_base.disk', 's3');
        $extension = strtolower(pathinfo($document->filename, PATHINFO_EXTENSION));
        $localPath = null;

        try {
            // Pull the original to a temp local file for parsing/OCR.
            $localPath = tempnam(sys_get_temp_dir(), 'kb_src_').'.'.$extension;
            file_put_contents($localPath, Storage::disk($disk)->get($document->filename));

            $extracted = $extractor->extract($localPath, $extension);

            if (trim($extracted['text']) === '') {
                $this->markFailed($document, 'No extractable text found in the document.');

                return;
            }

            $document->update([
                'source_type' => $extracted['source_type'],
                'page_count' => $extracted['page_count'],
                'ocr_used' => $extracted['ocr_used'],
            ]);

            $this->sendToN8n($document, $extracted['text'], $qdrant);
        } catch (\Throwable $e) {
            Log::error('Knowledge document processing failed', [
                'document_id' => $document->id,
                'message' => $e->getMessage(),
            ]);
            $this->markFailed($document, $e->getMessage());

            throw $e; // let the queue retry per $tries
        } finally {
            if ($localPath !== null && is_file($localPath)) {
                @unlink($localPath);
            }
        }
    }

    /**
     * POST the doc-12 §6 contract. If n8n isn't configured yet, leave the
     * document in 'processing' (extracted, awaiting ingestion wiring).
     */
    private function sendToN8n(KnowledgeDocument $document, string $text, QdrantService $qdrant): void
    {
        $url = config('services.n8n.ingest_url');

        if (blank($url)) {
            Log::info('n8n ingest URL not configured; document extracted and awaiting ingestion.', [
                'document_id' => $document->id,
            ]);

            return;
        }

        $response = Http::timeout(config('services.n8n.timeout', 30))
            ->withHeaders(array_filter([
                'X-Webhook-Secret' => config('services.n8n.secret'),
            ]))
            ->post($url, [
                'document_id' => $document->id,
                'doc_key' => $document->doc_key,
                'version' => $document->version,
                'filename' => $document->original_name ?? $document->filename,
                'category' => $document->category,
                'department' => $document->department,
                'authority_weight' => (float) $document->authority_weight,
                'source_type' => $document->source_type,
                'effective_from' => optional($document->effective_from)->toDateString(),
                'effective_to' => optional($document->effective_to)->toDateString(),
                'text' => $text,
            ]);

        if ($response->failed()) {
            $this->markFailed($document, 'n8n ingestion webhook returned HTTP '.$response->status());

            return;
        }

        // n8n is stateless now — it returns the counts; Laravel stores them.
        // (We delete/supersede by document_id, so qdrant_ids is audit-only.)
        $result = $response->json();
        $document->update([
            'chunk_count' => $result['chunks'] ?? null,
            'qdrant_ids' => $result['point_ids'] ?? null,
        ]);

        // Ingestion succeeded: promote this version and retire the old ones.
        $this->promoteAndSupersede($document, $qdrant);
    }

    /**
     * Activate the freshly-ingested version and retire all prior versions of
     * the same doc_key — in Postgres (source of truth) and in Qdrant (so the
     * active-only retrieval filter immediately stops surfacing stale chunks).
     */
    private function promoteAndSupersede(KnowledgeDocument $document, QdrantService $qdrant): void
    {
        $supersededIds = DB::transaction(function () use ($document) {
            $priorIds = KnowledgeDocument::where('doc_key', $document->doc_key)
                ->where('id', '!=', $document->id)
                ->where('status', '!=', 'superseded')
                ->pluck('id')
                ->all();

            if (! empty($priorIds)) {
                KnowledgeDocument::whereIn('id', $priorIds)
                    ->update(['is_active' => false, 'status' => 'superseded', 'updated_at' => now()]);
            }

            $document->update(['is_active' => true, 'status' => 'active', 'ingest_error' => null]);

            return $priorIds;
        });

        // Flip the old chunks in Qdrant (best-effort; logged on failure).
        foreach ($supersededIds as $id) {
            $qdrant->supersedeByDocumentId($id);
        }
    }

    private function markFailed(KnowledgeDocument $document, string $reason): void
    {
        $document->update(['status' => 'failed', 'ingest_error' => $reason]);
    }

    public function failed(\Throwable $exception): void
    {
        $document = KnowledgeDocument::find($this->documentId);
        $document?->update(['status' => 'failed', 'ingest_error' => $exception->getMessage()]);
    }
}
