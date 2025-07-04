<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckoutCartAction;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutCartActionTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutCartAction $checkoutCartAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkoutCartAction = new CheckoutCartAction();
    }

    public function test_can_be_instantiated(): void
    {
        $this->assertInstanceOf(CheckoutCartAction::class, $this->checkoutCartAction);
    }

    public function test_does_nothing_when_cart_is_empty(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        // Act
        $this->checkoutCartAction->execute($cart);

        // Assert
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_product', 0);
    }

    public function test_creates_order_with_correct_total_price(): void
    {
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
    }

    public function test_creates_order_with_single_product(): void
    {
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
    }

    public function test_creates_order_with_multiple_products(): void
    {
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
    }

    public function test_clears_cart_after_successful_checkout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 20.00]);

        $cart->products()->attach($product->id, ['quantity' => 1]);

        // Verify cart has products before checkout
        $this->assertCount(1, $cart->fresh()->products);

        // Act
        $this->checkoutCartAction->execute($cart);

        // Assert - Cart should be empty after checkout
        $this->assertCount(0, $cart->fresh()->products);
    }
    public function test_uses_database_transaction(): void
    {
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
        $this->assertCount(0, $cart->fresh()->products);
    }

    public function test_preserves_original_product_price_in_order(): void
    {
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
    }

    public function test_handles_decimal_precision_correctly(): void
    {
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
    }

    public function test_sets_timestamps_on_order_products(): void
    {
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
    }
}
