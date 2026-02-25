<?php

namespace App\Domain\Broadcasting\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Broadcast::routes([
            'prefix' => 'api',
            'middleware' => [
                'api',
                \App\Http\Middleware\SetLocale::class,
                \App\Domain\Auth\Middleware\JwtAuth::class,
            ],
        ]);

        require base_path('routes/channels.php');
    }
}
