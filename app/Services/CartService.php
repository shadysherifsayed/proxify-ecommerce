<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class CartService
{
    /**
     * List products in the cart.
     *
     * @param \App\Models\Order $order The order/cart to get products from
     * @return \Illuminate\Support\Collection Collection of products with pivot data (quantity, price)
     */
    public function getCart(User $user): Cart
    {
        $cart = $user->cart()->firstOrCreate();

        return $cart->load('products');
    }

    /**
     * Add a product to the cart.
     *
     * @param \App\Models\Order $order The order/cart to add the product to
     * @param \App\Models\Product $product The product to add to the cart
     * @param string $quantity The quantity of the product to add
     * @return void
     */
    public function addProduct(User $user, Product $product, int $quantity)
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->syncWithoutDetaching([
            $product->id => [
                'quantity'  => $quantity,
            ]
        ]);
    }

    /**
     * Remove a product from the cart.
     *
     * @param \App\Models\Order $order The order/cart to remove the product from
     * @param \App\Models\Product $product The product to remove from the cart
     * @return void
     */
    public function removeProduct(User $user, Product $product)
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach($product->id);
    }

    /**
     * Clear the cart.
     *
     * @param \App\Models\User $user The user whose cart to clear
     * @return \App\Models\Cart The cleared cart
     */
    public function clearCart(User $user)
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach();

        return $cart;
    }

    /**
     * Checkout the cart.
     *
     * @param \App\Models\User $user The user whose cart to checkout
     * @return \App\Models\Order The created order
     */
    public function checkout(User $user): Order
    {
        $cart = $user->cart()->firstOrCreate();

        if ($cart->products->isEmpty()) {
            throw new \Exception('Cart is empty.');
        }

        // Create an order from the cart
        $order = Order::create([
            'user_id' => $user->id,
            'total'   => $cart->products->sum(function ($product) {
                return $product->pivot->quantity * $product->price;
            }),
        ]);

        // Attach products to the order
        foreach ($cart->products as $product) {
            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'price'    => $product->price,
            ]);
        }

        // Clear the cart after checkout
        $this->clearCart($user);

        return $order;
    }
}
