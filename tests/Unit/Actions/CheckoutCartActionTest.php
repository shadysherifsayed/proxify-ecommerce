<?php

use App\Actions\CheckoutCartAction;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->checkoutCartAction = new CheckoutCartAction;
});

test('can be instantiated', function () {
    expect($this->checkoutCartAction)->toBeInstanceOf(CheckoutCartAction::class);
});

test('throws exception when cart is empty', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    // Act & Assert
    expect(fn () => $this->checkoutCartAction->execute($cart))
        ->toThrow(\App\Exceptions\Cart\CartEmptyException::class, 'Cannot checkout an empty cart');
    
    // Verify no orders were created
    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseCount('order_product', 0);
});

test('creates order with correct total price', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    $product1 = Product::factory()->create(['price' => 10.50]);
    $product2 = Product::factory()->create(['price' => 25.75]);

    // Add products to cart with specific quantities
    $cart->products()->attach($product1->id, ['quantity' => 2]);
    $cart->products()->attach($product2->id, ['quantity' => 1]);

    $expectedTotal = (10.50 * 2) + (25.75 * 1); // 46.75

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_price' => $expectedTotal,
    ]);
});

test('creates order with single product', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 15.99]);

    $cart->products()->attach($product->id, ['quantity' => 3]);

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert
    $order = Order::first();
    $this->assertNotNull($order);
    $this->assertEquals($user->id, $order->user_id);
    $this->assertEquals(15.99 * 3, $order->total_price);

    // Check order products
    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 15.99,
    ]);
});

test('creates order with multiple products', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    $product1 = Product::factory()->create(['price' => 12.00]);
    $product2 = Product::factory()->create(['price' => 8.50]);
    $product3 = Product::factory()->create(['price' => 30.25]);

    $cart->products()->attach($product1->id, ['quantity' => 2]);
    $cart->products()->attach($product2->id, ['quantity' => 4]);
    $cart->products()->attach($product3->id, ['quantity' => 1]);

    $expectedTotal = (12.00 * 2) + (8.50 * 4) + (30.25 * 1); // 88.25

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert
    $order = Order::first();
    $this->assertNotNull($order);
    $this->assertEquals($user->id, $order->user_id);
    $this->assertEquals($expectedTotal, $order->total_price);

    // Verify all products are attached to the order with correct quantities and prices
    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 12.00,
    ]);

    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'quantity' => 4,
        'price' => 8.50,
    ]);

    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product3->id,
        'quantity' => 1,
        'price' => 30.25,
    ]);
});

test('clears cart after successful checkout', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 20.00]);

    $cart->products()->attach($product->id, ['quantity' => 1]);

    // Verify cart has products before checkout
    $this->assertEquals(1, $cart->fresh()->products->count());

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert - Cart should be empty after checkout
    $this->assertEquals(0, $cart->fresh()->products->count());
});

test('uses database transaction', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 15.00]);

    $cart->products()->attach($product->id, ['quantity' => 2]);

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert - verify the transaction completed successfully
    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('order_product', 1);
    $this->assertEquals(0, $cart->fresh()->products->count());
});

test('preserves original product price in order', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 50.00]);

    $cart->products()->attach($product->id, ['quantity' => 1]);

    // Change product price after adding to cart (simulating price change)
    $product->update(['price' => 75.00]);

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert - Order should use the current product price (75.00), not the cart price
    $order = Order::first();
    $this->assertEquals(75.00, $order->total_price);

    $this->assertDatabaseHas('order_product', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 75.00, // Current product price at checkout time
    ]);
});

test('handles decimal precision correctly', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    // Create products with prices that test decimal precision
    $product1 = Product::factory()->create(['price' => 10.99]);
    $product2 = Product::factory()->create(['price' => 15.47]);

    $cart->products()->attach($product1->id, ['quantity' => 3]);
    $cart->products()->attach($product2->id, ['quantity' => 2]);

    $expectedTotal = (10.99 * 3) + (15.47 * 2); // 32.97 + 30.94 = 63.91

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert
    $order = Order::first();
    $this->assertEquals($expectedTotal, $order->total_price);
    $this->assertEquals(63.91, $order->total_price);
});

test('sets timestamps on order products', function () {
    // Arrange
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => 25.00]);

    $cart->products()->attach($product->id, ['quantity' => 1]);

    // Act
    $this->checkoutCartAction->execute($cart);

    // Assert
    $order = Order::first();
    $orderProduct = DB::table('order_product')
        ->where('order_id', $order->id)
        ->where('product_id', $product->id)
        ->first();

    $this->assertNotNull($orderProduct->created_at);
    $this->assertNotNull($orderProduct->updated_at);
});


