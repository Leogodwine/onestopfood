<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'administrator@onestopfood.co.tz';

    private const DEFAULT_ADMIN_PASSWORD = 'Admin@OneStopFood@2026';

    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD', self::DEFAULT_ADMIN_PASSWORD);

        User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
                'admin_title' => User::ADMIN_TITLE_SYSTEM_ADMINISTRATOR,
                'is_super_admin' => true,
                'status' => User::STATUS_APPROVED,
                'approved_at' => now(),
            ]
        );

        $this->command->info('Admin user ready: '.self::ADMIN_EMAIL);
    }
}
