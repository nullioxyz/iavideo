<?php

use App\Http\Controllers\PublicMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/image/{token}/image', [PublicMediaController::class, 'image'])
    ->where('token', '[A-Za-z0-9\-_]+')
    ->name('public.media.image');

Route::get('/video/{token}/{filename}', [PublicMediaController::class, 'video'])
    ->where([
        'token' => '[A-Za-z0-9\-_]+',
        'filename' => '.+',
    ])
    ->name('public.media.video');
