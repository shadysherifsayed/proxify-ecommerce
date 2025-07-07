<?php

namespace App\Managers\Cache;

use Illuminate\Support\Facades\Cache;

class ProductCacheManager extends CacheManager
{
    private const PRODUCT_SINGLE = 'products:single';

    private const PRODUCT_LIST = 'products:list';

    /**
     * Remember the product list cache.
     *
     * @param  array  $context  Context for the product list, e.g., filters, sort options.
     * @param  callable  $callback  Callback to fetch the product list if not cached.
     * @param  int  $ttl  Time to live for the cache in seconds. Default is 1 hour.
     */
    public function rememberList(
        array $context,
        callable $callback,
        int $ttl = self::TTL,
    ) {
        $key = $this->getProductListKey($context);

        return $this->remember(
            $key,
            self::PRODUCT_LIST,
            $callback,
            $ttl,
        );
    }

    /**
     * Remember a single product cache.
     *
     * @param  int  $productId  ID of the product to cache.
     * @param  callable  $callback  Callback to fetch the product if not cached.
     * @param  int  $ttl  Time to live for the cache in seconds. Default is 1 hour.
     */
    public function rememberSingle(
        int $productId,
        callable $callback,
        int $ttl = self::TTL
    ) {
        $key = $this->getProductKey($productId);

        return $this->remember(
            $key,
            self::PRODUCT_SINGLE,
            $callback,
            $ttl,
        );
    }

    /**
     * Get the cache key for the product list.
     */
    private function getProductListKey(array $context): string
    {
        return self::PRODUCT_LIST.':'.$this->generateHashKey($context);
    }

    /**
     * Get the cache key for a single product.
     */
    private function getProductKey(int $productId): string
    {
        return self::PRODUCT_SINGLE.":{$productId}";
    }

    /**
     * Invalidate the cache for a single product.
     *
     * @param  int  $productId  ID of the product to invalidate.
     */
    public function invalidateProductCache(int $productId): void
    {
        $key = $this->getProductKey($productId);

        $this->forgetKey(
            [self::PRODUCT_SINGLE],
            $key
        );
    }

    /**
     * Invalidate the cache for all products.
     *
     * This will flush the entire product list cache.
     */
    public function invalidateProductsListCache(): void
    {
        $this->forgetTag([self::PRODUCT_LIST]);
    }
}
