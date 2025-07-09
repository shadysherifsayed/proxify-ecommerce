<?php

namespace App\Filters;

use Illuminate\Support\Str;
use App\Helpers\DatabaseHelper;
use Illuminate\Database\Eloquent\Builder;

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
     * @param  Builder  $query
     * @param  string  $search
     * @return void
     */
    public function search(Builder $query, string $search): void
    {
        if (empty($search)) {
            return;
        }

        if (strlen($search) < 3 || !DatabaseHelper::supportsFullTextSearch()) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        } else {
            $query->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$search . '*']);
        }
    }
    
    /**
     * Filter products by categories IDs.
     * 
     * @param  Builder  $query
     * @param  array  $categoryIds
     * @return void
     */
    public function categories(Builder $query, array $categoryIds): void
    {
        if (count($categoryIds) === 0) {
            return;
        }

        $query->whereIn('category_id', $categoryIds);
    }

    /**
     * Filter products by minimum price.
     * 
     * @param  Builder  $query
     * @param  int|float  $minPrice
     * @return void
     */
    public function minPrice(Builder $query, int|float $minPrice)
    {
        if (!is_numeric($minPrice) || $minPrice < 0) {
            return;
        }

        $query->where('price', '>=', $minPrice);
    }

    /**
     * Filter products by maximum price.
     *
     * @param  Builder  $query
     * @param  int|float  $maxPrice
     * @return void
     */
    public function maxPrice(Builder $query, int|float $maxPrice): void
    {
        if (!is_numeric($maxPrice) || $maxPrice < 0) {
            return;
        }

        $query->where('price', '<=', $maxPrice);
    }

    /**
     * Filter products by min rating.
     *
     * @param  Builder  $query
     * @param  int|float  $rating
     * @return void
     */
    public function minRating(Builder $query, int|float $rating): void
    {
        if (!is_numeric($rating) || $rating < 0) {
            return;
        }

        $query->where('rating', '>=', $rating);
    }
}
