<?php

namespace App\Domain\Auth\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AuthRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Auth/Routes/api.php')
        );

        parent::boot();
    }
}
