<?php

namespace App\Domain\SocialNetworks\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SocialNetworksRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/SocialNetworks/Routes/api.php')
        );

        parent::boot();
    }
}

