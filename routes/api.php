<?php

use App\Http\Controllers\Api\Internal\PaymentEventController;
use Illuminate\Support\Facades\Route;

Route::post('/internal/v1/payments/events', PaymentEventController::class)
    ->middleware(['throttle:payment-bridge', 'payment.bridge.signature'])
    ->name('api.internal.payments.events');
