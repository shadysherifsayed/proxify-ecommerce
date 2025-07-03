<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct(private ProductService $productService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->listProducts();

        return response()->json(compact('products'));
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->getProduct($product);

        return response()->json(compact('product'));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct($product, $request->toArray());

        return response()->json(compact('product'));
    }
}
