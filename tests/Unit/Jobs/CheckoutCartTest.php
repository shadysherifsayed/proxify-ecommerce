<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CheckoutCart;
use App\Actions\CheckoutCartAction;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class CheckoutCartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_job_can_be_instantiated_with_cart(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        $job = new CheckoutCart($cart);

        $this->assertInstanceOf(CheckoutCart::class, $job);
    }

    public function test_job_implements_should_queue_interface(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $job = new CheckoutCart($cart);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    public function test_job_uses_queueable_trait(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $job = new CheckoutCart($cart);

        $this->assertTrue(method_exists($job, 'onQueue'));
        $this->assertTrue(method_exists($job, 'delay'));
    }
    public function test_handle_method_calls_checkout_action_with_cart(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 99.99
        ]);
        $cart->products()->attach($product->id, ['quantity' => 2]);

        /** @var CheckoutCartAction|\Mockery\MockInterface $mockAction */
        $mockAction = $this->mock(CheckoutCartAction::class);
        $mockAction->shouldReceive('execute')
            ->once()
            ->with($cart);

        $job = new CheckoutCart($cart);
        $job->handle($mockAction);
    }

    public function test_job_processes_cart_checkout_successfully(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product1 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 50.00
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 75.00
        ]);

        $cart->products()->attach($product1->id, ['quantity' => 2]);
        $cart->products()->attach($product2->id, ['quantity' => 1]);

        // Verify cart has products before checkout
        $this->assertEquals(2, $cart->products()->count());

        $action = new CheckoutCartAction();
        $job = new CheckoutCart($cart);
        $job->handle($action);

        // Verify order was created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(175.00, $order->total_price); // (50 * 2) + (75 * 1)
        $this->assertEquals($this->user->id, $order->user_id);

        // Verify order has products
        $this->assertEquals(2, $order->products()->count());

        // Verify cart is empty after checkout
        $cart->refresh();
        $this->assertEquals(0, $cart->products()->count());
    }

    public function test_job_handles_empty_cart_gracefully(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        // Ensure cart is empty
        $this->assertEquals(0, $cart->products()->count());

        $action = new CheckoutCartAction();
        $job = new CheckoutCart($cart);
        $job->handle($action);

        // Verify no order was created
        $orderCount = Order::where('user_id', $this->user->id)->count();
        $this->assertEquals(0, $orderCount);

        // Cart should remain empty
        $this->assertEquals(0, $cart->products()->count());
    }

    public function test_job_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 99.99
        ]);
        $cart->products()->attach($product->id, ['quantity' => 1]);

        CheckoutCart::dispatch($cart);

        Queue::assertPushed(CheckoutCart::class, function ($job) use ($cart) {
            return $job->cart->id === $cart->id;
        });
    }

    public function test_job_can_be_dispatched_with_delay(): void
    {
        Queue::fake();

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CheckoutCart::dispatch($cart)->delay(now()->addMinutes(5));

        Queue::assertPushed(CheckoutCart::class);
    }

    public function test_job_can_be_dispatched_to_specific_queue(): void
    {
        Queue::fake();

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CheckoutCart::dispatch($cart)->onQueue('checkout');

        Queue::assertPushedOn('checkout', CheckoutCart::class);
    }
    public function test_job_handles_database_transaction_correctly(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00
        ]);
        $cart->products()->attach($product->id, ['quantity' => 1]);

        // Mock the action to throw an exception
        /** @var CheckoutCartAction|\Mockery\MockInterface $mockAction */
        $mockAction = $this->mock(CheckoutCartAction::class);
        $mockAction->shouldReceive('execute')
            ->once()
            ->with($cart)
            ->andThrow(new \Exception('Checkout failed'));

        $job = new CheckoutCart($cart);

        $this->expectException(\Exception::class);
        $job->handle($mockAction);

        // Verify no order was created due to exception
        $orderCount = Order::where('user_id', $this->user->id)->count();
        $this->assertEquals(0, $orderCount);
    }

    public function test_job_processes_multiple_products_with_different_quantities(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        $products = [];
        $expectedTotal = 0;

        for ($i = 1; $i <= 5; $i++) {
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => 10.00 * $i // $10, $20, $30, $40, $50
            ]);
            $quantity = $i; // 1, 2, 3, 4, 5

            $cart->products()->attach($product->id, ['quantity' => $quantity]);
            $products[] = ['product' => $product, 'quantity' => $quantity];
            $expectedTotal += $product->price * $quantity;
        }

        $action = new CheckoutCartAction();
        $job = new CheckoutCart($cart);
        $job->handle($action);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($expectedTotal, $order->total_price); // $10*1 + $20*2 + $30*3 + $40*4 + $50*5 = $550
        $this->assertEquals(5, $order->products()->count());

        // Verify each product has correct quantity and price in order
        foreach ($products as $productData) {
            $orderProduct = $order->products()->where('product_id', $productData['product']->id)->first();
            $this->assertEquals($productData['quantity'], $orderProduct->pivot->quantity);
            $this->assertEquals($productData['product']->price, $orderProduct->pivot->price);
        }
    }

    public function test_job_preserves_original_product_prices_in_order(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00
        ]);
        $cart->products()->attach($product->id, ['quantity' => 2]);

        $action = new CheckoutCartAction();
        $job = new CheckoutCart($cart);
        $job->handle($action);

        $order = Order::where('user_id', $this->user->id)->first();
        $orderProduct = $order->products()->first();

        // Change product price after adding to cart
        $product->update(['price' => 150.00]);

        // Order should have the original price, not the updated price
        $this->assertEquals(100.00, $orderProduct->pivot->price);
        $this->assertEquals(200.00, $order->total_price); // 100 * 2
    }

    public function test_job_handles_cart_with_single_product(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 25
        ]);
        $cart->products()->attach($product->id, ['quantity' => 3]);

        $action = new CheckoutCartAction();
        $job = new CheckoutCart($cart);
        $job->handle($action);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(75, $order->total_price); // 25 * 3
        $this->assertEquals(1, $order->products()->count());

        $orderProduct = $order->products()->first();
        $this->assertEquals(3, $orderProduct->pivot->quantity);
        $this->assertEquals(25, $orderProduct->pivot->price);
    }

    public function test_job_processes_checkout_for_different_users_independently(): void
    {
        $user1 = $this->user;
        $user2 = User::factory()->create();

        $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
        $cart2 = Cart::factory()->create(['user_id' => $user2->id]);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 50.00
        ]);

        $cart1->products()->attach($product->id, ['quantity' => 1]);
        $cart2->products()->attach($product->id, ['quantity' => 2]);

        $action = new CheckoutCartAction();

        // Process both jobs
        $job1 = new CheckoutCart($cart1);
        $job2 = new CheckoutCart($cart2);

        $job1->handle($action);
        $job2->handle($action);

        // Verify separate orders were created
        $order1 = Order::where('user_id', $user1->id)->first();
        $order2 = Order::where('user_id', $user2->id)->first();

        $this->assertNotNull($order1);
        $this->assertNotNull($order2);
        $this->assertNotEquals($order1->id, $order2->id);
        $this->assertEquals(50.00, $order1->total_price);
        $this->assertEquals(100.00, $order2->total_price);
    }
}
