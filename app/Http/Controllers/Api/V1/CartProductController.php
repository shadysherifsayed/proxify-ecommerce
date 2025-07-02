<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;

class CartProductController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Add product to cart
        $this->cartService->addProduct(
            $request->user(),
            $product,
            $request->input('quantity')
        );

        return response()->json(['message' => 'Product added to cart successfully.'], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        $this->cartService->removeProduct(
            $request->user(),
            $product,
        );

        return response()->json(['message' => 'Product removed from cart successfully.'], 204);
    }
}
