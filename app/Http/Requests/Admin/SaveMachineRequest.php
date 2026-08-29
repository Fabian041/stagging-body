<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-machine-auth') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'machine_code' => strtoupper(trim((string) $this->input('machine_code'))),
            'machine_name' => trim((string) $this->input('machine_name')),
            'location' => $this->filled('location') ? trim((string) $this->input('location')) : null,
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $machine = $this->route('machine');

        return [
            'line_id' => [
                'required',
                'integer',
                Rule::exists('lines', 'id')->where(function ($query) use ($machine) {
                    $query->where(function ($lineQuery) use ($machine) {
                        $lineQuery->where('is_active', true);

                        if ($machine?->line_id) {
                            $lineQuery->orWhere('id', $machine->line_id);
                        }
                    });
                }),
            ],
            'machine_code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9._-]+$/',
                Rule::unique('machines', 'machine_code')->ignore($machine?->id),
            ],
            'machine_name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
