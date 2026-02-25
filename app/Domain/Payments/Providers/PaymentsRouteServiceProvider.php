<?php

namespace App\Domain\Payments\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class PaymentsRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('app/Domain/Payments/Routes/api.php'));

        parent::boot();
    }
}
