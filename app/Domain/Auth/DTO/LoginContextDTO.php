<?php

namespace App\Domain\Auth\DTO;

final class LoginContextDTO
{
    public function __construct(
        public readonly ?string $ipAddress = null,
        public readonly ?string $forwardedFor = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $region = null,
        public readonly ?string $city = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $browser = null,
        public readonly ?string $platform = null,
    ) {}
}

