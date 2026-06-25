<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterTicketsRequest extends FormRequest
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
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Normalize the validated filters into a predictable shape (blank strings
     * instead of nulls) so the frontend controls stay controlled.
     *
     * @return array{search: string, category: string, priority: string, status: string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim($validated['search'] ?? ''),
            'category' => $validated['category'] ?? '',
            'priority' => $validated['priority'] ?? '',
            'status' => $validated['status'] ?? '',
        ];
    }
}
