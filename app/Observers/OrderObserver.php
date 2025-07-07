<?php

namespace App\Observers;

use App\Managers\Cache\CartCacheManager;
use App\Managers\Cache\OrderCacheManager;
use App\Models\Order;
use Illuminate\Support\Facades\App;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->invalidateOrdersListCache($order->user_id);

        $this->invalidateCartCache($order->user_id);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $this->invalidateOrderCache($order->id);

        $this->invalidateOrdersListCache($order->user_id);
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $this->invalidateOrderCache($order->id);

        $this->invalidateOrdersListCache($order->user_id);
    }

    /**
     * Invalidate the cache for a single order.
     */
    private function invalidateOrderCache(int $orderId): void
    {
        App::make(OrderCacheManager::class)->invalidateOrderCache($orderId);
    }

    /**
     * Invalidate the cache for all orders.
     *
     * @param  int  $orderId
     */
    private function invalidateOrdersListCache(int $userId): void
    {
        App::make(OrderCacheManager::class)->invalidateOrdersListCache($userId);
    }

    /**
     * Invalidate the cache for a user's cart.
     */
    private function invalidateCartCache(int $user): void
    {
        App::make(CartCacheManager::class)->invalidateCartCache($user);
    }
}
