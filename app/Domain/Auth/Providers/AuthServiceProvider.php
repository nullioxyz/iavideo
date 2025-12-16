<?php

namespace App\Domain\Auth\Providers;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\Infra\Auth\JwtAuthGateway;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(JwtAuthGatewayInterface::class, function () {
            return new JwtAuthGateway('api');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
