<?php

namespace App\Domain\Institutional\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class InstitutionalRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Institutional/Routes/api.php')
        );

        parent::boot();
    }
}

