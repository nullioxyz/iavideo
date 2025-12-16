<?php

namespace App\Domain\Auth\DTO;

final class TokenDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresInSeconds,
    ) {}
}
