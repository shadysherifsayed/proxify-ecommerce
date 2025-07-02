<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;

Route::middleware('api', 'auth:sanctum')
    ->group(function () {
        Route::apiResource('categories', CategoryController::class)->only('index');
        Route::apiResource('products', ProductController::class)->only('index');
        Route::apiResource('carts/products', CartController::class)->only(['index', 'store', 'destroy']);
        // Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    });

Route::middleware('api', 'guest')
    ->group(function () {
        Route::post('login', LoginController::class);
        Route::post('register', RegisterController::class);
    });
