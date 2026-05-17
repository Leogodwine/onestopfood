<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@fooddelivery.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_super_admin' => true,
                'status' => User::STATUS_APPROVED,
                'approved_at' => now(),
            ]
        );

        $this->command->info('Admin user created: admin@fooddelivery.com / password');
    }
}
