<?php

namespace App\Http\Controllers;

use App\Actions\IngestKnowledgeDocumentAction;
use App\Http\Requests\StoreKnowledgeDocumentRequest;
use App\Http\Requests\UpdateKnowledgeDocumentRequest;
use App\Models\KnowledgeDocument;
use App\Services\QdrantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(): Response
    {
        $documents = KnowledgeDocument::orderByDesc('created_at')
            ->limit(100)
            ->get([
                'id',
                'doc_key',
                'original_name',
                'filename',
                'category',
                'department',
                'version',
                'is_active',
                'status',
                'authority_weight',
                'source_type',
                'chunk_count',
                'page_count',
                'ocr_used',
                'ingest_error',
                'effective_from',
                'effective_to',
                'created_at',
            ])
            ->map(fn (KnowledgeDocument $d) => [
                'id' => $d->id,
                'doc_key' => $d->doc_key,
                'original_name' => $d->original_name,
                'filename' => $d->filename,
                'category' => $d->category,
                'department' => $d->department,
                'version' => $d->version,
                'is_active' => $d->is_active,
                'status' => $d->status,
                'authority_weight' => (float) $d->authority_weight,
                'source_type' => $d->source_type,
                'chunk_count' => $d->chunk_count,
                'page_count' => $d->page_count,
                'ocr_used' => $d->ocr_used,
                'ingest_error' => $d->ingest_error,
                'effective_from' => optional($d->effective_from)->toDateString(),
                'effective_to' => optional($d->effective_to)->toDateString(),
                'created_at' => optional($d->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('KnowledgeBase/Index', [
            'documents' => $documents,
            'documentKeys' => KnowledgeDocument::query()
                ->selectRaw('doc_key, MAX(version) as latest_version')
                ->groupBy('doc_key')
                ->orderBy('doc_key')
                ->get()
                ->map(fn ($row) => [
                    'doc_key' => $row->doc_key,
                    'latest_version' => (int) $row->latest_version,
                ]),
            'options' => [
                'categories' => StoreKnowledgeDocumentRequest::CATEGORIES,
                'departments' => StoreKnowledgeDocumentRequest::DEPARTMENTS,
            ],
            'maxUploadKb' => config('services.knowledge_base.max_upload_kb', 10240),
        ]);
    }

    public function store(StoreKnowledgeDocumentRequest $request, IngestKnowledgeDocumentAction $action): RedirectResponse
    {
        $action->execute(
            $request->safe()->except('file'),
            $request->file('file'),
            $request->user()?->email,
        );

        return back()->with('success', 'Document uploaded. Text extraction and indexing are running in the background.');
    }

    public function update(UpdateKnowledgeDocumentRequest $request, KnowledgeDocument $knowledgeDocument, QdrantService $qdrant): RedirectResponse
    {
        $validated = $request->validated();
        $knowledgeDocument->update($validated);

        // Keep the Qdrant payload mirror in sync so authority/category filters
        // and ranking reflect the edit immediately — no re-embedding needed.
        $qdrant->setPayload($knowledgeDocument->id, [
            'category' => $knowledgeDocument->category,
            'department' => $knowledgeDocument->department,
            'authority_weight' => (float) $knowledgeDocument->authority_weight,
            'effective_from' => optional($knowledgeDocument->effective_from)->toDateString(),
            'effective_to' => optional($knowledgeDocument->effective_to)->toDateString(),
        ]);

        return back()->with('success', 'Document metadata updated.');
    }

    public function destroy(KnowledgeDocument $knowledgeDocument, QdrantService $qdrant): RedirectResponse
    {
        $disk = config('services.knowledge_base.disk', 's3');

        if ($knowledgeDocument->filename && Storage::disk($disk)->exists($knowledgeDocument->filename)) {
            Storage::disk($disk)->delete($knowledgeDocument->filename);
        }

        // Remove this version's chunks from Qdrant so nothing orphaned is left
        // behind (prevents stale retrieval / contamination).
        $qdrant->deleteByDocumentId($knowledgeDocument->id);

        $knowledgeDocument->delete();

        return back()->with('success', 'Document removed from storage and the vector index.');
    }
}
