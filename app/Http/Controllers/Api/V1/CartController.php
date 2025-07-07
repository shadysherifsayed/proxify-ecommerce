<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function show(Request $request)
    {
        $cart = $this->cartService->getCart(
            $request->user()
        );

        return response()->json(compact('cart'));
    }

    public function destroy(Request $request)
    {
        $this->cartService->clearCart(
            $request->user(),
        );

        return response()->noContent();
    }
}
