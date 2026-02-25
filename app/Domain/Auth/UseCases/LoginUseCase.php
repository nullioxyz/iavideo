<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\DTO\CredentialsDTO;
use App\Domain\Auth\DTO\LoginContextDTO;
use App\Domain\Auth\DTO\TokenDTO;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Models\LoginAudit;
use App\Domain\Auth\Models\User;

final class LoginUseCase
{
    public function __construct(
        private readonly JwtAuthGatewayInterface $auth
    ) {}

    public function execute(CredentialsDTO $credentials, ?LoginContextDTO $context = null): TokenDTO
    {
        $token = $this->auth->attempt($credentials->email, $credentials->password);

        if (! $token) {
            $this->recordAudit(
                user: null,
                email: $credentials->email,
                context: $context,
                success: false,
                failureReason: 'invalid_credentials',
            );

            throw new InvalidCredentialsException(
                __('validation.invalid_credentials')
            );
        }

        $user = $this->auth->user();

        if (! $user || ! $user->isActive()) {
            $this->auth->logout();
            $this->recordAudit(
                user: $user,
                email: $credentials->email,
                context: $context,
                success: false,
                failureReason: 'inactive_user',
            );
            abort(403, __('validation.inactive_user'));
        }

        if ($user->suspended_at !== null) {
            $this->auth->logout();
            $this->recordAudit(
                user: $user,
                email: $credentials->email,
                context: $context,
                success: false,
                failureReason: 'suspended_user',
            );
            abort(403, __('validation.suspended_user'));
        }

        if ($user->exists) {
            $attributes = [
                'last_login_at' => now(),
                'last_activity_at' => now(),
            ];

            if ($context?->userAgent !== null) {
                $attributes['user_agent'] = $context->userAgent;
            }

            $user->forceFill($attributes)->save();
        }

        $this->recordAudit(
            user: $user,
            email: $credentials->email,
            context: $context,
            success: true,
            failureReason: null,
        );

        return new TokenDTO(
            accessToken: $token,
            tokenType: 'bearer',
            expiresInSeconds: $this->auth->tokenTtlSeconds(),
        );
    }

    private function recordAudit(
        ?User $user,
        string $email,
        ?LoginContextDTO $context,
        bool $success,
        ?string $failureReason
    ): void {
        if (! $context) {
            return;
        }

        LoginAudit::query()->create([
            'user_id' => $user?->getKey(),
            'email' => $email,
            'success' => $success,
            'failure_reason' => $failureReason,
            'ip_address' => $context->ipAddress,
            'forwarded_for' => $context->forwardedFor,
            'country_code' => $context->countryCode,
            'region' => $context->region,
            'city' => $context->city,
            'user_agent' => $context->userAgent,
            'browser' => $context->browser,
            'platform' => $context->platform,
        ]);
    }
}
