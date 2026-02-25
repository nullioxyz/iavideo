<?php

use App\Domain\Payments\Controllers\CreateCreditPurchaseController;
use App\Domain\Payments\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
        'throttle:payments-create',
    ])
    ->group(function () {
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::post('/credits/purchase', CreateCreditPurchaseController::class)->name('credits.purchase');
        });
    });

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        'throttle:payments-webhook',
    ])
    ->group(function () {
        Route::prefix('payments/webhook')->name('payments.webhook.')->group(function () {
            Route::post('/{provider}', PaymentWebhookController::class)->name('provider');
        });
    });
