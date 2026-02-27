<?php

use App\Domain\SocialNetworks\Controllers\SocialNetworkListController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
    ])
    ->group(function () {
        Route::prefix('social-networks')->name('social-networks.')->group(function () {
            Route::get('/', SocialNetworkListController::class)->name('list');
        });
    });

