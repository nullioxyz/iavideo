<?php

namespace App\Domain\AIProviders\DTO;

final class CreatePredictionResultDTO
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $status,
        public readonly array $requestPayload,
        public readonly array $responsePayload
    ) {}
}
