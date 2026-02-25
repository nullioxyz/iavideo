<?php

namespace App\Domain\Payments\Infra\Fake;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\DTO\CreateCheckoutResultDTO;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'fakepay';
    }

    public function createCheckout(array $payload): CreateCheckoutResultDTO
    {
        $status = (string) config('services.payments.fake.default_checkout_status', 'pending');

        return new CreateCheckoutResultDTO(
            externalId: (string) Str::uuid(),
            status: $status,
            checkoutUrl: 'https://fakepay.local/checkout/'.($payload['order_id'] ?? 'unknown'),
            raw: [
                'provider' => $this->provider(),
                'payload' => $payload,
            ],
        );
    }
}
