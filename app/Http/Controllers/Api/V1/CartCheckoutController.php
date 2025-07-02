<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Http\Controllers\Controller;

class CartCheckoutController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Store a newly created resource in storage.
     */
    public function __invoke(Request $request)
    {
        $order = $this->cartService->checkout($request->user());

        return response()->json($order, 201);
    }

}
