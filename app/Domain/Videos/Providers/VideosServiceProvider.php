<?php

namespace App\Domain\Videos\Providers;

use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\AIProviders\Infra\Replicate\ReplicateClient;
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

        $this->app->bind(ProviderRegistry::class, function ($app) {
            return new ProviderRegistry([
                'replicate' => $app->make(ReplicateClient::class),
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
