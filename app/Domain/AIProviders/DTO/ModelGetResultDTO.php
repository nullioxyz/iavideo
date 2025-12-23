<?php

namespace App\Domain\AIProviders\DTO;

final class ModelGetResultDTO
{
    public function __construct(
        public readonly string $status,            // processing|succeeded|failed
        public readonly ?array $outputUrls = null, // normalizado
        public readonly ?string $errorMessage = null,
        public readonly array $raw = []
    ) {}
}
