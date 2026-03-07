<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\Models\User;
use App\Domain\Observability\Support\StructuredActivityLogger;

final class LogoutUseCase
{
    public function __construct(
        private readonly JwtAuthGatewayInterface $auth,
        private readonly StructuredActivityLogger $activityLogger,
    ) {}

    public function execute(User $user): void
    {
        $this->activityLogger->log('logout', $user);
        $this->auth->logout();
    }
}
