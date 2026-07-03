<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserBulkImporter
{
    private const ROLES = ['admin', 'ppic', 'prod', 'mh', 'injection', 'direct'];

    private const REQUIRED_KEYS = ['name', 'email', 'npk', 'password', 'role'];

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{imported: int, errors: array<int, string>}
     */
    public function process(array $rows): array
    {
        if ($rows === []) {
            return ['imported' => 0, 'errors' => ['File tidak memiliki data.']];
        }

        $headers = $this->headers($rows[0] ?? []);
        $missing = array_values(array_diff(self::REQUIRED_KEYS, $headers));
        if ($missing !== []) {
            return ['imported' => 0, 'errors' => ['Format file tidak sesuai. Kolom wajib: Nama, Email, Username, Password, Role.']];
        }

        $imported = 0;
        $errors = [];
        $seenEmails = [];
        $seenNpks = [];

        for ($i = 1; $i < count($rows); $i++) {
            $line = $i + 1;
            $row = $this->combine($headers, $rows[$i] ?? []);
            if ($this->emptyRow($row)) {
                continue;
            }

            $data = [
                'name' => $this->cell($row, 'name'),
                'email' => strtolower($this->cell($row, 'email')),
                'npk' => $this->cell($row, 'npk'),
                'password' => $this->cell($row, 'password'),
                'role' => strtolower($this->cell($row, 'role')),
            ];

            $validator = Validator::make(
                $data,
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                    'npk' => ['required', 'digits:6', 'unique:users,npk'],
                    'password' => ['required', 'string', 'min:6'],
                    'role' => ['required', Rule::in(self::ROLES)],
                ],
                [
                    'required' => ':attribute wajib diisi.',
                    'email' => ':attribute tidak valid.',
                    'unique' => ':attribute sudah digunakan.',
                    'digits' => ':attribute harus 6 digit.',
                    'min' => ':attribute minimal :min karakter.',
                    'in' => ':attribute harus salah satu dari: '.implode(', ', self::ROLES).'.',
                ],
                [
                    'name' => 'Nama',
                    'email' => 'Email',
                    'npk' => 'Username/NPK',
                    'password' => 'Password',
                    'role' => 'Role',
                ]
            );

            if ($data['email'] !== '' && isset($seenEmails[$data['email']])) {
                $validator->after(fn ($v) => $v->errors()->add('email', 'Email duplikat di file ini.'));
            }
            if ($data['npk'] !== '' && isset($seenNpks[$data['npk']])) {
                $validator->after(fn ($v) => $v->errors()->add('npk', 'Username/NPK duplikat di file ini.'));
            }

            if ($validator->fails()) {
                $errors[] = "Baris {$line}: ".implode(' ', $validator->errors()->all());
                continue;
            }

            try {
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'npk' => $data['npk'],
                    'role' => $data['role'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);

                $seenEmails[$data['email']] = true;
                $seenNpks[$data['npk']] = true;
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Baris {$line}: gagal menyimpan user.";
            }
        }

        if ($imported === 0 && $errors === []) {
            $errors[] = 'Tidak ada baris data yang bisa diproses.';
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    private function headers(array $row): array
    {
        return array_map(function ($value) {
            $slug = Str::slug(trim((string) $value), '_');

            return match ($slug) {
                'nama', 'name' => 'name',
                'email', 'mail' => 'email',
                'username', 'user_name', 'npk', 'email_username', 'email_or_username' => 'npk',
                'password', 'kata_sandi' => 'password',
                'role', 'akses' => 'role',
                default => $slug,
            };
        }, $row);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function combine(array $headers, array $row): array
    {
        $out = [];
        foreach ($headers as $index => $key) {
            $out[$key] = $row[$index] ?? null;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function emptyRow(array $row): bool
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function cell(array $row, string $key): string
    {
        return trim((string) ($row[$key] ?? ''));
    }
}
