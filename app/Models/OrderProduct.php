<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderProduct extends Pivot
{
    /**
     * The attributes that should be cast to native types.
     *
     * Automatically converts database values to appropriate PHP types:
     * - price: Stored as decimal, cast to float for calculations
     * - quantity: Stored as integer
     *
     * @var array<string, string> Attribute name to cast type mapping
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
    ];
}
