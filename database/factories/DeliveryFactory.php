<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['unassigned', 'assigned', 'picked_up', 'delivered', 'failed'];
        $travelerEarning = fake()->randomFloat(2, 5, 20);

        return [
            'order_id' => Order::factory(),
            'traveler_id' => fake()->optional(0.7)->randomElement([User::factory()]),
            'status' => fake()->randomElement($statuses),
            'traveler_earning' => $travelerEarning,
        ];
    }
}
