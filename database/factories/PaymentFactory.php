<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $methods = ['mpesa', 'tigo', 'airtel', 'card', 'cod'];
        $statuses = ['pending', 'paid', 'failed', 'refunded'];

        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement($methods),
            'status' => fake()->randomElement($statuses),
            'amount' => fake()->randomFloat(2, 20, 200),
            'provider_reference' => fake()->optional(0.7)->bothify('REF-########'),
        ];
    }
}
