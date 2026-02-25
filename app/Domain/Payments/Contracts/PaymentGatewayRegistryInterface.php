<?php

namespace App\Domain\Payments\Contracts;

interface PaymentGatewayRegistryInterface
{
    public function get(string $provider): PaymentGatewayInterface;
}
