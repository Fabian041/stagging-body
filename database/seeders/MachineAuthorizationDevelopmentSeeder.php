<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachinePermission;
use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Database\Seeder;

class MachineAuthorizationDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Seeder machine authorization hanya boleh dijalankan di local/testing.');

            return;
        }

        $machine = Machine::updateOrCreate(
            ['machine_code' => 'MC-001'],
            [
                'machine_name' => 'Machine 001',
                'location' => 'Development',
                'description' => 'Development machine authorization seed',
                'is_active' => true,
            ]
        );

        $npk = trim((string) config('machine_auth.development_npk'));

        if ($npk === '') {
            $this->command?->warn('MC-001 dibuat. Set MACHINE_AUTH_DEV_NPK untuk membuat RFID dan permission development.');

            return;
        }

        $user = User::where('npk', $npk)->first();

        if (! $user) {
            $this->command?->warn("User development dengan NPK {$npk} tidak ditemukan.");

            return;
        }

        RfidCard::updateOrCreate(
            ['card_uid' => (string) config('machine_auth.development_card_uid', '1667815478')],
            ['user_id' => $user->id, 'is_active' => true]
        );

        MachinePermission::updateOrCreate(
            ['user_id' => $user->id, 'machine_id' => $machine->id],
            ['is_allowed' => true, 'valid_from' => null, 'valid_until' => null]
        );

        $this->command?->info('Development machine, RFID, dan permission berhasil disiapkan.');
    }
}
