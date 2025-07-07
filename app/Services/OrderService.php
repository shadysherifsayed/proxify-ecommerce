<?php

namespace App\Services;

use App\Managers\Cache\OrderCacheManager;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    /**
     * Create a new OrderService instance
     *
     * @param  OrderCacheManager  $orderCacheManager  The cache manager for order operations
     */
    public function __construct(
        private OrderCacheManager $orderCacheManager
    ) {}

    /**
     * List orders for a specific user with filtering, sorting, and pagination
     *
     * Retrieves a paginated list of orders for the given user with their associated
     * products (including quantity and price from pivot table). Results are cached
     * for improved performance.
     *
     * @param  User  $user  The user whose orders to retrieve
     * @param  array  $filters  Optional filters to apply to the query
     * @param  array  $sort  Optional sorting configuration with 'field' and 'direction' keys
     * @param  int  $page  The page number for pagination (default: 1)
     * @return LengthAwarePaginator Paginated collection of orders with products
     */
    public function listOrders(User $user, array $filters = [], array $sort = [], int $page = 1): LengthAwarePaginator
    {
        return $this->orderCacheManager->rememberListForUser(
            $user->id,
            [$filters, $sort, compact('page')],
            fn () => $user->orders()
                ->with(['products' => fn (BelongsToMany $query) => $query->withPivot(['quantity', 'price'])])
                ->filter($filters)
                ->sort($sort['field'] ?? 'created_at', $sort['direction'] ?? 'desc')
                ->paginate(10, ['*'], 'page', $page)
        );
    }

    /**
     * Get a single order with its associated products
     *
     * Retrieves a specific order with its related products (including quantity
     * and price from pivot table). The result is cached for improved performance.
     *
     * @param  Order  $order  The order to retrieve
     * @return Order The order with loaded products relationship
     */
    public function getOrder(Order $order): Order
    {
        return $this->orderCacheManager->rememberSingle(
            $order->id,
            fn () => $order->load(['products' => fn (BelongsToMany $query) => $query->withPivot(['quantity', 'price'])])
        );
    }

    /**
     * Update an existing order with new data
     *
     * Updates the order with the provided data and returns the updated order
     * with its associated products (including quantity and price from pivot table).
     *
     * @param  Order  $order  The order to update
     * @param  array  $data  The data to update the order with
     * @return Order The updated order with loaded products relationship
     */
    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->load(['products' => fn (BelongsToMany $query) => $query->withPivot(['quantity', 'price'])]);
    }
}
