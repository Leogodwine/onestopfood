<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;

trait UsesSeedPassword
{
    protected function seedPasswordHash(): string
    {
        $password = env('SEED_USER_PASSWORD') ?: env('ADMIN_SEED_PASSWORD');

        if (! $password && app()->environment('production')) {
            throw new \RuntimeException(
                'Set SEED_USER_PASSWORD or ADMIN_SEED_PASSWORD in .env before seeding in production.'
            );
        }

        $password ??= 'FoodDelivery@2026!';

        return Hash::make($password);
    }

    protected function seedPasswordHint(): string
    {
        if (app()->environment('production')) {
            return '(password from SEED_USER_PASSWORD / ADMIN_SEED_PASSWORD in .env)';
        }

        return '(default dev: FoodDelivery@2026! or set SEED_USER_PASSWORD in .env)';
    }
}
