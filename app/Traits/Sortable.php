<?php

namespace App\Traits;

trait Sortable
{
    /**
     * Apply sorting to the query based on the provided sort parameters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  The query builder instance
     * @param  array  $sortParams  Associative array of sort parameters (e.g., ['field' => 'price', 'direction' => 'asc'])
     * @return \Illuminate\Database\Eloquent\Builder The modified query with sorting applied
     */
    public function scopeSort($query, string $sortBy = 'id', string $direction = 'asc')
    {
        // Validate sort direction
        $validDirections = ['asc', 'desc'];

        if (! in_array($direction, $validDirections)) {
            $direction = 'asc';
        }

        // Validate sortBy
        $sortableFields = $this->sortableFields();

        if (! in_array($sortBy, $sortableFields)) {
            $sortBy = 'id';
        }

        return $query->orderBy($sortBy, $direction);
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
        ];
    }
}
