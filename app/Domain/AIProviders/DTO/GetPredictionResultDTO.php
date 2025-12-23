<?php

namespace App\Domain\AIProviders\DTO;

final class GetPredictionResultDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?array $outputUrls = null,
        public readonly ?string $errorMessage = null,
        public readonly array $responsePayload = [],
        public readonly ?int $processingMs = null,
        public readonly ?int $totalMs = null,
    ) {}
}
