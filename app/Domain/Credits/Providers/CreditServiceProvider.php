<?php

namespace App\Domain\Credits\Providers;

use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\AIProviders\Infra\Replicate\ReplicateClient;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Credits\Wallet\CreditWallet;
use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Repositories\InputRepository;
use App\Infra\Contracts\InputImageIngestionInterface;
use App\Infra\Uploads\InputImageIngestionService;
use Illuminate\Support\ServiceProvider;

class CreditServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CreditWalletInterface::class, CreditWallet::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
