<?php

namespace App\Console\Commands;

use App\Models\Machine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateMachineAuthToken extends Command
{
    protected $signature = 'machine-auth:token
        {machine_code : Kode mesin, contoh MC-001}
        {--name=raspberry-pi : Nama token}
        {--expires= : Waktu kedaluwarsa yang dapat diparse Carbon}
        {--rotate : Cabut seluruh token mesin sebelum membuat token baru}';

    protected $description = 'Membuat atau merotasi Sanctum token untuk Raspberry Pi';

    public function handle(): int
    {
        $machineCode = strtoupper(trim((string) $this->argument('machine_code')));
        $machine = Machine::where('machine_code', $machineCode)->first();

        if (! $machine) {
            $this->error("Mesin {$machineCode} tidak ditemukan.");

            return self::FAILURE;
        }

        if ($this->option('rotate')) {
            $machine->tokens()->delete();
            $this->warn('Seluruh token lama untuk mesin ini telah dicabut.');
        }

        try {
            $expiresAt = $this->option('expires')
                ? Carbon::parse((string) $this->option('expires'))
                : null;
        } catch (\Throwable $exception) {
            $this->error('Nilai --expires tidak valid.');

            return self::FAILURE;
        }

        if ($expiresAt && $expiresAt->isPast()) {
            $this->error('Waktu kedaluwarsa harus berada di masa depan.');

            return self::FAILURE;
        }

        $token = $machine->createToken(
            trim((string) $this->option('name')) ?: 'raspberry-pi',
            [config('machine_auth.token_ability', 'machine-auth')],
            $expiresAt
        );

        $this->newLine();
        $this->info("Token untuk {$machine->machine_code} berhasil dibuat.");
        $this->warn('Simpan token berikut sekarang. Token tidak dapat dilihat kembali:');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
