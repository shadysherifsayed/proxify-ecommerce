<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\App;
use App\Managers\Cache\ProductCacheManager;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->invalidateProductsListCache();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->invalidateProductCache($product->id);

        $this->invalidateProductsListCache();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->invalidateProductCache($product->id);

        $this->invalidateProductsListCache();
    }


    /**
     * Invalidate the cache for a single product.
     * 
     * @param int $productId
     * @return void
     */
    private function invalidateProductCache(int $productId): void
    {
        App::make(ProductCacheManager::class)->invalidateProductCache($productId);
    }

    /**
     * Invalidate the cache for all products.
     * 
     * @param int $productId
     * @return void
     */
    private function invalidateProductsListCache(): void
    {
        App::make(ProductCacheManager::class)->invalidateProductsListCache();
    }
}
