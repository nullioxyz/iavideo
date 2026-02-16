<?php

namespace App\Domain\Auth\Infra\Auth;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\Models\User;
use RuntimeException;
use Tymon\JWTAuth\JWTGuard;

class JwtAuthGateway implements JwtAuthGatewayInterface
{
    public function __construct(
        private readonly string $guard = 'api'
    ) {}

    public function attempt(string $email, string $password): ?string
    {
        $guard = auth($this->guard);
        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException('Configured auth guard is not a JWT guard.');
        }

        /** @var mixed $token */
        $token = $guard->attempt([
            'email' => $email,
            'password' => $password,
        ]);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return $token;
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
        $guard = auth($this->guard);
        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException('Configured auth guard is not a JWT guard.');
        }

        return $guard->factory()->getTTL() * 60;
    }
}
