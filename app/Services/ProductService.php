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

        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Handle category filtering
        if ($request->has('category') && !empty($request->category)) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

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
