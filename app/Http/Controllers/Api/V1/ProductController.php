<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
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

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return response()->json(compact('product'));
    }

    /**
     * Update product image separately
     */
    public function updateImage(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ]);

        $url = $this->productService->updateProductImage($product, $request->file('image'));

        return response()->json(compact('url'));
    }
}
