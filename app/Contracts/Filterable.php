<?php

namespace App\Contracts;

interface Filterable
{
    /**
     * Apply filters to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters);
}
