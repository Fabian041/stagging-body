<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRfidCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-machine-auth') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_uid' => strtoupper(trim((string) $this->input('card_uid'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $card = $this->route('rfidCard');

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'card_uid' => [
                'required',
                'string',
                'max:64',
                'not_regex:/[\x00-\x1F\x7F]/',
                Rule::unique('rfid_cards', 'card_uid')->ignore($card?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
