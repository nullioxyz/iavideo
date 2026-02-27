<?php

namespace App\Domain\Seo\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SeoRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Seo/Routes/api.php')
        );

        parent::boot();
    }
}

