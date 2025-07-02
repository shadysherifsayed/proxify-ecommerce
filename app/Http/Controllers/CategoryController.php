<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private CategoryService $categoryService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->categoryService->list();

        return view('categories.index', compact('categories'));
    }
}
