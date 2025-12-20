<?php

namespace App\Domain\Videos\Providers;

use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;
use App\Domain\AIModels\Repositories\AIModelsRepository;
use App\Domain\AIModels\Repositories\PresetsRepository;
use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Repositories\InputRepository;
use App\Infra\Contracts\InputImageIngestionInterface;
use App\Infra\Uploads\InputImageIngestionService;
use Illuminate\Support\ServiceProvider;

class VideosServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InputRepositoryInterface::class, InputRepository::class);
        $this->app->bind(InputImageIngestionInterface::class, InputImageIngestionService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
