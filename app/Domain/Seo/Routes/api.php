<?php

use App\Domain\Seo\Controllers\SeoShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
    ])
    ->group(function () {
        Route::prefix('seo')->name('seo.')->group(function () {
            Route::get('/{slug}', SeoShowController::class)->name('show');
        });
    });

