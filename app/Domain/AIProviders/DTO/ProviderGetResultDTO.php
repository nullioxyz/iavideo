<?php

namespace App\Domain\AIProviders\DTO;

final class ProviderGetResultDTO
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $payload
    ) {}
}
