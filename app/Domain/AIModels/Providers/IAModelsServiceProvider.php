<?php

namespace App\Domain\AIModels\Providers;

use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use App\Domain\AIModels\Repositories\AIModelsRepository;
use Illuminate\Support\ServiceProvider;

class IAModelsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIModelsRepositoryInterface::class, AIModelsRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
