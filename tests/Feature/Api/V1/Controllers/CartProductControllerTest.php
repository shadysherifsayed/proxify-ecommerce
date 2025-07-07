<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CartProductController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
        ]);
    });

    describe('AddProductToCart', function () {
        it('adds a product to the user cart with authentication', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 2,
                ]);

            $response->assertStatus(201)
                ->assertJson([
                    'product' => [
                        'id' => $this->product->id,
                        'title' => $this->product->title,
                        'price' => $this->product->price,
                        'description' => $this->product->description,
                        'category_id' => $this->product->category_id,
                    ],
                ]);

            // Verify the product was added to the cart
            $cart = $this->user->cart;
            expect($cart)->not->toBeNull();
            expect($cart->products)->toHaveCount(1);
            expect($cart->products->first()->id)->toBe($this->product->id);
            expect($cart->products->first()->pivot->quantity)->toBe(2);
        });

        it('creates a cart automatically if user does not have one', function () {
            expect($this->user->cart)->toBeNull();

            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 1,
                ]);

            $response->assertStatus(201);

            // Refresh user and check cart was created
            $this->user->refresh();
            expect($this->user->cart)->not->toBeNull();
            expect($this->user->cart->products)->toHaveCount(1);
        });

        it('requires authentication to add product to cart', function () {
            $response = $this->postJson("/api/v1/carts/products/{$this->product->id}", [
                'quantity' => 1,
            ]);

            $response->assertStatus(401);
        });

        it('validates quantity is required', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['quantity']);
        });

        it('validates quantity is an integer', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 'invalid',
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['quantity']);
        });

        it('validates quantity is at least 1', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 0,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['quantity']);

            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => -1,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['quantity']);
        });

        it('returns 404 for non-existent product', function () {
            $nonExistentProductId = 99999;

            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$nonExistentProductId}", [
                    'quantity' => 1,
                ]);

            $response->assertStatus(404);
        });

        it('adds multiple different products to cart', function () {
            $product2 = Product::factory()->create([
                'category_id' => $this->category->id,
            ]);
            $product3 = Product::factory()->create([
                'category_id' => $this->category->id,
            ]);

            // Add first product
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 2,
                ])
                ->assertStatus(201);

            // Add second product
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$product2->id}", [
                    'quantity' => 3,
                ])
                ->assertStatus(201);

            // Add third product
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$product3->id}", [
                    'quantity' => 1,
                ])
                ->assertStatus(201);

            // Verify all products are in cart
            $cart = $this->user->cart->load('products');
            expect($cart->products)->toHaveCount(3);

            $productIds = $cart->products->pluck('id')->toArray();
            expect($productIds)->toContain($this->product->id, $product2->id, $product3->id);
        });

        it('handles adding same product multiple times (using syncWithoutDetaching)', function () {
            // Add product first time
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 2,
                ])
                ->assertStatus(201);

            // Add same product again - should not duplicate, just keep existing
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 3,
                ])
                ->assertStatus(201);

            // Verify only one instance of product in cart
            $cart = $this->user->cart->load('products');
            expect($cart->products)->toHaveCount(1);
            expect($cart->products->first()->id)->toBe($this->product->id);
            // Note: syncWithoutDetaching means the second quantity (3) overwrites the first
            expect($cart->products->first()->pivot->quantity)->toBe(3);
        });

        it('allows adding large quantities', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 1000,
                ]);

            $response->assertStatus(201);

            $cart = $this->user->cart;
            expect($cart->products->first()->pivot->quantity)->toBe(1000);
        });

        it('isolates carts between different users', function () {
            $user2 = User::factory()->create();

            // User 1 adds product
            $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 2,
                ])
                ->assertStatus(201);

            // User 2 adds same product
            $this->actingAs($user2)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 5,
                ])
                ->assertStatus(201);

            // Verify each user has their own cart
            $user1Cart = $this->user->cart->load('products');
            $user2Cart = $user2->cart->load('products');

            expect($user1Cart->id)->not->toBe($user2Cart->id);
            expect($user1Cart->products->first()->pivot->quantity)->toBe(2);
            expect($user2Cart->products->first()->pivot->quantity)->toBe(5);
        });

        it('includes product relationships in response', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/v1/carts/products/{$this->product->id}", [
                    'quantity' => 1,
                ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'product' => [
                        'id',
                        'title',
                        'price',
                        'description',
                        'category_id',
                        'image',
                        'created_at',
                        'updated_at',
                    ],
                ]);
        });
    });

    describe('destroy method', function () {
        beforeEach(function () {
            // Create cart and add products
            $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
            $this->product2 = Product::factory()->create([
                'category_id' => $this->category->id,
            ]);

            // Add products to cart
            $this->cart->products()->attach([
                $this->product->id => ['quantity' => 2],
                $this->product2->id => ['quantity' => 3],
            ]);
        });

        it('removes a product from the user cart with authentication', function () {
            expect($this->cart->products)->toHaveCount(2);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}");

            $response->assertStatus(204);

            // Verify the product was removed
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(1);
            expect($this->cart->products->first()->id)->toBe($this->product2->id);
        });

        it('requires authentication to remove product from cart', function () {
            $response = $this->deleteJson("/api/v1/carts/products/{$this->product->id}");

            $response->assertStatus(401);
        });

        it('returns 404 for non-existent product', function () {
            $nonExistentProductId = 99999;

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$nonExistentProductId}");

            $response->assertStatus(404);
        });

        it('handles removing product not in cart gracefully', function () {
            $productNotInCart = Product::factory()->create([
                'category_id' => $this->category->id,
            ]);

            expect($this->cart->products)->toHaveCount(2);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$productNotInCart->id}");

            $response->assertStatus(204);

            // Cart should remain unchanged
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(2);
        });

        it('creates cart automatically if user does not have one', function () {
            $userWithoutCart = User::factory()->create();
            expect($userWithoutCart->cart)->toBeNull();

            $response = $this->actingAs($userWithoutCart)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}");

            $response->assertStatus(204);

            // Verify cart was created (even though no products to remove)
            $userWithoutCart->refresh();
            expect($userWithoutCart->cart)->not->toBeNull();
            expect($userWithoutCart->cart->products)->toHaveCount(0);
        });

        it('removes only the specified product', function () {
            expect($this->cart->products)->toHaveCount(2);

            // Remove first product
            $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}")
                ->assertStatus(204);

            // Verify only first product was removed
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(1);
            expect($this->cart->products->first()->id)->toBe($this->product2->id);
            expect($this->cart->products->first()->pivot->quantity)->toBe(3);
        });

        it('removes all quantity of a product at once', function () {
            // Verify product has quantity > 1
            $cartProduct = $this->cart->products()->where('product_id', $this->product->id)->first();
            expect($cartProduct->pivot->quantity)->toBe(2);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}");

            $response->assertStatus(204);

            // Verify product is completely removed, not just quantity decreased
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(1);
            expect($this->cart->products->pluck('id'))->not->toContain($this->product->id);
        });

        it('isolates cart operations between different users', function () {
            $user2 = User::factory()->create();
            $cart2 = Cart::factory()->create(['user_id' => $user2->id]);
            $cart2->products()->attach([
                $this->product->id => ['quantity' => 5],
                $this->product2->id => ['quantity' => 1],
            ]);

            // User 1 removes product from their cart
            $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}")
                ->assertStatus(204);

            // Verify user 1's cart is updated
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(1);

            // Verify user 2's cart is unchanged
            $cart2->refresh();
            $cart2->load('products');
            expect($cart2->products)->toHaveCount(2);
            expect($cart2->products->where('id', $this->product->id)->first()->pivot->quantity)->toBe(5);
        });

        it('handles empty cart gracefully', function () {
            // Clear the cart first
            $this->cart->products()->detach();
            $this->cart->refresh();
            expect($this->cart->products)->toHaveCount(0);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/v1/carts/products/{$this->product->id}");

            $response->assertStatus(204);

            // Cart should remain empty
            $this->cart->refresh();
            $this->cart->load('products');
            expect($this->cart->products)->toHaveCount(0);
        });
    });
    describe('performance and edge cases', function () {
        it('handles concurrent operations safely', function () {
            // Simulate concurrent adds (this is a basic test, real concurrency testing would need specialized tools)
            $responses = [];

            for ($i = 0; $i < 3; $i++) {
                $responses[] = $this->actingAs($this->user)
                    ->postJson("/api/v1/carts/products/{$this->product->id}", [
                        'quantity' => 1,
                    ]);
            }

            // All requests should succeed
            foreach ($responses as $response) {
                $response->assertStatus(201);
            }

            // Should only have one instance of the product due to syncWithoutDetaching
            $cart = $this->user->cart->load('products');
            expect($cart->products)->toHaveCount(1);
        });

        it('handles multiple operations on cart efficiently', function () {
            $products = Product::factory()->count(5)->create([
                'category_id' => $this->category->id,
            ]);

            // Add multiple products
            foreach ($products as $product) {
                $this->actingAs($this->user)
                    ->postJson("/api/v1/carts/products/{$product->id}", [
                        'quantity' => rand(1, 10),
                    ])
                    ->assertStatus(201);
            }

            // Remove some products
            foreach ($products->take(3) as $product) {
                $this->actingAs($this->user)
                    ->deleteJson("/api/v1/carts/products/{$product->id}")
                    ->assertStatus(204);
            }

            // Verify final state
            $cart = $this->user->cart->load('products');
            expect($cart->products)->toHaveCount(2);
        });
    });
});
