<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 50);
        $lineTotal = $quantity * $unitPrice;

        return [
            'order_id' => Order::factory(),
            'meal_id' => Meal::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
}
