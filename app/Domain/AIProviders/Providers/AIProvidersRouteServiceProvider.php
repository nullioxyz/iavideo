<?php

namespace App\Domain\AIModels\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AIProvidersRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/AIModels/Routes/api.php')
        );

        parent::boot();
    }
}
