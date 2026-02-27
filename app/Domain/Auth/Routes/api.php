<?php

namespace App\Domain\Auth\Routes;

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Auth\Controllers\FirstLoginResetPasswordController;
use App\Domain\Auth\Controllers\ExchangeImpersonationHashController;
use App\Domain\Auth\Controllers\MeController;
use App\Domain\Auth\Controllers\UpdateUserPreferencesController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api', \App\Http\Middleware\SetLocale::class])
    ->group(function () {
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('/login', AuthController::class)
                ->middleware('throttle:auth-login')
                ->name('login');
        });
    });

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
        'throttle:auth-user-main',
    ])
    ->group(function () {
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::get('/me', MeController::class)->name('me');
            Route::patch('/preferences', UpdateUserPreferencesController::class)->name('preferences.update');
            Route::post('/first-login/reset-password', FirstLoginResetPasswordController::class)->name('first-login.reset-password');
            Route::post('/impersonation/exchange', ExchangeImpersonationHashController::class)->name('impersonation.exchange');
        });
    });
