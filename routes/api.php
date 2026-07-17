<?php

use App\Http\Controllers\Api\CreateBrokerPaymentController;
use App\Http\Controllers\Api\PayUNotificationController;
use App\Http\Controllers\Api\PayUReturnController;
use Illuminate\Support\Facades\Route;

Route::post('/internal/v1/payments', CreateBrokerPaymentController::class)
    ->middleware(['throttle:payment-bridge', 'bridge.hmac'])
    ->name('bridge.payments.create');

Route::post('/payu/notifications', PayUNotificationController::class)
    ->middleware('throttle:payu-notifications')
    ->name('payu.notifications');

Route::get('/payu/return/{brokerPaymentId}/{token}', PayUReturnController::class)
    ->whereUuid('brokerPaymentId')
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('payu.return');
