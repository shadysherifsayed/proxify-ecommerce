<?php

namespace App\Managers\Cache;

use Illuminate\Support\Facades\Cache;

abstract class CacheManager
{
    protected const TTL = 3600; // Default TTL for cache (1 hour)

    /**
     * Check if the cache store supports tagging.
     *
     * @return bool True if tags are supported, false otherwise.
     */
    protected function isTagSupported(): bool
    {
        $store = Cache::getStore();

        return method_exists($store, 'tags');
    }

    /**
     * Remember a value in the cache with a specific key and tag.
     *
     * @param string $key The cache key.
     * @param string $tag The tag to associate with the cache entry.
     * @param callable $callback The callback to execute if the cache entry does not exist.
     * @param int|null $ttl Time to live for the cache entry in seconds. Default is 1 hour.
     * @return mixed The cached value.
     */
    protected function remember(string $key, string $tag, callable $callback, ?int $ttl = null): mixed
    {
        if ($this->isTagSupported()) {
            return Cache::tags($tag)->remember($key, $ttl ?? static::TTL, $callback);
        }

        return Cache::remember($key, $ttl ?? static::TTL, $callback);
    }

    /**
     * Forget all cache entries for the given tags.
     *
     * @param array|string $tags Tags to flush.
     * @return bool True if the cache was successfully flushed, false otherwise.
     */
    protected function forgetTag(array|string $tags = []): bool
    {
        if ($this->isTagSupported()) {
            return Cache::tags($tags)->flush();
        }
        return Cache::flush();
    }

    /**
     * Forget a specific key from the cache.
     *
     * @param array|string $tags Tags to which the key belongs.
     * @param string $key The cache key to forget.
     * @return bool True if the key was successfully forgotten, false otherwise.
     */
    protected function forgetKey(array|string $tags, string $key): bool
    {
        if ($this->isTagSupported()) {
            return Cache::tags($tags)->forget($key);
        }

        return Cache::forget($key);
    }

    /**
     * Generate a unique hash key based on the provided context.
     *
     * @param mixed ...$context Variable number of context parameters to generate the hash key.
     * @return string The generated hash key.
     */
    protected function generateHashKey(...$context): string
    {
        return md5(serialize($context));
    }
}
