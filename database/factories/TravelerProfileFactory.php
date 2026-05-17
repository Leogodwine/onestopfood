<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelerProfileFactory extends Factory
{
    public function definition(): array
    {
        $vehicleTypes = ['motorcycle', 'bicycle', 'car', 'van'];

        return [
            'user_id' => User::factory(),
            'vehicle_type' => fake()->randomElement($vehicleTypes),
            'vehicle_registration_no' => fake()->bothify('TZ-#######'),
            'is_online' => fake()->boolean(60),
        ];
    }
}
