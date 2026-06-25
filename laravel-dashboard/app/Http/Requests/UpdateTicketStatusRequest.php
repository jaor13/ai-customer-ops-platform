<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    /**
     * Allowed ticket lifecycle statuses (mirrors tickets.status).
     */
    public const STATUSES = ['open', 'pending_response', 'resolved', 'closed'];

    public function authorize(): bool
    {
        // Authorization is handled by the 'auth' + 'verified' route middleware.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(self::STATUSES)],
        ];
    }
}
