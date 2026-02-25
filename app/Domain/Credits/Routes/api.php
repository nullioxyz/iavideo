<?php

use App\Domain\Credits\Controllers\CreditsBalanceController;
use App\Domain\Credits\Controllers\CreditsStatementController;
use App\Domain\Credits\Controllers\CreditsVideoGenerationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
        'throttle:auth-user-main',
    ])
    ->group(function () {
        Route::prefix('credits')->name('credits.')->group(function () {
            Route::get('/balance', CreditsBalanceController::class)->name('balance');
            Route::get('/statement', CreditsStatementController::class)->name('statement');
            Route::get('/video-generations', CreditsVideoGenerationsController::class)->name('video-generations');
        });
    });
