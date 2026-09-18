<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateMachineTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-machine-auth') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'rotate_existing' => $this->boolean('rotate_existing'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'rotate_existing' => ['required', 'boolean'],
        ];
    }
}
