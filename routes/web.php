<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SkuController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('products', ProductController::class)->except('show');
    Route::scopeBindings()->group(function () {
        Route::resource('products.skus', SkuController::class)
            ->only('store', 'update', 'destroy');
    });
});

require __DIR__.'/settings.php';
