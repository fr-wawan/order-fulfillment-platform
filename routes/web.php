<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SkuController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('products', ProductController::class)->only('index', 'create', 'store', 'edit', 'update');
    Route::resource('warehouses', WarehouseController::class)->except('show');

    Route::resource('orders', OrderController::class)->only('index', 'create', 'store', 'show');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/fulfill', [OrderController::class, 'fulfill'])->name('orders.fulfill');

    Route::scopeBindings()->group(function () {
        Route::resource('orders.payments', PaymentController::class)
            ->only('store');
        Route::resource('products.skus', SkuController::class)
            ->only('store', 'update');
        Route::resource('warehouses.inventories', InventoryController::class)
            ->only('store', 'update');
    });
});

require __DIR__.'/settings.php';
