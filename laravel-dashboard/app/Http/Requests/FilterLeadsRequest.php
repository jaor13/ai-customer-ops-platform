<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterLeadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by the 'auth' + 'verified' route middleware.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Normalize the validated filters into a predictable shape (blank strings
     * instead of nulls) so the frontend controls stay controlled.
     *
     * @return array{search: string, category: string, source: string, status: string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim($validated['search'] ?? ''),
            'category' => $validated['category'] ?? '',
            'source' => $validated['source'] ?? '',
            'status' => $validated['status'] ?? '',
        ];
    }
}
