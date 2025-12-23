<?php

namespace App\Domain\AIProviders\DTO;

final class ModelCreateCommandDTO
{
    public function __construct(
        public readonly array $payload,
        public readonly array $headers = []
    ) {}
}
