<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SkuController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('products', ProductController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::scopeBindings()->group(function () {
        Route::resource('products.skus', SkuController::class)
            ->only('store', 'update', 'destroy');
        Route::resource('warehouses.inventories', InventoryController::class)
            ->only('store', 'update', 'destroy');
    });
});

require __DIR__.'/settings.php';
