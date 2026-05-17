<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['pending', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
        $subtotal = fake()->randomFloat(2, 20, 200);
        $deliveryFee = fake()->randomFloat(2, 5, 15);
        $total = $subtotal + $deliveryFee;

        return [
            'customer_id' => User::factory(),
            'chef_id' => User::factory(),
            'status' => fake()->randomElement($statuses),
            'special_instructions' => fake()->optional(0.3)->sentence(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'delivery_location_id' => Location::factory(),
        ];
    }
}
