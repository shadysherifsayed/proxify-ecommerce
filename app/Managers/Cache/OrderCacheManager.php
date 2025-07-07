<?php

namespace App\Managers\Cache;

use Illuminate\Support\Facades\Cache;

class OrderCacheManager extends CacheManager
{
    private const ORDER_SINGLE = 'orders:single';

    private const ORDER_LIST = 'orders:list';

    /**
     * Remember the orders list for a user.
     *
     * @param  int  $userId  ID of the user whose orders list to cache.
     * @param  array  $context  Context for the orders list, e.g., filters, sort options.
     * @param  callable  $callback  Callback to fetch the orders list if not cached.
     * @param  int  $ttl  Time to live for the cache in seconds. Default is 1 hour.
     */
    public function rememberListForUser(
        int $userId,
        array $context,
        callable $callback,
        int $ttl = self::TTL
    ): mixed {

        $tag = $this->getOrdersListTag($userId);

        $key = $this->getOrdersListKey($context);

        return $this->remember(
            $key,
            $tag,
            $callback,
            $ttl
        );
    }

    /**
     * Remember a single order cache.
     *
     * @param  int  $orderId  ID of the order to cache.
     * @param  callable  $callback  Callback to fetch the order if not cached.
     * @param  int  $ttl  Time to live for the cache in seconds. Default is 1 hour.
     */
    public function rememberSingle(
        int $orderId,
        callable $callback,
        int $ttl = self::TTL
    ): mixed {
        $key = $this->getOrderKey($orderId);

        return $this->remember(
            $key,
            self::ORDER_SINGLE,
            $callback,
            $ttl
        );
    }

    /**
     * Get the cache tag for a user orders.
     */
    private function getOrdersListTag(int $userId): string
    {
        return self::ORDER_LIST.":{$userId}";
    }

    /**
     * Get the cache key for the orders list.
     */
    private function getOrdersListKey(array $context): string
    {
        return self::ORDER_LIST.':'.$this->generateHashKey($context);
    }

    /**
     * Get the cache key for a single order.
     */
    private function getOrderKey(int $orderId): string
    {
        return self::ORDER_SINGLE.":{$orderId}";
    }

    /**
     * Invalidate the cache for a single order.
     *
     * @param  int  $orderId  ID of the order to invalidate.
     */
    public function invalidateOrderCache(int $orderId): void
    {
        $key = $this->getOrderKey($orderId);

        $this->forgetKey(self::ORDER_SINGLE, $key);
    }

    /**
     * Invalidate the cache for the orders list of a user.
     *
     * @param  int  $userId  ID of the user whose orders list cache to invalidate.
     */
    public function invalidateOrdersListCache(int $userId): void
    {
        $tag = $this->getOrdersListTag($userId);

        $this->forgetTag($tag);
    }
}
