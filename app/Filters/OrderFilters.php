<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OrderFilters
{

    /**
     * Apply the filters to the query.
     *
     * @param Builder $query
     * @param array $filters
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
     *
     * @param Builder $query
     * @param int $userId
     * @return OrderFilters
     */
    public function user(Builder $query, int $userId): self
    {
        $query->where('user_id', $userId);

        return $this;
    }

    /**
     * Filter orders by status.
     *
     * @param Builder $query
     * @param string $status
     * @return OrderFilters
     */
    public function status(Builder $query, string $status): self
    {
        $query->where('status', $status);

        return $this;
    }

    /**
     * Filter orders by minimum price.
     *
     * @param Builder $query
     * @param float|int $minPrice
     * @return OrderFilters
     */
    public function minPrice(Builder $query, int|float $minPrice)
    {
        $query->where('total_price', '>=', $minPrice);

        return $this;
    }
    /**
     * Filter orders by maximum price.
     *
     * @param Builder $query
     * @param float|int $maxPrice
     * @return OrderFilters
     */
    public function maxPrice(Builder $query, int|float $maxPrice): self
    {
        $query->where('total_price', '<=', $maxPrice);

        return $this;
    }


    /**
     * Filter orders by min created at.
     *
     * @param Builder $query
     * @param $date
     * @return OrderFilters
     */
    public function dateFrom($query, $date): self
    {
        $query->where('created_at', '>=', $date);

        return $this;
    }

    /**
     * Filter orders by max created at.
     *
     * @param Builder $query
     * @param $date
     * @return OrderFilters
     */
    public function dateTo($query, $date): self
    {
        $query->where('created_at', '<=', $date);

        return $this;
    }
}
