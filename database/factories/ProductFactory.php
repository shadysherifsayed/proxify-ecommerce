<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'description' => $this->faker->paragraph(3),
            'image' => $this->faker->imageUrl(400, 400, 'products'),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'reviews_count' => $this->faker->numberBetween(1, 1000),
            'external_id' => $this->faker->unique()->randomNumber(5),
            'category_id' => \App\Models\Category::factory(),
        ];
    }
}
