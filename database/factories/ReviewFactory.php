<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'customer_id' => User::factory(),
            'chef_id' => User::factory(),
            'traveler_id' => fake()->optional(0.5)->randomElement([User::factory()]),
            'chef_rating' => fake()->numberBetween(3, 5),
            'traveler_rating' => fake()->optional(0.4)->numberBetween(3, 5),
            'comment' => fake()->optional(0.6)->paragraph(2),
        ];
    }
}
