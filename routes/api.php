<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CartCheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CartProductController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;

Route::middleware('api', 'auth:sanctum')
    ->group(function () {
        Route::apiResource('categories', CategoryController::class)->only('index');
        Route::apiResource('products', ProductController::class)->only('index');
        Route::apiResource('carts', CartController::class)->only(['index', 'destroy']);
        Route::post('carts/products/{product}', [CartProductController::class, 'store'])->name('carts.products.store');
        Route::delete('carts/products/{product}', [CartProductController::class, 'destroy'])->name('carts.products.destroy');
        Route::post('carts/checkout', CartCheckoutController::class)->name('carts.checkout');
        
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        
        Route::post('logout', LogoutController::class);
    });

Route::middleware('api', 'guest')
    ->group(function () {
        Route::post('login', LoginController::class);
        Route::post('register', RegisterController::class);
    });
