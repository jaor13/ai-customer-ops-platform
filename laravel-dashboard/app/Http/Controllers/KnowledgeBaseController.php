<?php

namespace App\Http\Controllers;

use App\Actions\IngestKnowledgeDocumentAction;
use App\Http\Requests\StoreKnowledgeDocumentRequest;
use App\Http\Requests\UpdateKnowledgeDocumentRequest;
use App\Models\KnowledgeDocument;
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

    public function update(UpdateKnowledgeDocumentRequest $request, KnowledgeDocument $knowledgeDocument): RedirectResponse
    {
        $knowledgeDocument->update($request->validated());

        // TODO: When Qdrant payload sync is wired, also patch the chunk payloads
        // for this document_id with the updated category/department/authority_weight
        // via Qdrant set_payload API.

        return back()->with('success', 'Document metadata updated.');
    }

    public function destroy(KnowledgeDocument $knowledgeDocument): RedirectResponse
    {
        $disk = config('services.knowledge_base.disk', 's3');

        if ($knowledgeDocument->filename && Storage::disk($disk)->exists($knowledgeDocument->filename)) {
            Storage::disk($disk)->delete($knowledgeDocument->filename);
        }

        $knowledgeDocument->delete();

        // Note: Qdrant chunk cleanup (by document_id / qdrant_ids) is handled by
        // the n8n delete/supersede flow — wire that up when n8n is connected.
        return back()->with('success', 'Document removed.');
    }
}
