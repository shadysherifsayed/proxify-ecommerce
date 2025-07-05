<?php

namespace Tests\Feature\Api\V1\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CartController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('CartShow', function () {
        test('returns empty cart when user has no cart', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'cart' => [
                        'id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'products',
                    ],
                ])
                ->assertJson([
                    'cart' => [
                        'user_id' => $this->user->id,
                        'products' => [],
                    ],
                ]);
        });

        test('returns cart with products when cart has items', function () {
            $category = Category::factory()->create();
            $product1 = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Product 1',
                'price' => 99.99,
            ]);
            $product2 = Product::factory()->create([
                'category_id' => $category->id,
                'title' => 'Product 2',
                'price' => 149.99,
            ]);

            // Create cart and add products
            $cart = Cart::factory()->create(['user_id' => $this->user->id]);
            $cart->products()->attach($product1->id, ['quantity' => 2]);
            $cart->products()->attach($product2->id, ['quantity' => 1]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'cart' => [
                        'id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'products' => [
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
                                'pivot' => [
                                    'cart_id',
                                    'product_id',
                                    'quantity',
                                    'created_at',
                                    'updated_at',
                                ],
                            ],
                        ],
                    ],
                ]);

            $cart = $response->json('cart');
            expect($cart['products'])->toHaveCount(2);
            expect($cart['user_id'])->toBe($this->user->id);

            // Verify products and quantities
            $cartProducts = collect($cart['products']);
            $cartProduct1 = $cartProducts->firstWhere('id', $product1->id);
            $cartProduct2 = $cartProducts->firstWhere('id', $product2->id);

            expect($cartProduct1['pivot']['quantity'])->toBe(2);
            expect($cartProduct2['pivot']['quantity'])->toBe(1);
        });

        test('requires authentication to view cart', function () {
            $response = $this->getJson('/api/v1/carts');

            $response->assertStatus(401);
        });

        test('creates cart automatically if user does not have one', function () {
            // Ensure user has no cart initially
            expect($this->user->cart)->toBeNull();

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response->assertStatus(200);

            // Verify cart was created
            $this->user->refresh();
            expect($this->user->cart)->not->toBeNull();
            expect($this->user->cart->user_id)->toBe($this->user->id);
        });

        test('returns same cart for multiple requests', function () {
            // First request - creates cart
            $response1 = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response1->assertStatus(200);
            $cartId1 = $response1->json('cart.id');

            // Second request - should return same cart
            $response2 = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response2->assertStatus(200);
            $cartId2 = $response2->json('cart.id');

            expect($cartId1)->toBe($cartId2);
        });

        test('returns correct JSON response format', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response->assertStatus(200)
                ->assertHeader('content-type', 'application/json');

            expect($response->json())->toHaveKey('cart');
            expect($response->json('cart'))->toHaveKeys([
                'id', 'user_id', 'created_at', 'updated_at', 'products',
            ]);
        });

        test('returns products with correct data types', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'price' => 99.99,
                'rating' => 4.5,
                'reviews_count' => 10,
            ]);

            $cart = Cart::factory()->create(['user_id' => $this->user->id]);
            $cart->products()->attach($product->id, ['quantity' => 3]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $response->assertStatus(200);

            $cartData = $response->json('cart');
            $productData = $cartData['products'][0];

            // Verify data types
            expect($cartData['id'])->toBeInt();
            expect($cartData['user_id'])->toBeInt();
            expect($productData['id'])->toBeInt();
            expect($productData['price'])->toBeFloat();
            expect($productData['rating'])->toBeFloat();
            expect($productData['reviews_count'])->toBeInt();
            expect($productData['pivot']['quantity'])->toBeInt();
        });

        test('handles cart with many products efficiently', function () {
            $category = Category::factory()->create();
            $products = Product::factory()->count(50)->create(['category_id' => $category->id]);

            $cart = Cart::factory()->create(['user_id' => $this->user->id]);

            // Add all products to cart
            foreach ($products as $product) {
                $cart->products()->attach($product->id, ['quantity' => rand(1, 5)]);
            }

            $startTime = microtime(true);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/carts');

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $response->assertStatus(200);

            // Ensure response time is reasonable
            expect($executionTime)->toBeLessThan(2.0);

            $cartData = $response->json('cart');
            expect($cartData['products'])->toHaveCount(50);
        });
    });

    describe('CartDestroy', function () {
        test('clears cart successfully when cart has products', function () {
            $category = Category::factory()->create();
            $product1 = Product::factory()->create(['category_id' => $category->id]);
            $product2 = Product::factory()->create(['category_id' => $category->id]);

            // Create cart with products
            $cart = Cart::factory()->create(['user_id' => $this->user->id]);
            $cart->products()->attach($product1->id, ['quantity' => 2]);
            $cart->products()->attach($product2->id, ['quantity' => 1]);

            // Verify cart has products before clearing
            expect($cart->products()->count())->toBe(2);

            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);

            // Verify cart is now empty
            $cart->refresh();
            expect($cart->products()->count())->toBe(0);
        });

        test('clears empty cart without errors', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);

            // Verify cart was created and is empty
            $this->user->refresh();
            expect($this->user->cart)->not->toBeNull();
            expect($this->user->cart->products()->count())->toBe(0);
        });

        test('requires authentication to clear cart', function () {
            $response = $this->deleteJson('/api/v1/carts');

            $response->assertStatus(401);
        });

        test('creates cart if user does not have one during clear operation', function () {
            // Ensure user has no cart initially
            expect($this->user->cart)->toBeNull();

            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);

            // Verify cart was created
            $this->user->refresh();
            expect($this->user->cart)->not->toBeNull();
            expect($this->user->cart->products()->count())->toBe(0);
        });

        test('returns no content response format', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);
            expect($response->getContent())->toBeEmpty();
        });

        test('only clears current user cart', function () {
            $user1 = $this->user;
            $user2 = User::factory()->create();

            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            // Create carts for both users
            $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
            $cart2 = Cart::factory()->create(['user_id' => $user2->id]);

            $cart1->products()->attach($product->id, ['quantity' => 1]);
            $cart2->products()->attach($product->id, ['quantity' => 1]);

            // Clear user1's cart
            $response = $this->actingAs($user1, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);

            // Verify only user1's cart was cleared
            $cart1->refresh();
            $cart2->refresh();

            expect($cart1->products()->count())->toBe(0);
            expect($cart2->products()->count())->toBe(1);
        });

        test('handles clearing cart with many products efficiently', function () {
            $category = Category::factory()->create();
            $products = Product::factory()->count(100)->create(['category_id' => $category->id]);

            $cart = Cart::factory()->create(['user_id' => $this->user->id]);

            // Add all products to cart
            foreach ($products as $product) {
                $cart->products()->attach($product->id, ['quantity' => 1]);
            }

            expect($cart->products()->count())->toBe(100);

            $startTime = microtime(true);

            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $response->assertStatus(204);

            // Ensure response time is reasonable
            expect($executionTime)->toBeLessThan(2.0);

            // Verify all products were removed
            $cart->refresh();
            expect($cart->products()->count())->toBe(0);
        });

        test('preserves cart entity after clearing products', function () {
            $category = Category::factory()->create();
            $product = Product::factory()->create(['category_id' => $category->id]);

            $cart = Cart::factory()->create(['user_id' => $this->user->id]);
            $cart->products()->attach($product->id, ['quantity' => 1]);

            $originalCartId = $cart->id;

            $response = $this->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/v1/carts');

            $response->assertStatus(204);

            // Verify cart still exists but is empty
            $this->assertDatabaseHas('carts', [
                'id' => $originalCartId,
                'user_id' => $this->user->id,
            ]);

            $cart->refresh();
            expect($cart->products()->count())->toBe(0);
        });
    });
});
