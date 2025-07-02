<?php

namespace Tests\Feature\API\V1;

use App\Models\Product;
use App\Models\Category;


describe('ProductController Feature Tests', function () {
    it('can list products with pagination', function () {
        // Arrange
        $category = Category::factory()->create(['name' => 'Electronics']);
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        // Act
        $response = $this->get('/api/v1/products');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'price',
                    'description',
                    'image',
                    'rating',
                    'reviews',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'category' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ],
            'next_cursor',
            'next_page_url',
            'path',
            'per_page',
            'prev_cursor',
            'prev_page_url',
        ]);
    });

    it('returns products with their categories', function () {
        // Arrange
        $category = Category::factory()->create(['name' => 'Books']);
        $product = Product::factory()->create([
            'title' => 'Test Product',
            'category_id' => $category->id
        ]);        // Act
        $response = $this->get('/api/v1/products');

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();

        expect($responseData['data'])->toHaveCount(1);
        expect($responseData['data'][0]['title'])->toBe('Test Product');
        expect($responseData['data'][0]['category']['name'])->toBe('Books');
    });
    it('returns empty data when no products exist', function () {
        // Act
        $response = $this->get('/api/v1/products');

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();
        expect($responseData['data'])->toBeEmpty();
    });

    it('handles pagination correctly', function () {
        // Arrange
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id]);

        // Act
        $response = $this->get('/api/v1/products');

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();

        expect($responseData['data'])->toHaveCount(15); // Default pagination size
        expect($responseData['next_cursor'])->not->toBeNull();
    });

    it('can handle cursor pagination for next page', function () {
        // Arrange
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id]);

        // Get first page
        $firstPageResponse = $this->get('/api/v1/products');
        $firstPageData = $firstPageResponse->json();
        $nextCursor = $firstPageData['next_cursor'];

        // Act - Get second page
        $secondPageResponse = $this->get('/api/v1/products?cursor=' . $nextCursor);

        // Assert
        $secondPageResponse->assertStatus(200);
        $secondPageData = $secondPageResponse->json();

        expect($secondPageData['data'])->toHaveCount(5); // Remaining products
        expect($secondPageData['prev_cursor'])->not->toBeNull();
    });

    it('handles request with query parameters gracefully', function () {
        // Arrange
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        // Act - Send request with additional query parameters
        $response = $this->get('/api/v1/products?search=test&filter=category');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'next_cursor',
            'next_page_url',
            'path',
            'per_page',
            'prev_cursor',
            'prev_page_url',
        ]);
    });
});
