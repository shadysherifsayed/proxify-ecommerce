<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Managers\Cache\OrderCacheManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderService
{
    public function __construct(
        private OrderCacheManager $orderCacheManager
    ) {}

    public function listOrders(User $user, array $filters = [], array $sort = [], int $page = 1): LengthAwarePaginator
    {
        return $this->orderCacheManager->rememberListForUser(
            $user->id,
            [$filters, $sort, compact('page')],
            fn() => $user->orders()
                ->with(['products' => fn(BelongsToMany $query) => $query->withPivot(['quantity', 'price'])])
                ->filter($filters)
                ->sort($sort['field'] ?? 'created_at', $sort['direction'] ?? 'desc')
                ->paginate(10, ['*'], 'page', $page)
        );
    }

    public function getOrder(Order $order): Order
    {
        return $this->orderCacheManager->rememberSingle(
            $order->id,
            fn() => $order->load(['products' => fn(BelongsToMany $query) => $query->withPivot(['quantity', 'price'])])
        );
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->load(['products' => fn(BelongsToMany $query) => $query->withPivot(['quantity', 'price'])]);
    }
}
