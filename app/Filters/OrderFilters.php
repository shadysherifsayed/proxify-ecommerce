<?php

namespace App\Filters;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class OrderFilters
{
    /**
     * Apply the filters to the query.
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            $method = Str::camel($key);
            if ($value && method_exists($this, $method)) {
                $this->$method($query, $value);
            }
        }

        return $query;
    }

    /**
     * Filter orders by user ID.
     * 
     * @param  Builder  $query
     * @param  int  $userId
     * @return void
     */
    public function user(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Filter orders by status.
     * 
     * @param  Builder  $query
     * @param  string  $status
     * @return void
     */
    public function status(Builder $query, string $status): void
    {
        $query->where('status', $status);

    }
    /**
     * Filter orders by minimum price.
     * 
     * @param  Builder  $query
     * @param  int|float  $minPrice
     * @return void
     */
    public function minPrice(Builder $query, int|float $minPrice): void
    {
        $query->where('total_price', '>=', $minPrice);
    }

    /**
     * Filter orders by maximum price.
     * 
     * @param  Builder  $query
     * @param  int|float  $maxPrice
     * @return void
     */
    public function maxPrice(Builder $query, int|float $maxPrice): void
    {
        $query->where('total_price', '<=', $maxPrice);
    }

    /**
     * Filter orders by min created at.
     * 
     * @param  Builder  $query
     * @param  string  $date
     * @return void
     */
    public function dateFrom(Builder $query, string $date): void
    {
        $query->where('created_at', '>=', $date);
    }

    /**
     * Filter orders by max created at.
     *
     * @param  Builder  $query
     * @param  string  $date
     * @return void
     */
    public function dateTo(Builder $query, string $date): void
    {
        $query->where('created_at', '<=', $date);
    }
}
