<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Api\V1\Order\OrderRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderRequest;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * Display a listing of the user's orders.
     */
    public function index(OrderRequest $request): JsonResponse
    {
        $orders = $this->orderService->listOrders(
            $request->user(),
            $request->validated('filters', []),
            $request->validated('sort', []),
            $request->query('page', 1),
        );

        return response()->json(compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order = $this->orderService->getOrder($order);

        return response()->json(compact('order'));
    }

    /**
     * Update the specified order's status.
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        $order = $this->orderService->updateOrder($order, $request->validated());

        return response()->json(compact('order'));
    }
}
