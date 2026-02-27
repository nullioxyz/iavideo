<?php

use App\Domain\Videos\Controllers\CancelInputPredictionController;
use App\Domain\Videos\Controllers\DownloadJobVideoController;
use App\Domain\Videos\Controllers\InputCreateController;
use App\Domain\Videos\Controllers\JobDetailController;
use App\Domain\Videos\Controllers\JobsListController;
use App\Domain\Videos\Controllers\JobsQuotaController;
use App\Domain\Videos\Controllers\RenameInputTitleController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware([
        'api',
        \App\Http\Middleware\SetLocale::class,
        \App\Domain\Auth\Middleware\JwtAuth::class,
        'throttle:auth-user-main',
    ])
    ->group(function () {
        Route::prefix('input')->name('input.')->group(function () {
            Route::post('/create', InputCreateController::class)->name('create');
        });

        Route::prefix('prediction')->name('prediction.')->group(function () {
            Route::post('/cancel', CancelInputPredictionController::class)->name('cancel');
        });

        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', JobsListController::class)->name('list');
            Route::get('/quota', JobsQuotaController::class)->name('quota');
            Route::get('/{job}', JobDetailController::class)->whereNumber('job')->name('detail');
            Route::get('/{job}/download', DownloadJobVideoController::class)->whereNumber('job')->name('download');
            Route::patch('/{job}/title', RenameInputTitleController::class)->whereNumber('job')->name('rename-title');
        });
    });
