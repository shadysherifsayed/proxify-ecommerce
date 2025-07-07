<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create categories first
        $categories = [
            'Electronics',
            'Clothing',
            'Home & Garden',
            'Sports & Outdoors',
            'Books',
            'Health & Beauty',
        ];

        foreach ($categories as $categoryName) {
            \App\Models\Category::firstOrCreate([
                'name' => $categoryName,
            ]);
        }

        // Create products for each category
        $categoryIds = \App\Models\Category::pluck('id')->toArray();

        foreach ($categoryIds as $categoryId) {
            \App\Models\Product::factory(20)->create([
                'category_id' => $categoryId,
            ]);
        }

        // Create a test user if it doesn't exist
        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);
    }
}
