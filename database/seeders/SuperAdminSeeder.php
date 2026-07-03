<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['npk' => '000000'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@bella.local',
                'role' => 'super_admin',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );
    }
}
