<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\DTO\CreateCheckoutResultDTO;

interface PaymentGatewayInterface
{
    public function provider(): string;

    public function createCheckout(array $payload): CreateCheckoutResultDTO;
}
