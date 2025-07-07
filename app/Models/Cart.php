<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    /**
     * The products associated with the cart.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, CartProduct::class)
            ->using(CartProduct::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
