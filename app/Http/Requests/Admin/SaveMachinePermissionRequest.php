<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMachinePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-machine-auth') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_allowed' => $this->boolean('is_allowed'),
        ]);
    }

    public function rules(): array
    {
        $permission = $this->route('machinePermission');

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'machine_id' => [
                'required',
                'integer',
                'exists:machines,id',
                Rule::unique('machine_permissions', 'machine_id')
                    ->where(fn ($query) => $query->where('user_id', $this->input('user_id')))
                    ->ignore($permission?->id),
            ],
            'is_allowed' => ['required', 'boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('valid_from') || ! $this->filled('valid_until')) {
                return;
            }

            $validFrom = strtotime((string) $this->input('valid_from'));
            $validUntil = strtotime((string) $this->input('valid_until'));

            if ($validFrom !== false && $validUntil !== false && $validUntil <= $validFrom) {
                $validator->errors()->add('valid_until', 'Berlaku sampai harus setelah berlaku mulai.');
            }
        });
    }
}
