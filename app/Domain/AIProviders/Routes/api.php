<?php

use App\Domain\AIProviders\Controllers\ReplicateWebHookController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api', \App\Http\Middleware\SetLocale::class])
    ->group(function () {
    Route::prefix('webhook')->name('webhook.')->group(function () {
        Route::post('/replicate', ReplicateWebHookController::class)->name('replicate');
    });
});

