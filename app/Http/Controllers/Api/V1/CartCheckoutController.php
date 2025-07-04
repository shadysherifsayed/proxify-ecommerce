<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\CheckoutCart;
use Illuminate\Http\Request;

class CartCheckoutController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function __invoke(Request $request)
    {
        CheckoutCart::dispatch($request->user()->cart()->with('products')->firstOrCreate());

        return response()->noContent();
    }
}
