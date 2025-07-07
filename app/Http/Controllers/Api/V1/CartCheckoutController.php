<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\CheckoutCart;
use Illuminate\Http\Request;

class CartCheckoutController extends Controller
{
    /**
     * Process cart checkout
     *
     * Initiates the checkout process for the authenticated user's cart.
     * Dispatches a background job to handle the order creation and cart processing.
     *
     * @param  Request  $request  Request object containing user authentication
     * @return \Illuminate\Http\Response Empty response with 204 No Content status
     */
    public function __invoke(Request $request)
    {
        CheckoutCart::dispatch($request->user()->cart()->with('products')->firstOrCreate());

        return response()->noContent();
    }
}
