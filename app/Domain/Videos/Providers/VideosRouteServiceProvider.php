<?php

namespace App\Domain\Videos\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class VideosRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Videos/Routes/api.php')
        );

        parent::boot();
    }
}
