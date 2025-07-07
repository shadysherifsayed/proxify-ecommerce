<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Managers\Cache\CartCacheManager;

class CartService
{
    public function __construct(
        private CartCacheManager $cartCacheManager
    ) {}

    /**
     * List products in the cart.
     *
     * @param  \App\Models\User  $user  The user to get cart from
     * @return \App\Models\Cart Cart with products loaded
     */
    public function getCart(User $user): Cart
    {
        return $this->cartCacheManager->rememberCart(
            $user->id,
            fn() => $user->cart()->firstOrCreate()->load('products')
        );
    }

    /**
     * Add a product to the cart.
     *
     * @param  \App\Models\User  $user  The user to add product to cart for
     * @param  \App\Models\Product  $product  The product to add to the cart
     * @param  int  $quantity  The quantity of the product to add
     * @return void
     */
    public function addProduct(User $user, Product $product, int $quantity): void
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->syncWithoutDetaching([
            $product->id => [
                'quantity' => $quantity,
            ],
        ]);

        // Invalidate cart cache
        $this->cartCacheManager->invalidateCartCache($user->id);
    }

    /**
     * Remove a product from the cart.
     *
     * @param  \App\Models\User  $user  The user to remove product from cart for
     * @param  \App\Models\Product  $product  The product to remove from the cart
     */
    public function removeProduct(User $user, Product $product): void
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach($product->id);

        // Invalidate cart cache
        $this->cartCacheManager->invalidateCartCache($user->id);
    }

    /**
     * Clear the cart.
     *
     * @param  \App\Models\User  $user  The user whose cart to clear
     */
    public function clearCart(User $user): void
    {
        $cart = $user->cart()->firstOrCreate();

        $cart->products()->detach();

        // Invalidate cart cache
        $this->cartCacheManager->invalidateCartCache($user->id);
    }
}
