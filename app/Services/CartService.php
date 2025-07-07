<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Managers\Cache\CartCacheManager;
use App\Exceptions\Cart\CartOperationFailedException;

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
            fn () => $user->cart()->firstOrCreate()->load('products')
        );
    }

    /**
     * Add a product to the cart.
     *
     * @param  \App\Models\User  $user  The user to add product to cart for
     * @param  \App\Models\Product  $product  The product to add to the cart
     * @param  int  $quantity  The quantity of the product to add
     * @throws CartOperationFailedException When the cart operation fails
     */
    public function addProduct(User $user, Product $product, int $quantity): void
    {
        try {
            DB::transaction(function () use ($user, $product, $quantity) {
                // Use lockForUpdate to prevent concurrent modifications
                $cart = $user->cart()->lockForUpdate()->firstOrCreate();

                $cart->products()->syncWithoutDetaching([
                    $product->id => [
                        'quantity' => $quantity,
                    ],
                ]);
            });

            // Invalidate cart cache after successful transaction
            $this->cartCacheManager->invalidateCartCache($user->id);
        } catch (\Exception $e) {
            throw new CartOperationFailedException('Failed to add product to cart: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove a product from the cart.
     *
     * @param  \App\Models\User  $user  The user to remove product from cart for
     * @param  \App\Models\Product  $product  The product to remove from the cart
     * @throws CartOperationFailedException When the cart operation fails
     */
    public function removeProduct(User $user, Product $product): void
    {
        try {
            DB::transaction(function () use ($user, $product) {
                // Use lockForUpdate to prevent concurrent modifications
                $cart = $user->cart()->lockForUpdate()->firstOrCreate();

                $cart->products()->detach($product->id);
            });

            // Invalidate cart cache after successful transaction
            $this->cartCacheManager->invalidateCartCache($user->id);
        } catch (\Exception $e) {
            throw new CartOperationFailedException('Failed to remove product from cart: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clear the cart.
     *
     * @param  \App\Models\User  $user  The user whose cart to clear
     * @throws CartOperationFailedException When the cart operation fails
     */
    public function clearCart(User $user): void
    {
        try {
            DB::transaction(function () use ($user) {
                // Use lockForUpdate to prevent concurrent modifications
                $cart = $user->cart()->lockForUpdate()->firstOrCreate();

                $cart->products()->detach();
            });

            // Invalidate cart cache after successful transaction
            $this->cartCacheManager->invalidateCartCache($user->id);
        } catch (\Exception $e) {
            throw new CartOperationFailedException('Failed to clear cart: ' . $e->getMessage(), 0, $e);
        }
    }
}
