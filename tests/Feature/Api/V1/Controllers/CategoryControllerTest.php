<?php

namespace Tests\Feature\Api\V1\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CategoryController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('CategoriesListing', function () {
        test('returns all categories with products count when authenticated', function () {
            // Create categories with different numbers of products
            $category1 = Category::factory()->create(['name' => 'Electronics']);
            $category2 = Category::factory()->create(['name' => 'Books']);
            $category3 = Category::factory()->create(['name' => 'Clothing']);

            // Create products for categories
            Product::factory()->count(3)->create(['category_id' => $category1->id]);
            Product::factory()->count(2)->create(['category_id' => $category2->id]);
            // category3 has no products

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'categories' => [
                        '*' => [
                            'id',
                            'name',
                            'created_at',
                            'updated_at',
                            'products_count',
                        ],
                    ],
                ]);

            $categories = $response->json('categories');

            // Verify all categories are returned
            expect($categories)->toHaveCount(3);

            // Find and verify each category's products count
            $electronicsCategory = collect($categories)->firstWhere('name', 'Electronics');
            $booksCategory = collect($categories)->firstWhere('name', 'Books');
            $clothingCategory = collect($categories)->firstWhere('name', 'Clothing');

            expect($electronicsCategory['products_count'])->toBe(3);
            expect($booksCategory['products_count'])->toBe(2);
            expect($clothingCategory['products_count'])->toBe(0);
        });

        test('returns empty array when no categories exist', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200)
                ->assertJson([
                    'categories' => [],
                ]);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/v1/categories');

            $response->assertStatus(401);
        });

        test('returns categories in database order', function () {
            // Create categories in specific order
            $category1 = Category::factory()->create(['name' => 'First Category']);
            $category2 = Category::factory()->create(['name' => 'Second Category']);
            $category3 = Category::factory()->create(['name' => 'Third Category']);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200);

            $categories = $response->json('categories');
            expect($categories)->toHaveCount(3);

            // Verify order based on creation order (assuming default ordering by id)
            expect($categories[0]['name'])->toBe('First Category');
            expect($categories[1]['name'])->toBe('Second Category');
            expect($categories[2]['name'])->toBe('Third Category');
        });

        test('products count is accurate for each category', function () {
            $category1 = Category::factory()->create(['name' => 'Category with Many Products']);
            $category2 = Category::factory()->create(['name' => 'Category with Few Products']);
            $category3 = Category::factory()->create(['name' => 'Empty Category']);

            // Create different numbers of products for each category
            Product::factory()->count(10)->create(['category_id' => $category1->id]);
            Product::factory()->count(1)->create(['category_id' => $category2->id]);
            // No products for category3

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200);

            $categories = $response->json('categories');

            foreach ($categories as $category) {
                switch ($category['name']) {
                    case 'Category with Many Products':
                        expect($category['products_count'])->toBe(10);
                        break;
                    case 'Category with Few Products':
                        expect($category['products_count'])->toBe(1);
                        break;
                    case 'Empty Category':
                        expect($category['products_count'])->toBe(0);
                        break;
                }
            }
        });

        test('returns correct data types for category fields', function () {
            $category = Category::factory()->create(['name' => 'Test Category']);
            Product::factory()->count(5)->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200);

            $categories = $response->json('categories');
            expect($categories)->toHaveCount(1);

            $categoryData = $categories[0];

            // Verify data types
            expect($categoryData['id'])->toBeInt();
            expect($categoryData['name'])->toBeString();
            expect($categoryData['products_count'])->toBeInt();
            expect($categoryData['created_at'])->toBeString();
            expect($categoryData['updated_at'])->toBeString();
        });

        test('handles large number of categories efficiently', function () {
            // Create many categories
            Category::factory()->count(100)->create();

            $startTime = microtime(true);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $response->assertStatus(200);

            // Ensure response time is reasonable (less than 2 seconds)
            expect($executionTime)->toBeLessThan(2.0);

            // Verify all categories are returned
            $categories = $response->json('categories');
            expect($categories)->toHaveCount(100);
        });

        test('returns correct JSON response format', function () {
            Category::factory()->create(['name' => 'Test Category']);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');

            // Verify the response structure matches what the controller returns
            expect($response->json())->toHaveKey('categories');
            expect($response->json('categories'))->toBeArray();
        });

        test('categories include all required fields', function () {
            $category = Category::factory()->create(['name' => 'Complete Category']);
            Product::factory()->count(3)->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200);

            $categories = $response->json('categories');
            expect($categories)->toHaveCount(1);

            $categoryData = $categories[0];

            // Verify all expected fields are present
            expect($categoryData)->toHaveKeys([
                'id', 'name', 'created_at', 'updated_at', 'products_count',
            ]);

            // Verify field values are not null or empty where applicable
            expect($categoryData['id'])->not->toBeNull();
            expect($categoryData['name'])->not->toBeEmpty();
            expect($categoryData['created_at'])->not->toBeNull();
            expect($categoryData['updated_at'])->not->toBeNull();
            expect($categoryData['products_count'])->toBeGreaterThanOrEqual(0);
        });

        test('products count updates correctly when products are added/removed', function () {
            $category = Category::factory()->create(['name' => 'Dynamic Category']);

            // Initially no products
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $categories = $response->json('categories');
            $categoryData = collect($categories)->firstWhere('name', 'Dynamic Category');
            expect($categoryData['products_count'])->toBe(0);

            // Add products
            Product::factory()->count(3)->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $categories = $response->json('categories');
            $categoryData = collect($categories)->firstWhere('name', 'Dynamic Category');
            expect($categoryData['products_count'])->toBe(3);
        });

        test('handles categories with special characters in name', function () {
            $specialNames = [
                'Electronics & Gadgets',
                'Books/Magazines',
                'Toys & Games (Kids)',
                'Health & Beauty',
                'Home & Garden',
                'Sports & Outdoors',
            ];

            foreach ($specialNames as $name) {
                Category::factory()->create(['name' => $name]);
            }

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $response->assertStatus(200);

            $categories = $response->json('categories');
            expect($categories)->toHaveCount(count($specialNames));

            $returnedNames = collect($categories)->pluck('name')->toArray();

            foreach ($specialNames as $name) {
                expect($returnedNames)->toContain($name);
            }
        });

        test('performance with categories having many products', function () {
            // Create categories with many products each
            $category1 = Category::factory()->create(['name' => 'Popular Category 1']);
            $category2 = Category::factory()->create(['name' => 'Popular Category 2']);

            Product::factory()->count(50)->create(['category_id' => $category1->id]);
            Product::factory()->count(75)->create(['category_id' => $category2->id]);

            $startTime = microtime(true);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/categories');

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $response->assertStatus(200);

            // Ensure response time is reasonable even with many products
            expect($executionTime)->toBeLessThan(1.0);

            $categories = $response->json('categories');
            $category1Data = collect($categories)->firstWhere('name', 'Popular Category 1');
            $category2Data = collect($categories)->firstWhere('name', 'Popular Category 2');

            expect($category1Data['products_count'])->toBe(50);
            expect($category2Data['products_count'])->toBe(75);
        });
    });
});
