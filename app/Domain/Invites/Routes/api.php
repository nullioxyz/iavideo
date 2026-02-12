<?php

use App\Domain\Invites\Controllers\RedeemInviteController;
use App\Domain\Invites\Controllers\ValidateInviteController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
    ])
    ->group(function () {
        Route::prefix('invites')->name('invites.')->group(function () {
            Route::post('/validate', ValidateInviteController::class)->name('validate');
        });
    });

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
    ])
    ->group(function () {
        Route::prefix('invites')->name('invites.')->group(function () {
            Route::post('/redeem', RedeemInviteController::class)->name('redeem');
        });
    });
