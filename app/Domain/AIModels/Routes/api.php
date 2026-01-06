<?php

use App\Domain\AIModels\Controllers\AIModelsController;
use App\Domain\AIModels\Controllers\PresetsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
    ])
    ->group(function () {
    Route::prefix('models')->name('models.')->group(function () {
        Route::get('/', AIModelsController::class)->name('list');
        Route::get('{model}/presets', PresetsController::class)->name('presets');
    });
});
