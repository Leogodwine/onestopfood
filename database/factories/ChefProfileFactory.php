<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChefProfileFactory extends Factory
{
    public function definition(): array
    {
        $cuisineTypes = ['Italian Cuisine', 'Asian Fusion', 'American BBQ', 'French Cuisine', 'Mediterranean', 'Mexican', 'African Heritage'];
        $specialties = [
            'Italian' => ['Pasta Expert', 'Michelin Trained', '15+ Years Experience'],
            'Asian Fusion' => ['Fusion Expert', 'James Beard Nominee', 'Sustainable Cooking', 'Innovation Award'],
            'American BBQ' => ['BBQ Master', 'Sauce Specialist', 'National Champion', '20+ Years Experience'],
            'French Cuisine' => ['Michelin Trained', 'French Master', 'Classical Techniques'],
            'Mediterranean' => ['Mediterranean Master', 'Seafood Expert', 'Organic Specialist'],
        ];

        $cuisine = fake()->randomElement($cuisineTypes);
        $specialtiesList = $specialties[$cuisine] ?? ['Expert Chef', 'Professional Cooking'];

        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(3),
            'heritage_story' => fake()->paragraph(5),
            'specialties' => implode(', ', $specialtiesList),
            'specialties_list' => $specialtiesList,
            'kitchen_address' => fake()->address(),
            'food_handler_certificate_no' => 'FH-' . fake()->numerify('#######'),
            'years_experience' => (string) fake()->numberBetween(5, 25),
            'cuisine_type' => $cuisine,
        ];
    }
}
