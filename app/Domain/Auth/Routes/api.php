<?php

namespace App\Domain\Auth\Routes;

use App\Domain\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api', \App\Http\Middleware\SetLocale::class])
    ->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', AuthController::class)->name('login');
    });
});
