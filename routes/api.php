<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::prefix('v1')
    ->middleware(['api'])
    ->group(function () {
        Route::apiResource('categories', CategoryController::class)->only('index');
        Route::apiResource('products', ProductController::class)->only('index');
        Route::apiResource('carts/products', CartController::class)->only(['index', 'store', 'destroy']);
        // Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    });
