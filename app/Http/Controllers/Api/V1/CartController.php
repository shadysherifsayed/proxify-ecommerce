<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Resources\Json\JsonResource;

class CartController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getCart(
            $request->user()
        );

        return new JsonResource($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->cartService->addProduct(
            $request->user(),
            $request->product,
            $request->quantity
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $this->cartService->clearCart(
            $request->user(),
        );
    }
}
