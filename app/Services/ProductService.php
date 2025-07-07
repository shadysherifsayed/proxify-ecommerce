<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use App\Managers\Cache\ProductCacheManager;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ProductService
{
    /**
     * Create a new ProductService instance
     * 
     * @param ProductCacheManager $productCacheManager The cache manager for product operations
     */
    public function __construct(
        private ProductCacheManager $productCacheManager
    ) {}

    /**
     * List products with filtering, sorting, and cursor pagination
     * 
     * Retrieves a cursor-paginated list of products with their associated categories.
     * Supports filtering and sorting options. Results are cached for improved performance.
     * 
     * @param array $filters Optional filters to apply to the query
     * @param array $sort Optional sorting configuration with 'field' and 'direction' keys
     * @param string $cursor Cursor for pagination (default: 'none')
     * @return CursorPaginator Cursor-paginated collection of products with categories
     */
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

    /**
     * Get a single product with its associated category
     * 
     * Retrieves a specific product with its related category.
     * The result is cached for improved performance.
     * 
     * @param Product $product The product to retrieve
     * @return Product The product with loaded category relationship
     */
    public function getProduct(Product $product): Product
    {
        return $this->productCacheManager->rememberSingle(
            $product->id,
            fn() => $product->load('category')
        );
    }

    /**
     * Update an existing product with new data
     * 
     * Updates the product with the provided data. If an image is included in the data,
     * it will be processed separately using the updateProductImage method.
     * Returns the updated product with its category relationship loaded.
     * 
     * @param Product $product The product to update
     * @param array $data The data to update the product with (may include 'image' for file upload)
     * @return Product The updated product with loaded category relationship
     */
    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['image'])) {
            $this->updateProductImage($product, $data['image']);
            unset($data['image']); // Remove image from data to avoid overwriting
        }

        $product->update($data);

        return $product->load('category');
    }

    /**
     * Update product image
     * 
     * Handles the upload and storage of a new product image. Stores the image
     * in the 'products' directory within public storage and updates the product
     * record with the new image path.
     * 
     * @param Product $product The product to update the image for
     * @param UploadedFile $image The uploaded image file
     * @return string The path to the stored image
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
