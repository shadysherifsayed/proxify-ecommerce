<?php

namespace Tests\Feature\Api\V1\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OrderController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    });

    describe('OrdersListing', function () {
        test('returns paginated orders for authenticated user', function () {
            // Create orders for the authenticated user
            $userOrders = Order::factory()->count(3)->create([
                'user_id' => $this->user->id,
            ]);

            // Create orders for another user (should not be returned)
            Order::factory()->count(2)->create([
                'user_id' => $this->otherUser->id,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/orders');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'orders' => [
                        'data' => [
                            '*' => [
                                'id',
                                'status',
                                'total_price',
                                'user_id',
                                'created_at',
                                'updated_at',
                            ],
                        ],
                    ],
                ])
                ->assertJsonCount(3, 'orders.data');

            // Verify only user's orders are returned
            $returnedOrderIds = collect($response->json('orders.data'))->pluck('id');

            $userOrderIds = $userOrders->pluck('id');

            expect($returnedOrderIds->sort()->values())
                ->toEqual($userOrderIds->sort()->values());
        });

        test('returns empty array when user has no orders', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/orders');

            $response->assertStatus(200)
                ->assertJson(['orders' => []]);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/v1/orders');

            $response->assertStatus(401);
        });
    });

    describe('OrderShow', function () {
        test('returns order details for order owner', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'order' => [
                        'id',
                        'status',
                        'total_price',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ],
                ])
                ->assertJson([
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status->value,
                        'total_price' => $order->total_price,
                        'user_id' => $order->user_id,
                    ],
                ]);
        });

        test('returns 403 when user tries to view another users order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->otherUser->id,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(403);
        });

        test('returns 404 for non-existent order', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/v1/orders/999999');

            $response->assertStatus(404);
        });

        test('requires authentication', function () {
            $order = Order::factory()->create();

            $response = $this->getJson("/api/v1/orders/{$order->id}");

            $response->assertStatus(401);
        });
    });

    describe('OrderUpdate', function () {
        test('updates order status successfully for order owner', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'order' => [
                        'id',
                        'status',
                        'total_price',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ],
                ])
                ->assertJson([
                    'order' => [
                        'id' => $order->id,
                        'status' => OrderStatus::PROCESSING->value,
                    ],
                ]);

            // Verify the order was actually updated in the database
            expect($order->fresh()->status)->toBe(OrderStatus::PROCESSING);
        });

        test('allows processing order to be completed', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PROCESSING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::COMPLETED->value,
                ]);

            $response->assertStatus(200)
                ->assertJson([
                    'order' => [
                        'id' => $order->id,
                        'status' => OrderStatus::COMPLETED->value,
                    ],
                ]);

            expect($order->fresh()->status)->toBe(OrderStatus::COMPLETED);
        });

        test('allows processing order to be cancelled', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PROCESSING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::CANCELLED->value,
                ]);

            $response->assertStatus(200)
                ->assertJson([
                    'order' => [
                        'id' => $order->id,
                        'status' => OrderStatus::CANCELLED->value,
                    ],
                ]);

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
        });

        test('returns 403 when user tries to update another users order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->otherUser->id,
                'status' => OrderStatus::PENDING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(403);
        });

        test('returns validation error for invalid status transition', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            // Try to transition from PENDING directly to COMPLETED (invalid)
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::COMPLETED->value,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
        });

        test('returns validation error for invalid status value', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => 'invalid_status',
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
        });

        test('returns validation error when status is missing', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
        });

        test('cannot update completed order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::COMPLETED,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
        });

        test('cannot update cancelled order', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CANCELLED,
            ]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
        });

        test('returns 404 for non-existent order', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson('/api/v1/orders/999999', [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(404);
        });

        test('requires authentication', function () {
            $order = Order::factory()->create();

            $response = $this->putJson("/api/v1/orders/{$order->id}", [
                'status' => OrderStatus::PROCESSING->value,
            ]);

            $response->assertStatus(401);
        });
    });

    describe('order status transitions', function () {
        test('valid status transition flow from pending to completed', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            // PENDING -> PROCESSING
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(200);
            expect($order->fresh()->status)->toBe(OrderStatus::PROCESSING);

            // PROCESSING -> COMPLETED
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::COMPLETED->value,
                ]);

            $response->assertStatus(200);
            expect($order->fresh()->status)->toBe(OrderStatus::COMPLETED);
        });

        test('valid status transition flow from pending to cancelled', function () {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING,
            ]);

            // PENDING -> PROCESSING
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::PROCESSING->value,
                ]);

            $response->assertStatus(200);

            // PROCESSING -> CANCELLED
            $response = $this->actingAs($this->user, 'sanctum')
                ->putJson("/api/v1/orders/{$order->id}", [
                    'status' => OrderStatus::CANCELLED->value,
                ]);

            $response->assertStatus(200);
            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
        });
    });
});
