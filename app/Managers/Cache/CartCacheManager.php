<?php

namespace App\Managers\Cache;

class CartCacheManager extends CacheManager
{
    private const CART_SINGLE = 'carts:single';

    /**
     * Get the cache key for a user's cart.
     * 
     * @param int $userId The ID of the user whose cart key is to be generated
     * @return string The cache key for the user's cart
     */
    private function getCartKey(int $userId): string
    {
        return self::CART_SINGLE . "{$userId}";
    }

    /**
     * Remember the cart for a user.
     * 
     * @param int $userId The ID of the user whose cart should be cached
     * @param callable $callback The callback to execute if the cart is not cached
     * @param int $ttl The time-to-live for the cache in seconds (default is 3600 seconds)
     * @return mixed The cached cart data
     */
    public function rememberCart(int $userId, callable $callback, int $ttl = self::TTL): mixed
    {
        $key = $this->getCartKey($userId);

        return $this->remember(
            $key,
            self::CART_SINGLE,
            $callback,
            $ttl
        );
    }

    /**
     * Invalidate the cache for a user's cart.
     *
     * @param int $userId The ID of the user whose cart cache should be invalidated
     */
    public function invalidateCartCache(int $userId): void
    {
        $key = $this->getCartKey($userId);

        $this->forgetKey(self::CART_SINGLE, $key);
    }
}
