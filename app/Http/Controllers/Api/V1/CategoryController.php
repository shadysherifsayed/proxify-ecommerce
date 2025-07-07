<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CategoryService;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance
     * 
     * @param CategoryService $categoryService Service for category operations
     */
    public function __construct(private CategoryService $categoryService) {}

    /**
     * Display a listing of all categories
     * 
     * Retrieves all product categories along with their associated product counts.
     * Useful for navigation and filtering purposes.
     * 
     * @return \Illuminate\Http\JsonResponse JSON response containing all categories with product counts
     */
    public function index()
    {
        $categories = $this->categoryService->listCategories();

        return response()->json(compact('categories'));
    }
}
