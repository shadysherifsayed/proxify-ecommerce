<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

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
