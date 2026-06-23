<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeDocumentRequest extends FormRequest
{
    /**
     * Allowed classification categories (mirrors knowledge_documents.category).
     */
    public const CATEGORIES = [
        'pricing',
        'faq',
        'policy',
        'sop',
        'case_study',
        'legal',
        'product',
        'general',
    ];

    /**
     * Allowed owning departments.
     */
    public const DEPARTMENTS = ['sales', 'support', 'billing', 'general'];

    public function authorize(): bool
    {
        // Route is already behind the 'auth' + 'verified' middleware group.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('services.knowledge_base.max_upload_kb', 10240),
                // Validate both the client extension and the server-detected MIME
                // (magic bytes) so a renamed binary can't slip through.
                'extensions:pdf,txt,md,docx',
                'mimetypes:application/pdf,text/plain,text/markdown,application/octet-stream,'
                .'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            // Stable logical key shared across versions. Optional — derived from
            // the filename when omitted. Lowercase slug.
            'doc_key' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'department' => ['nullable', Rule::in(self::DEPARTMENTS)],
            'authority_weight' => ['required', 'numeric', 'between:0.5,2'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.extensions' => 'Only PDF, DOCX, TXT, and Markdown files are accepted.',
            'file.mimetypes' => 'The uploaded file type does not match its contents.',
            'doc_key.regex' => 'The document key may only contain lowercase letters, numbers, hyphens, and underscores.',
            'authority_weight.between' => 'Authority weight must be between 0.50 (draft) and 2.00 (official).',
        ];
    }
}
