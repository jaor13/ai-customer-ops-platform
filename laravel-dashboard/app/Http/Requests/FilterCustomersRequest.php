<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterCustomersRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Normalize the validated filters into a predictable shape.
     *
     * @return array{search: string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim($validated['search'] ?? ''),
        ];
    }
}
