<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function listProducts(): CursorPaginator
    {
        $query = Product::with('category');

        return $query->cursorPaginate();
    }

    public function getProduct(Product $product): Product
    {
        return $product->load('category');
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        if (isset($data['image'])) {
            $this->updateProductImage($product, $data['image']);
        }

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
