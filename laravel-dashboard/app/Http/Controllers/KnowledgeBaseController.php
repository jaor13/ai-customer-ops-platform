<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeDocument;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(): Response
    {
        $documents = KnowledgeDocument::orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'filename', 'original_name', 'department', 'version', 'chunk_count', 'created_at'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'filename' => $d->filename,
                'original_name' => $d->original_name,
                'department' => $d->department,
                'version' => $d->version,
                'chunk_count' => $d->chunk_count,
                'created_at' => optional($d->created_at)->diffForHumans() ?? '—',
            ]);

        return Inertia::render('KnowledgeBase/Index', [
            'documents' => $documents,
        ]);
    }
}
