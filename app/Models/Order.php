<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Contracts\Sortable;
use App\Enums\OrderStatus;
use App\Filters\OrderFilters;
use App\Traits\Sortable as TraitsSortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model implements Filterable, Sortable
{
    use HasFactory, TraitsSortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'status',
        'user_id',
        'total_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => OrderStatus::class,
        'total_price' => 'float',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products associated with the order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, OrderProduct::class)
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price']);
    }

    /**
     * Apply filters to the orders query.
     *
     * This method uses the OrderFilters class to apply various filtering criteria
     * based on the provided filters array. It allows dynamic filtering of orders
     * based on attributes like user ID, status, price range, etc.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  The query builder instance
     * @param  array  $filters  Associative array of filter criteria (e.g., ['user' => 1, 'min_price' => 100])
     * @return \Illuminate\Database\Eloquent\Builder The modified query with filters applied
     */
    public function scopeFilter($query, array $filters = [])
    {
        (new OrderFilters)->applyFilters($query, $filters);

        return $query;
    }

    /**
     * Get the fields that can be used for sorting.
     *
     * Returns an array of field names that are valid for sorting operations.
     * This is used to validate sort parameters in the Sortable trait.
     *
     * @return array<string> List of sortable field names
     */
    public function sortableFields(): array
    {
        return [
            'id',
            'status',
            'user_id',
            'total_price',
            'created_at',
        ];
    }
}
