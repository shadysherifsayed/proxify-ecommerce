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
    public function showDetails(User $user): \Illuminate\Support\Collection
    {
        $cart = $user->cart->firstOrCreate();

        return $cart->load('products')->withPivot('quantity');
    }

    /**
     * Add a product to the cart.
     *
     * @param \App\Models\Order $order The order/cart to add the product to
     * @param \App\Models\Product $product The product to add to the cart
     * @param string $quantity The quantity of the product to add
     * @return void
     */
    public function addProduct(User $user, Product $product, string $quantity)
    {
        $cart = $user->cart->firstOrCreate();

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
        $cart = $user->cart->firstOrCreate();

        $cart->products()->detach($product->id);
    }
}
