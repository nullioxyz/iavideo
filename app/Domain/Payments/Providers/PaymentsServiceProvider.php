<?php

namespace App\Domain\Payments\Providers;

use App\Domain\Payments\Contracts\PaymentGatewayRegistryInterface;
use App\Domain\Payments\Infra\Fake\FakePaymentGateway;
use App\Domain\Payments\Infra\PaymentGatewayRegistry;
use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayRegistryInterface::class, function ($app) {
            return new PaymentGatewayRegistry([
                'fakepay' => $app->make(FakePaymentGateway::class),
            ]);
        });
    }
}
