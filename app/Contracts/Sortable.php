<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Sortable
{
    /**
     * Apply sorting to the query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort(Builder $query, string $sortBy = 'id', string $sortDirection = 'asc');
}
