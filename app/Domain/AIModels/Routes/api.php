<?php

use App\Domain\AIModels\Controllers\AIModelsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api', \App\Http\Middleware\SetLocale::class])
    ->group(function () {
    Route::prefix('models')->name('models.')->group(function () {
        Route::get('/', AIModelsController::class)->name('list');
    });
});
