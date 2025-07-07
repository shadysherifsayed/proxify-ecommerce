<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductFilters
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
     * Filter products by title and description.
     *
     * @param  string|null  $title
     * @return ProductFilters
     */
    public function search(Builder $query, string $search): static
    {
        $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });

        return $this;
    }

    /**
     * Filter products by categories IDs.
     *
     * @param  int  $categoryId
     * @return ProductFilters
     */
    public function categories(Builder $query, array $categoryIds): static
    {
        $query->whereIn('category_id', $categoryIds);

        return $this;
    }

    /**
     * Filter products by minimum price.
     *
     * @return ProductFilters
     */
    public function minPrice(Builder $query, int|float $minPrice)
    {
        $query->where('price', '>=', $minPrice);

        return $this;
    }

    /**
     * Filter products by maximum price.
     *
     * @return ProductFilters
     */
    public function maxPrice(Builder $query, int|float $maxPrice): static
    {
        $query->where('price', '<=', $maxPrice);

        return $this;
    }

    /**
     * Filter products by min rating.
     *
     * @param  Builder  $query
     * @return ProductFilters
     */
    public function minRating($query, int|float $rating)
    {
        $query->where('rating', '>=', $rating);

        return $this;
    }
}
