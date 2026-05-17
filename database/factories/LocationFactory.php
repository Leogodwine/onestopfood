<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        $areas = ['Masaki', 'Oysterbay', 'Mikocheni', 'Upanga', 'Kariakoo', 'City Center'];
        
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement($areas),
            'address_line' => fake()->streetAddress(),
            'city' => 'Dar es Salaam',
            'region' => 'Dar es Salaam',
            'country' => 'Tanzania',
            'latitude' => fake()->latitude(-6.8, -6.7),
            'longitude' => fake()->longitude(39.2, 39.3),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
