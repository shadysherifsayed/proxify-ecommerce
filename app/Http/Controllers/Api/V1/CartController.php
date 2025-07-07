<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param  CartService  $cartService  Service for cart operations
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Display the user's cart contents
     *
     * Retrieves the current user's cart with all products, quantities, and totals.
     *
     * @param  Request  $request  Request object containing user authentication
     * @return \Illuminate\Http\JsonResponse JSON response containing the cart data
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->getCart(
            $request->user()
        );

        return response()->json(compact('cart'));
    }

    /**
     * Clear the user's cart
     *
     * Removes all products from the authenticated user's cart.
     *
     * @param  Request  $request  Request object containing user authentication
     * @return \Illuminate\Http\Response Empty response with 204 No Content status
     */
    public function destroy(Request $request)
    {
        $this->cartService->clearCart(
            $request->user(),
        );

        return response()->noContent();
    }
}
