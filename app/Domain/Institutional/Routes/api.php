<?php

use App\Domain\Institutional\Controllers\InstitutionalListController;
use App\Domain\Institutional\Controllers\InstitutionalShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
    ])
    ->group(function () {
        Route::prefix('institutional')->name('institutional.')->group(function () {
            Route::get('/', InstitutionalListController::class)->name('list');
            Route::get('/{slug}', InstitutionalShowController::class)->name('show');
        });
    });

