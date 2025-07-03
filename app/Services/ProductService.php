<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ProductService
{

    public function listProducts(Request $request): CursorPaginator
    {
        $query = Product::with('category');

        return $query->cursorPaginate();
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
