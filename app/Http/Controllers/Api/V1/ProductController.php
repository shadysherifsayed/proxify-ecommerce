<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return response()->json(compact('product'));
    }
}
