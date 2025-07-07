<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * List all categories with product counts
     * 
     * Retrieves all categories from the database along with the count
     * of products associated with each category.
     * 
     * @return \Illuminate\Database\Eloquent\Collection<int, Category> Collection of categories with product counts
     */
    public function listCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount('products')->get();
    }
}
