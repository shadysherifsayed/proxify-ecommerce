<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\CursorPaginator;

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

        return $product->load('category');
    }

    /**
     * Update product image
     */
    public function updateProductImage(Product $product, UploadedFile $image): Product
    {

        // Store the new image
        $imagePath = $image->store('products', 'public');

        // Update the product with new image URL
        $product->update(['image' => $imagePath]);

        return $product->load('category');
    }
}
