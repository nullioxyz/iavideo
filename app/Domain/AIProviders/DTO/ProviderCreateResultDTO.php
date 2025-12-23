<?php

namespace App\Domain\AIProviders\DTO;

final class ProviderCreateResultDTO
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $status,
        public readonly array $responsePayload
    ) {}
}
