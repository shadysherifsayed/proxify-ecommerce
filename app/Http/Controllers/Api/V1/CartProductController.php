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
     * Create a new controller instance.
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Store a newly created resource in storage.
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
     * Remove the specified resource from storage.
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
