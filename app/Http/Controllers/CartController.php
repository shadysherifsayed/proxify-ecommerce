<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;

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
        $products = $this->cartService->showDetails(
            $request->user()
        );

        return view('products.index', compact('products'));
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
        $this->cartService->removeProduct(
            $request->user(),
            $request->product
        );
    }
}
