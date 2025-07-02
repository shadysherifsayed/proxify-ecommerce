<?php

namespace App\Services;

use App\Models\User;

class CartService
{
    /**
     * List products in the order.
     *
     * @param \App\Models\Order $order The order to get products from
     * @return \Illuminate\Support\Collection Collection of products with pivot data (quantity, price)
     */
    public function listOrders(User $user): \Illuminate\Support\Collection
    {
        return $user->orders()->with('products')->get();
    }
}
