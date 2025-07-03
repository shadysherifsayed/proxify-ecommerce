<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;

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
    public function removeProduct(User $user, Product $product): void
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach($product->id);
    }

    /**
     * Clear the cart.
     *
     * @param \App\Models\User $user The user whose cart to clear
     * @return void
     */
    public function clearCart(User $user): void
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach();
    }
}
