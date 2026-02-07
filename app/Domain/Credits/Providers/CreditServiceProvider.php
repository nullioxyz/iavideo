<?php

namespace App\Domain\Credits\Providers;

use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Credits\Wallet\CreditWallet;
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
