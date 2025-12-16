<?php

namespace App\Domain\Auth\Contracts\Infra;

use App\Domain\Auth\Models\User;

interface JwtAuthGatewayInterface
{
    public function attempt(string $email, string $password): ?string;

    public function user(): ?User;

    public function logout(): void;

    public function tokenTtlSeconds(): int;
}
