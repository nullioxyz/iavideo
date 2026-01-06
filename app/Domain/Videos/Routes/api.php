<?php

use App\Domain\Videos\Controllers\InputCreateController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class
    ])
    ->group(function () {
    Route::prefix('input')->name('input.')->group(function () {
        Route::post('/create', InputCreateController::class)->name('create');
    });
});
