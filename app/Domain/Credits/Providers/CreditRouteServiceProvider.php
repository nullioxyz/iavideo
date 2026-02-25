<?php

namespace App\Domain\Credits\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class CreditRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Credits/Routes/api.php')
        );

        parent::boot();
    }
}
