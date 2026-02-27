<?php

use App\Domain\Analytics\Controllers\MvpKpisController;
use App\Domain\Analytics\Controllers\OpsMetricsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
        'throttle:auth-user-main',
    ])
    ->group(function () {
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/mvp-kpis', MvpKpisController::class)->name('mvp-kpis');
            Route::get('/ops-metrics', OpsMetricsController::class)->name('ops-metrics');
        });
    });
