<?php

namespace App\Domain\AIProviders\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AIProvidersRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/AIProviders/Routes/api.php')
        );

        parent::boot();
    }
}
