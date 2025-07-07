<?php

namespace App\Services;

use App\Managers\Cache\ProductCacheManager;
use App\Models\Product;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function __construct(
        private ProductCacheManager $productCacheManager
    ) {}

    public function listProducts(array $filters = [], array $sort = [], string $cursor = 'none'): CursorPaginator
    {
        return $this->productCacheManager->rememberList(
            [$filters, $sort, compact("cursor")],
            fn() => Product::with('category')
                ->filter($filters)
                ->sort($sort['field'] ?? 'id', $sort['direction'] ?? 'asc')
                ->cursorPaginate(cursor: $cursor)
        );
    }

    public function getProduct(Product $product): Product
    {
        return $this->productCacheManager->rememberSingle(
            $product->id,
            fn() => $product->load('category')
        );
    }

    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['image'])) {
            $this->updateProductImage($product, $data['image']);
            unset($data['image']); // Remove image from data to avoid overwriting
        }

        $product->update($data);

        // Invalidate cache after update
        // $this->cacheService->invalidateProductCache($product->id);

        return $product->load('category');
    }

    /**
     * Update product image
     */
    private function updateProductImage(Product $product, UploadedFile $image): string
    {
        // Store the new image
        $imagePath = $image->store('products', 'public');

        // Update the product with new image URL
        $product->update(['image' => $imagePath]);

        return $product->image;
    }
}
