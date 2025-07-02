<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

// anything except of any route starts with 'api/' will be handled by the web middleware
// and will return the 'app' view, which is the main entry point for the Vue.js application


Route::get('/users/{email}', function ($email) {
    return User::updateOrCreate(
        ['email' => $email],
        ['name' => 'Guest User', 'password' => bcrypt('password')]
    );
});

Route::view('{any}', 'app')->where('any', '.*');
