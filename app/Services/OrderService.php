<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    /**
     * List orders for a user.
     *
     * @param \App\Models\User $user The user to get orders from
     * @return \Illuminate\Database\Eloquent\Collection Collection of orders with products
     */
    public function listOrders(User $user): Collection
    {
        return $user->orders()
            ->with(['products' => function ($query) {
                $query->withPivot(['quantity', 'price']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a specific order for a user.
     *
     * @param \App\Models\User $user The user
     * @param int $orderId The order ID
     * @return \App\Models\Order|null The order or null if not found
     */
    public function getOrder(User $user, int $orderId): ?Order
    {
        return $user->orders()
            ->with(['products' => function ($query) {
                $query->withPivot(['quantity', 'price']);
            }])
            ->find($orderId);
    }

    /**
     * Create an order from a cart.
     *
     * @param \App\Models\Cart $cart The cart to convert to order
     * @return \App\Models\Order The created order
     */
    public function createOrderFromCart(Cart $cart): Order
    {
        $totalPrice = $cart->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });

        $order = Order::create([
            'user_id' => $cart->user_id,
            'total' => $totalPrice,
            'status' => 'pending',
        ]);

        // Copy cart products to order products
        foreach ($cart->products as $product) {
            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
            ]);
        }

        return $order->load(['products' => function ($query) {
            $query->withPivot(['quantity', 'price']);
        }]);
    }

    /**
     * Update order status.
     *
     * @param \App\Models\Order $order The order to update
     * @param string $status The new status
     * @return \App\Models\Order The updated order
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        
        return $order->load(['products' => function ($query) {
            $query->withPivot(['quantity', 'price']);
        }]);
    }

    /**
     * Calculate order total price.
     *
     * @param \App\Models\Order $order The order
     * @return float The total price
     */
    public function calculateTotal(Order $order): float
    {
        return $order->products->sum(function ($product) {
            return $product->pivot->price * $product->pivot->quantity;
        });
    }
}
