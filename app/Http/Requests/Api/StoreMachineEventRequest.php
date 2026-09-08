<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMachineEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'event_id' => trim((string) $this->input('event_id')),
            'transaction_id' => trim((string) $this->input('transaction_id')),
            'event_type' => strtoupper(trim((string) $this->input('event_type'))),
            'card_uid' => strtoupper(trim((string) $this->input('card_uid'))),
            'machine_code' => strtoupper(trim((string) $this->input('machine_code'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'uuid'],
            'transaction_id' => ['required', 'uuid'],
            'event_type' => ['required', 'string', Rule::in(config('machine_auth.event_types', []))],
            'card_uid' => ['required', 'string', 'max:64', 'not_regex:/[\x00-\x1F\x7F]/'],
            'machine_code' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9._-]+$/'],
            'occurred_at' => ['required', 'date'],
            'metadata' => ['nullable', 'array', 'max:50'],
        ];
    }
}
