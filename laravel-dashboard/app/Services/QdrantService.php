<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for the document-lifecycle operations Laravel owns directly
 * against Qdrant (delete, supersede, payload sync). Ingestion itself stays
 * in n8n; this only manages already-stored chunks, filtered by document_id.
 */
class QdrantService
{
    private string $collection;

    public function __construct()
    {
        $this->collection = (string) config('services.qdrant.collection', 'ai_crm_knowledge');
    }

    /**
     * Hard-delete every chunk belonging to a document version.
     */
    public function deleteByDocumentId(int $documentId): bool
    {
        $response = $this->client()->post(
            "/collections/{$this->collection}/points/delete",
            ['filter' => $this->documentFilter($documentId)],
        );

        return $this->ok($response, 'delete', $documentId);
    }

    /**
     * Flip a document version's chunks to status=superseded so retrieval
     * (which filters status=active) stops surfacing them.
     */
    public function supersedeByDocumentId(int $documentId): bool
    {
        return $this->setPayload($documentId, ['status' => 'superseded']);
    }

    /**
     * Patch filterable payload fields after a metadata edit (no re-embedding).
     *
     * @param  array<string, mixed>  $payload
     */
    public function setPayload(int $documentId, array $payload): bool
    {
        $response = $this->client()->post(
            "/collections/{$this->collection}/points/payload",
            ['payload' => $payload, 'filter' => $this->documentFilter($documentId)],
        );

        return $this->ok($response, 'set_payload', $documentId);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.qdrant.host'), '/'))
            ->timeout((int) config('services.qdrant.timeout', 15))
            ->acceptJson()
            ->asJson();
    }

    /**
     * @return array<string, mixed>
     */
    private function documentFilter(int $documentId): array
    {
        return ['must' => [['key' => 'document_id', 'match' => ['value' => $documentId]]]];
    }

    private function ok(Response $response, string $op, int $documentId): bool
    {
        if ($response->failed()) {
            Log::warning("Qdrant {$op} failed", [
                'document_id' => $documentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }
}
