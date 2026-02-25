<?php

namespace App\Domain\Payments\DTO;

class CreateCheckoutResultDTO
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $status,
        public readonly ?string $checkoutUrl = null,
        public readonly array $raw = [],
    ) {}
}
