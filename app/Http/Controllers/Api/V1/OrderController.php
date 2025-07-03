<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Requests\Api\V1\Order\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CartService $cartService
    ) {}

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listOrders($request->user());

        return response()->json(compact('orders'));
    }


    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        // @todo
        // Check if order belongs to the authenticated user

        $order = $this->orderService->getOrder($order);

        return response()->json(compact('order'));
    }

    /**
     * Update the specified order's status.
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {

        // @todo
        // Check if order belongs to the authenticated user
        $order = $this->orderService->updateOrder($order, $request->validated());

        return response()->json(compact('order'));
    }
}
