<?php

namespace App\Domain\AIModels\Providers;

use App\Domain\AIModels\Adapters\ModelAdapterRegistry;
use App\Domain\AIModels\Contracts\Adapters\ModelAdapterRegistryInterface;
use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;
use App\Domain\AIModels\Infra\Replicate\KlingV25TurboProAdapter;
use App\Domain\AIModels\Repositories\AIModelsRepository;
use App\Domain\AIModels\Repositories\PresetsRepository;
use Illuminate\Support\ServiceProvider;

class IAModelsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIModelsRepositoryInterface::class, AIModelsRepository::class);
        $this->app->bind(PresetRepositoryInterface::class, PresetsRepository::class);

        $this->app->bind(ModelAdapterRegistryInterface::class, function ($app) {
            return new ModelAdapterRegistry([
                'kwaivgi/kling-v2.5-turbo-pro' => $app->make(KlingV25TurboProAdapter::class),
            ]);
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
