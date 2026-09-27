<?php

use App\Http\Controllers\XenditPaymentWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/xendit/payment', XenditPaymentWebhookController::class)->name('webhooks.xendit.payment');
