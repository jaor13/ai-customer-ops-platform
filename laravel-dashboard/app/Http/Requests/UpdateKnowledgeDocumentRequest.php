<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(StoreKnowledgeDocumentRequest::CATEGORIES)],
            'department' => ['nullable', Rule::in(StoreKnowledgeDocumentRequest::DEPARTMENTS)],
            'authority_weight' => ['required', 'numeric', 'between:0.5,2'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
