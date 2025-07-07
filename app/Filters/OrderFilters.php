<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OrderFilters
{
    /**
     * Apply the filters to the query.
     *
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters)
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
     */
    public function user(Builder $query, int $userId): self
    {
        $query->where('user_id', $userId);

        return $this;
    }

    /**
     * Filter orders by status.
     */
    public function status(Builder $query, string $status): self
    {
        $query->where('status', $status);

        return $this;
    }

    /**
     * Filter orders by minimum price.
     *
     * @return OrderFilters
     */
    public function minPrice(Builder $query, int|float $minPrice)
    {
        $query->where('total_price', '>=', $minPrice);

        return $this;
    }

    /**
     * Filter orders by maximum price.
     */
    public function maxPrice(Builder $query, int|float $maxPrice): self
    {
        $query->where('total_price', '<=', $maxPrice);

        return $this;
    }

    /**
     * Filter orders by min created at.
     *
     * @param  Builder  $query
     */
    public function dateFrom($query, $date): self
    {
        $query->where('created_at', '>=', $date);

        return $this;
    }

    /**
     * Filter orders by max created at.
     *
     * @param  Builder  $query
     */
    public function dateTo($query, $date): self
    {
        $query->where('created_at', '<=', $date);

        return $this;
    }
}
