<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CartProduct extends Pivot
{
    protected $table = 'cart_product';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];
}
