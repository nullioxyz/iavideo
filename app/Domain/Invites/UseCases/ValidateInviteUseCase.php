<?php

namespace App\Domain\Invites\UseCases;

use App\Domain\Invites\Contracts\Repositories\InviteRepositoryInterface;

final class ValidateInviteUseCase
{
    public function __construct(
        private readonly InviteRepositoryInterface $repository,
    ) {}

    public function execute(string $hash): bool
    {
        $invite = $this->repository->findByToken($hash);

        if (! $invite) {
            return false;
        }

        if ($invite->used_at !== null) {
            return false;
        }

        if ($invite->expires_at !== null && $invite->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
