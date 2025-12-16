<?php

namespace App\Domain\Auth\Infra\Auth;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\Models\User;

class JwtAuthGateway implements JwtAuthGatewayInterface
{
    public function __construct(
        private readonly string $guard = 'api'
    ) {}

    public function attempt(string $email, string $password): ?string
    {
        $token = auth($this->guard)->attempt([
            'email' => $email,
            'password' => $password,
        ]);

        return $token ?: null;
    }

    public function user(): ?User
    {
        /** @var User|null $user */
        $user = auth($this->guard)->user();

        return $user;
    }

    public function logout(): void
    {
        auth($this->guard)->logout();
    }

    public function tokenTtlSeconds(): int
    {
        return auth($this->guard)->factory()->getTTL() * 60;
    }
}
