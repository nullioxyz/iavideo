<?php

namespace App\Domain\AIProviders\Providers;

use App\Domain\AIProviders\Contracts\ProviderRegistryInterface;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\AIProviders\Infra\Replicate\Fake\FakeReplicateClient;
use App\Domain\AIProviders\Infra\Replicate\ReplicateClient;
use App\Domain\AIProviders\Infra\Replicate\ReplicateVideoFromImageProvider;
use Illuminate\Support\ServiceProvider;

class AIProvidersServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {       
        $this->app->singleton(ProviderRegistryInterface::class, function ($app) {
            $replicateClient = app()->environment(['local', 'testing'])
                ? $app->make(FakeReplicateClient::class)
                : $app->make(ReplicateClient::class);

            return new ProviderRegistry([
                'replicate' => $replicateClient,
                // 'openai' => $app->make(OpenAIVideoFromImageProvider::class),
                // 'gemini' => $app->make(GeminiVideoFromImageProvider::class),
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
