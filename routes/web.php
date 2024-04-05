<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Users will be redirected to this route if not logged in
Volt::route('/login', 'login')->name('login');
Volt::route('/register', 'register');

// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

// Protected routes here
Route::middleware('auth')->group(function () {
    // Users
    Volt::route('/', 'index');
    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');

    // Materials
    Volt::route('/materials/list', 'materials.list');
    Volt::route('/materials/create', 'materials.create');
    Volt::route('/materials/{material}/edit', 'materials.edit');

    //Products
    Volt::route('/products/list', 'products.list');
    Volt::route('/products/create', 'products.create');
    Volt::route('/products/{product}/edit', 'products.edit');

    //Storage
    Volt::route('/storage/list', 'storage.list');
    Volt::route('/storage/create', 'storage.create');
    Volt::route('/storage/{product}/edit', 'storage.edit');

    // ... more
});
