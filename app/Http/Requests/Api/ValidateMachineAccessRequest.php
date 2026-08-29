<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ValidateMachineAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'request_id' => trim((string) $this->input('request_id')),
            'card_uid' => strtoupper(trim((string) $this->input('card_uid'))),
            'machine_code' => strtoupper(trim((string) $this->input('machine_code'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'uuid'],
            'card_uid' => ['required', 'string', 'max:64', 'not_regex:/[\x00-\x1F\x7F]/'],
            'machine_code' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9._-]+$/'],
        ];
    }
}
