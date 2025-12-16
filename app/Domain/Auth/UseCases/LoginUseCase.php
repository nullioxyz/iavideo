<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\DTO\CredentialsDTO;
use App\Domain\Auth\DTO\TokenDTO;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Illuminate\Validation\ValidationException;

final class LoginUseCase
{
    public function __construct(
        private readonly JwtAuthGatewayInterface $auth
    ) {}

    public function execute(CredentialsDTO $credentials): TokenDTO
    {
        $token = $this->auth->attempt($credentials->email, $credentials->password);

        if (!$token) {
            throw new InvalidCredentialsException(
                __('validation.invalid_credentials')
            );
        }

        $user = $this->auth->user();

        if (!$user || !$user->isActive()) {
            $this->auth->logout();
            abort(403, __('validation.inactive_user'));
        }

        return new TokenDTO(
            accessToken: $token,
            tokenType: 'bearer',
            expiresInSeconds: $this->auth->tokenTtlSeconds(),
        );
    }
}
