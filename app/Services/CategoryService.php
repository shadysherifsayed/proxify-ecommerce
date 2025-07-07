<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function listCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount('products')->get();
    }
}
