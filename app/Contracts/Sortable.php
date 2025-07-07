<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Sortable
{
    /**
     * Apply sorting to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param string $sortDirection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort(Builder $query, string $sortBy = 'id', string $sortDirection = 'asc');
}
