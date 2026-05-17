<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Italian', 'Asian Fusion', 'BBQ', 'Desserts', 'French', 'Mediterranean', 'Mexican', 'African Heritage'];
        $dietaryTags = ['Vegetarian', 'Vegan', 'Halal', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];

        return [
            'chef_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(15),
            'heritage_story' => fake()->optional(0.7)->paragraph(3),
            'origin' => fake()->optional(0.6)->sentence(2),
            'prep_time_minutes' => fake()->numberBetween(15, 60),
            'price' => fake()->randomFloat(2, 10, 100),
            'category' => fake()->randomElement($categories),
            'dietary_tags' => fake()->optional(0.5)->randomElement($dietaryTags),
            'image_path' => 'images/' . fake()->randomElement(['food 01.jpeg', 'food 03.png', 'african food 01.jpg', 'african food 02.jpg', 'african food 03.jpg', 'african food 04.jpg']),
            'is_available' => true,
            'is_heritage' => fake()->boolean(30),
            'is_popular' => fake()->boolean(40),
        ];
    }
}
