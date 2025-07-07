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
    /**
     * Create a new controller instance
     * 
     * @param OrderService $orderService Service for order operations
     */
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * Display a paginated listing of the user's orders
     * 
     * Retrieves a paginated list of orders for the authenticated user with optional
     * filtering and sorting. Includes order products with quantities and prices.
     * 
     * @param OrderRequest $request Validated request containing filters, sort, and pagination parameters
     * @return JsonResponse JSON response containing the paginated user orders
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
     * Display a specific order with authorization
     * 
     * Retrieves a single order with its associated products. Enforces authorization
     * to ensure users can only view their own orders.
     * 
     * @param Order $order The order to display (route model binding)
     * @return JsonResponse JSON response containing the order data with products
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order = $this->orderService->getOrder($order);

        return response()->json(compact('order'));
    }

    /**
     * Update an order's status with authorization
     * 
     * Updates the specified order's status and other allowed fields. Enforces
     * authorization to ensure users can only update their own orders.
     * 
     * @param UpdateOrderRequest $request Validated request containing order update data
     * @param Order $order The order to update (route model binding)
     * @return JsonResponse JSON response containing the updated order data
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        $order = $this->orderService->updateOrder($order, $request->validated());

        return response()->json(compact('order'));
    }
}
