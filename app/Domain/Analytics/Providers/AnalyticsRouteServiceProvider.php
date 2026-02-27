<?php

namespace App\Domain\Analytics\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AnalyticsRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        Route::middleware('api')
            ->group(__DIR__.'/../Routes/api.php');
    }
}
