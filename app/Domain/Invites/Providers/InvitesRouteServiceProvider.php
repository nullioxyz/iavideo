<?php

namespace App\Domain\Invites\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class InvitesRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Invites/Routes/api.php')
        );

        parent::boot();
    }
}
