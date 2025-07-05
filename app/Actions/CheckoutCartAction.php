<?php

namespace App\Actions;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CheckoutCartAction
{
    /**
     * Execute the checkout process for the given cart.
     *
     * @param  Cart  $cart  The cart to checkout
     */
    public function execute(Cart $cart): void
    {
        if ($cart->products->isEmpty()) {
            return;
        }

        try {
            DB::beginTransaction();

            // Ensure the cart is not empty
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
            throw new \Exception('Checkout failed: '.$e->getMessage());
        }
    }
}
