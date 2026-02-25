<?php

namespace App\Domain\Payments\Infra;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Contracts\PaymentGatewayRegistryInterface;

class PaymentGatewayRegistry implements PaymentGatewayRegistryInterface
{
    /**
     * @param  array<string, PaymentGatewayInterface>  $gateways
     */
    public function __construct(private readonly array $gateways) {}

    public function get(string $provider): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$provider])) {
            throw new \InvalidArgumentException("Payment gateway [{$provider}] not configured.");
        }

        return $this->gateways[$provider];
    }
}
