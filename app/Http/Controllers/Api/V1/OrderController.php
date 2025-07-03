<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CartService $cartService
    ) {}

    /**
     * Display a listing of the user's orders.
     */
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();
        $orders = $this->orderService->listOrders($user);
        
        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order from cart.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get user's cart
        $cart = $this->cartService->getCart($user);
        
        if (!$cart || $cart->products->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty. Cannot create order.',
            ], 400);
        }

        // Create order from cart
        $order = $this->orderService->createOrderFromCart($cart);
        
        // Clear the cart after order creation
        $this->cartService->clearCart($user);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Check if order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $orderWithProducts = $this->orderService->getOrder($user, $order->id);
        
        if (!$orderWithProducts) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'data' => new OrderResource($orderWithProducts),
        ]);
    }

    /**
     * Update the specified order's status.
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Check if order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $updatedOrder = $this->orderService->updateOrderStatus($order, $request->validated()['status']);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => new OrderResource($updatedOrder),
        ]);
    }

    /**
     * Remove the specified order (cancel if pending).
     */
    public function destroy(Order $order): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Check if order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        // Only allow cancellation of pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled.',
            ], 400);
        }

        $cancelledOrder = $this->orderService->updateOrderStatus($order, 'cancelled');

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($cancelledOrder),
        ]);
    }
}
