<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{

    public function listProducts(): \Illuminate\Pagination\CursorPaginator
    {
        return Product::with('category')->cursorPaginate();
    }

    public function showProduct(Product $product): Product
    {
        return $product->load('category');
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->load('category');
    }
}
