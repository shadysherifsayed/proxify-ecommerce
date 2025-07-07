<?php

use App\Exceptions\Cart\CartOperationFailedException;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cartService = app(CartService::class);
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create(['price' => 19.99]);
});

test('cart service can add product with transaction', function () {
    // Act
    $this->cartService->addProduct($this->user, $this->product, 2);
    
    // Assert
    $cart = $this->user->fresh()->cart;
    expect($cart)->not->toBeNull();
    expect($cart->products)->toHaveCount(1);
    expect($cart->products->first()->pivot->quantity)->toBe(2);
});

test('cart service can remove product with transaction', function () {
    // Arrange - Add product first
    $this->cartService->addProduct($this->user, $this->product, 3);
    
    // Act
    $this->cartService->removeProduct($this->user, $this->product);
    
    // Assert
    $cart = $this->user->fresh()->cart;
    expect($cart->products)->toHaveCount(0);
});

test('cart service can clear cart with transaction', function () {
    // Arrange - Add multiple products
    $product2 = Product::factory()->create(['price' => 29.99]);
    $this->cartService->addProduct($this->user, $this->product, 2);
    $this->cartService->addProduct($this->user, $product2, 1);
    
    // Verify products are in cart
    $cart = $this->user->fresh()->cart;
    expect($cart->products)->toHaveCount(2);
    
    // Act
    $this->cartService->clearCart($this->user);
    
    // Assert
    $cart = $this->user->fresh()->cart;
    expect($cart->products)->toHaveCount(0);
});