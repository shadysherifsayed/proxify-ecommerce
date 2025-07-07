<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param  ProductService  $productService  Service for product operations
     */
    public function __construct(private ProductService $productService) {}

    /**
     * Display a paginated listing of products
     *
     * Retrieves a cursor-paginated list of products with optional filtering and sorting.
     * Supports various filters and sort parameters defined in the ProductRequest.
     *
     * @param  ProductRequest  $request  Validated request containing filters, sort, and cursor parameters
     * @return JsonResponse JSON response containing the paginated products
     */
    public function index(ProductRequest $request): JsonResponse
    {
        $products = $this->productService->listProducts(
            $request->validated('filters', []),
            $request->validated('sort', []),
            $request->validated('cursor', ''),
        );

        return response()->json(compact('products'));
    }

    /**
     * Display a specific product
     *
     * Retrieves a single product with its associated category information.
     *
     * @param  Product  $product  The product to display (route model binding)
     * @return JsonResponse JSON response containing the product data
     */
    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->getProduct($product);

        return response()->json(compact('product'));
    }

    /**
     * Update an existing product
     *
     * Updates a product with the provided data including optional image upload.
     * Handles validation through UpdateProductRequest.
     *
     * @param  UpdateProductRequest  $request  Validated request containing product update data
     * @param  Product  $product  The product to update (route model binding)
     * @return JsonResponse JSON response containing the updated product data
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return response()->json(compact('product'));
    }
}
