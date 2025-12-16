<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        private readonly JwtAuthGatewayInterface $auth
    ) {}

    public function execute(array $credentials): array
    {
        $token = $this->auth->attempt($credentials);

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $user = $this->auth->user();

        if (! $user || ! $user->isActive()) {
            $this->auth->logout();
            abort(403, 'Usuário inativo.');
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth->tokenTtlSeconds(),
        ];
    }
}
