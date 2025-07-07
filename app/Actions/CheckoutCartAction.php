<?php

namespace App\Actions;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Cart\CartEmptyException;
use App\Exceptions\Checkout\CheckoutFailedException;

class CheckoutCartAction
{
    /**
     * Execute the checkout process for the given cart.
     *
     * @param  Cart  $cart  The cart to checkout
     * @throws CartEmptyException When the cart is empty
     * @throws CheckoutFailedException When the checkout process fails
     */
    public function execute(Cart $cart): void
    {
        if ($cart->products->isEmpty()) {
            throw new CartEmptyException('Cannot checkout an empty cart');
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'user_id' => $cart->user_id,
                'total_price' => $cart->products->sum(fn ($product) => $product->pivot->quantity * $product->price),
            ]);

            // Attach products to the order
            foreach ($cart->products as $product) {
                $order->products()->attach($product->id, [
                    'quantity' => $product->pivot->quantity,
                    'price' => $product->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $cart->products()->detach();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new CheckoutFailedException('Checkout failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
