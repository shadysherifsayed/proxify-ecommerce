<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderService
{
    public function listOrders(User $user): Collection
    {
        return $user->orders()
            ->with(['products' => fn (BelongsToMany  $query) => $query->withPivot(['quantity', 'price'])])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function getOrder(Order $order): Order
    {
        return $order->load(['products' => fn (BelongsToMany  $query) => $query->withPivot(['quantity', 'price'])]);
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->load(['products' => fn (BelongsToMany  $query) => $query->withPivot(['quantity', 'price'])]);
    }
}
