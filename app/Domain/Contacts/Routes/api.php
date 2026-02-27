<?php

use App\Domain\Contacts\Controllers\CreateContactController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        'throttle:auth-user-main',
    ])
    ->group(function () {
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::post('/', CreateContactController::class)->name('create');
        });
    });

