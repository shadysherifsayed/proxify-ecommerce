<?php

namespace Tests\Feature\Api\V1\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ProductController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('ProductsListing', function () {
        test('returns paginated products successfully when authenticated', function () {
            // Create a category and some products
            $category = Category::factory()->create();
            $products = Product::factory()->count(5)->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'products' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'price',
                                'description',
                                'image',
                                'rating',
                                'reviews_count',
                                'category_id',
                                'external_id',
                                'created_at',
                                'updated_at',
                                'category' => [
                                    'id',
                                    'name',
                                    'created_at',
                                    'updated_at',
                                ],
                            ],
                        ],
                        'path',
                        'per_page',
                        'next_cursor',
                        'next_page_url',
                        'prev_cursor',
                        'prev_page_url',
                    ],
                ]);

            // Verify that products are returned with category relationship
            expect($response->json('products.data'))->toHaveCount(5);

            // Verify each product has category data loaded
            foreach ($response->json('products.data') as $productData) {
                expect($productData['category'])->not->toBeNull();
                expect($productData['category']['id'])->toBe($category->id);
                expect($productData['category']['name'])->toBe($category->name);
            }
        });

        test('returns empty result when no products exist', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $response->assertStatus(200)
                ->assertJson([
                    'products' => [
                        'data' => [],
                    ],
                ]);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/v1/products');

            $response->assertStatus(401);
        });

        test('returns products with correct pagination structure', function () {
            // Create more products to test pagination
            $category = Category::factory()->create();
            Product::factory()->count(20)->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $response->assertStatus(200);

            $responseData = $response->json('products');

            // Verify pagination structure
            expect($responseData)->toHaveKeys(['data', 'path', 'per_page']);
            expect($responseData['data'])->toBeArray();
            expect($responseData['per_page'])->toBeInt();
            expect($responseData['path'])->toBeString();
        });

        test('products are eager loaded with category relationship', function () {
            $category = Category::factory()->create(['name' => 'Test Category']);
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Test Product',
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $response->assertStatus(200);

            $products = $response->json('products.data');
            expect($products)->toHaveCount(1);

            $productData = $products[0];
            expect($productData['title'])->toBe('Test Product');
            expect($productData['category']['name'])->toBe('Test Category');
        });

        test('handles large number of products efficiently', function () {
            $category = Category::factory()->create();
            Product::factory()->count(100)->create(['category_id' => $category->id]);

            $startTime = microtime(true);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $response->assertStatus(200);

            // Ensure response time is reasonable (less than 2 seconds)
            expect($executionTime)->toBeLessThan(2.0);

            // Verify pagination is working (should not return all 100 products)
            $data = $response->json('products.data');
            expect(count($data))->toBeLessThanOrEqual(15); // Default cursor pagination limit
        });

        test('returns correct JSON response format', function () {
            $category = Category::factory()->create();
            Product::factory()->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/products');

            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');

            // Verify the response structure matches what the controller returns
            expect($response->json())->toHaveKey('products');
            expect($response->json('products'))->toHaveKeys(['data']);
        });
    });

    describe('ProductShow', function () {
        test('returns a specific product with category when authenticated', function () {
            $category = Category::factory()->create(['name' => 'Electronics']);
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Test Product',
                'price' => 99.99,
                'description' => 'Test product description',
                'rating' => 4.5,
                'reviews_count' => 10,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'product' => [
                        'id',
                        'title',
                        'price',
                        'description',
                        'image',
                        'rating',
                        'reviews_count',
                        'category_id',
                        'external_id',
                        'created_at',
                        'updated_at',
                        'category' => [
                            'id',
                            'name',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ])
                ->assertJson([
                    'product' => [
                        'id' => $product->id,
                        'title' => 'Test Product',
                        'price' => 99.99,
                        'description' => 'Test product description',
                        'rating' => 4.5,
                        'reviews_count' => 10,
                        'category' => [
                            'id' => $category->id,
                            'name' => 'Electronics',
                        ],
                    ],
                ]);
        });

        test('returns 404 when product does not exist', function () {
            $nonExistentId = 99999;

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$nonExistentId}");

            $response->assertStatus(404);
        });

        test('requires authentication to view product', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $response = $this->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(401);
        });

        test('product is eager loaded with category relationship', function () {
            $category = Category::factory()->create([
                'name' => 'Books',
            ]);
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Laravel Guide',
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(200);

            $productData = $response->json('product');
            expect($productData['title'])->toBe('Laravel Guide');
            expect($productData['category'])->not->toBeNull();
            expect($productData['category']['name'])->toBe('Books');
            expect($productData['category']['id'])->toBe($category->id);
        });

        test('returns correct JSON response format for single product', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');

            // Verify the response structure matches what the controller returns
            expect($response->json())->toHaveKey('product');
            expect($response->json('product'))->toHaveKeys([
                'id',
                'title',
                'price',
                'description',
                'image',
                'rating',
                'reviews_count',
                'category_id',
                'external_id',
                'created_at',
                'updated_at',
                'category',
            ]);
        });

        test('returns product with correct data types', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'price' => 149.99,
                'rating' => 4.8,
                'reviews_count' => 25,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(200);

            $productData = $response->json('product');

            // Verify data types
            expect($productData['id'])->toBeInt();
            expect($productData['price'])->toBeFloat();
            expect($productData['rating'])->toBeFloat();
            expect($productData['reviews_count'])->toBeInt();
            expect($productData['title'])->toBeString();
            expect($productData['description'])->toBeString();
        });

        test('product image attribute is properly formatted', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'image' => 'products/test-image.png',
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products/{$product->id}");

            $response->assertStatus(200);

            $productData = $response->json('product');
            expect($productData['image'])->toBeString();
            expect($productData['image'])->not->toBeEmpty();
        });
    });

    describe('ProductUpdate', function () {
        test('updates product successfully with valid data', function () {
            $category1 = Category::factory()->create(['name' => 'Electronics']);
            $category2 = Category::factory()->create(['name' => 'Books']);

            $product = Product::factory()->create([
                'category_id' => $category1->id,
                'title' => 'Original Title',
                'price' => 50.00,
                'description' => 'Original description',
            ]);

            $updateData = [
                'title' => 'Updated Product Title',
                'price' => 99.99,
                'description' => 'Updated product description',
                'category_id' => $category2->id,
            ];

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'product' => [
                        'id',
                        'title',
                        'price',
                        'description',
                        'category_id',
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ])
                ->assertJson([
                    'product' => [
                        'id' => $product->id,
                        'title' => 'Updated Product Title',
                        'price' => 99.99,
                        'description' => 'Updated product description',
                        'category_id' => $category2->id,
                        'category' => [
                            'id' => $category2->id,
                            'name' => 'Books',
                        ],
                    ],
                ]);

            // Verify the product was actually updated in the database
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'title' => 'Updated Product Title',
                'price' => 99.99,
                'description' => 'Updated product description',
                'category_id' => $category2->id,
            ]);
        });

        test('updates product with partial data', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Original Title',
                'price' => 50.00,
                'description' => 'Original description',
            ]);

            // Only update the title
            $updateData = ['title' => 'Only Title Updated'];

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'product' => [
                        'id' => $product->id,
                        'title' => 'Only Title Updated',
                        'price' => 50.00, // Should remain unchanged
                        'description' => 'Original description', // Should remain unchanged
                    ],
                ]);
        });

        test('requires authentication to update product', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $updateData = ['title' => 'Updated Title'];

            $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

            $response->assertStatus(401);
        });

        test('returns 404 when updating non-existent product', function () {
            $nonExistentId = 99999;
            $updateData = ['title' => 'Updated Title'];

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$nonExistentId}", $updateData);

            $response->assertStatus(404);
        });

        test('validates required data types and constraints', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Test invalid price (negative)
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", ['price' => -10]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);

            // Test invalid title (too long)
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'title' => str_repeat('A', 256), // Exceeds 255 character limit
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);

            // Test invalid description (too long)
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'description' => str_repeat('A', 1001), // Exceeds 1000 character limit
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['description']);
        });

        test('validates category_id exists in database', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $nonExistentCategoryId = 99999;

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'category_id' => $nonExistentCategoryId,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id']);
        });

        test('returns updated product with category relationship loaded', function () {
            $category1 = Category::factory()->create(['name' => 'Original Category']);
            $category2 = Category::factory()->create(['name' => 'New Category']);

            $product = Product::factory()->create(['category_id' => $category1->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'category_id' => $category2->id,
                    'title' => 'Updated Product',
                ]);

            $response->assertStatus(200);

            $productData = $response->json('product');
            expect($productData['category'])->not->toBeNull();
            expect($productData['category']['id'])->toBe($category2->id);
            expect($productData['category']['name'])->toBe('New Category');
        });

        test('handles numeric string conversions correctly', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Send price as string (should be converted to float)
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'price' => '123.45',
                ]);

            $response->assertStatus(200);

            $productData = $response->json('product');
            expect($productData['price'])->toBe(123.45);
            expect($productData['price'])->toBeFloat();
        });

        test('preserves unchanged fields when updating', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Original Title',
                'price' => 50.5,
                'description' => 'Original description',
                'rating' => 4.5,
                'reviews_count' => 10,
                'external_id' => 123,
            ]);

            // Only update title
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'title' => 'New Title Only',
                ]);

            $response->assertStatus(200);

            $productData = $response->json('product');

            // Verify updated field
            expect($productData['title'])->toBe('New Title Only');

            // Verify unchanged fields
            expect($productData['price'])->toBe(50.5);
            expect($productData['description'])->toBe('Original description');
            expect($productData['rating'])->toBe(4.5);
            expect($productData['reviews_count'])->toBe(10);
            expect($productData['external_id'])->toBe(123);
            expect($productData['category_id'])->toBe($category->id);
        });

        test('updates product image successfully via PATCH', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'image' => 'http://example.com/old-image.png',
            ]);

            $imageFile = \Illuminate\Http\Testing\File::fake()->image('new-image.png', 800, 600);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $imageFile,
                ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'product' => [
                        'id',
                        'image',
                        'category',
                    ],
                ]);
            // Verify the product's image was updated in the database
            $product->refresh();
            expect($product->image)->not->toBe('http://example.com/old-image.png');
            expect($product->image)->toBeString();
            expect($product->image)->not->toBeEmpty();
            expect($product->image)->toContain('products/');
        });

        test('updates product with both text fields and image simultaneously', function () {
            $category1 = Category::factory()->create(['name' => 'Electronics']);
            $category2 = Category::factory()->create(['name' => 'Books']);

            $product = Product::factory()->create([
                'category_id' => $category1->id,
                'title' => 'Original Title',
                'price' => 50.00,
                'image' => 'http://example.com/products/old-image.png',
            ]);

            $imageFile = \Illuminate\Http\Testing\File::fake()->image('updated-image.png');

            $updateData = [
                'title' => 'Updated Product Title',
                'price' => 99.99,
                'category_id' => $category2->id,
                'image' => $imageFile,
            ];

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'product' => [
                        'id' => $product->id,
                        'title' => 'Updated Product Title',
                        'price' => 99.99,
                        'category_id' => $category2->id,
                        'category' => [
                            'id' => $category2->id,
                            'name' => 'Books',
                        ],
                    ],
                ]);

            // Verify both text fields and image were updated
            $product->refresh();
            expect($product->title)->toBe('Updated Product Title');
            expect($product->price)->toBe(99.99);
            expect($product->category_id)->toBe($category2->id);
            expect($product->image)->not->toBe('http://example.com/old-image.png');
            expect($product->image)->toContain('products/');
        });

        test('validates image file requirements in product update', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Test invalid file type (text file)
            $textFile = \Illuminate\Http\Testing\File::fake()->create('document.txt', 100);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $textFile,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['image']);

            // Test invalid image format (gif not allowed)
            $gifFile = \Illuminate\Http\Testing\File::fake()->create('image.gif', 100, 'image/gif');

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $gifFile,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['image']);
        });

        test('validates image file size limit in product update', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Create a file larger than 1MB (1024KB)
            $largeImageFile = \Illuminate\Http\Testing\File::fake()->image('large-image.png')->size(1500);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $largeImageFile,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['image']);
        });

        test('updates only image when no other fields provided', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Original Title',
                'price' => 50.00,
                'description' => 'Original description',
                'image' => 'products/old-image.png',
            ]);

            $imageFile = \Illuminate\Http\Testing\File::fake()->image('new-image.png');

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $imageFile,
                ]);

            $response->assertStatus(200);

            $product->refresh();

            // Verify only image was updated, other fields remain unchanged
            expect($product->title)->toBe('Original Title');
            expect($product->price)->toBe(50.00);
            expect($product->description)->toBe('Original description');
            expect($product->category_id)->toBe($category->id);
            expect($product->image)->not->toBe('products/old-image.png');
            expect($product->image)->toContain('products/');
        });

        test('preserves existing image when not provided in update', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Original Title',
                'image' => 'products/existing-image.png',
            ]);

            $originalImage = $product->image;

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'title' => 'Updated Title',
                ]);

            $response->assertStatus(200);

            $product->refresh();

            // Verify image was not changed when not provided in update
            expect($product->image)->toBe($originalImage);
            expect($product->title)->toBe('Updated Title');
        });

        test('handles image file size at limit boundary in product update', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Test file exactly at the size limit (1MB = 1024KB)
            $imageFile = \Illuminate\Http\Testing\File::fake()->image('test-image.png')->size(1024);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $imageFile,
                ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'product' => ['id', 'image'],
                ]);
        });

        test('returns updated product with image path in correct format', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $imageFile = \Illuminate\Http\Testing\File::fake()->image('test-image.png');

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'image' => $imageFile,
                ]);

            $response->assertStatus(200);

            $productData = $response->json('product');
            expect($productData['image'])->toBeString();
            expect($productData['image'])->not->toBeEmpty();
            expect($productData['image'])->toContain('products/');
        });

        test('returns correct JSON response format for updated product', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/products/{$product->id}", [
                    'title' => 'Updated Title',
                ]);

            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');

            // Verify the response structure matches what the controller returns
            expect($response->json())->toHaveKey('product');
            expect($response->json('product'))->toHaveKeys([
                'id',
                'title',
                'price',
                'description',
                'image',
                'rating',
                'reviews_count',
                'category_id',
                'external_id',
                'created_at',
                'updated_at',
                'category',
            ]);
        });
    });
});
