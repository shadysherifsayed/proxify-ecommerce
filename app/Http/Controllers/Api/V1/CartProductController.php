<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddProductRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartProductController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param  CartService  $cartService  Service for cart operations
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Add a product to the user's cart
     *
     * Adds a specified product with the given quantity to the authenticated user's cart.
     *
     * @param  AddProductRequest  $request  Validated request containing quantity and user authentication
     * @param  Product  $product  The product to add to the cart (route model binding)
     * @return \Illuminate\Http\JsonResponse JSON response with the added product and 201 status
     */
    public function store(AddProductRequest $request, Product $product)
    {
        $this->cartService->addProduct(
            $request->user(),
            $product,
            $request->input('quantity')
        );

        return response()->json(compact('product'), Response::HTTP_CREATED);
    }

    /**
     * Remove a product from the user's cart
     *
     * Removes the specified product from the authenticated user's cart completely,
     * regardless of quantity.
     *
     * @param  Request  $request  Request object containing user authentication
     * @param  Product  $product  The product to remove from the cart (route model binding)
     * @return \Illuminate\Http\Response Empty response with 204 No Content status
     */
    public function destroy(Request $request, Product $product)
    {
        $this->cartService->removeProduct(
            $request->user(),
            $product,
        );

        return response()->noContent();
    }
}
