<?php

namespace App\Domain\Invites\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Invites\Contracts\Repositories\InviteRepositoryInterface;
use App\Domain\Invites\DTO\InviteCreateDTO;
use App\Domain\Invites\Models\Invite;
use Illuminate\Support\Str;

final class CreateInviteUseCase
{
    public function __construct(
        private readonly InviteRepositoryInterface $repository,
    ) {}

    public function execute(User $invitedBy, InviteCreateDTO $dto): Invite
    {
        return $this->repository->create(
            $dto->toArray(
                invitedByUserId: $invitedBy->getKey(),
                token: Str::uuid()->toString(),
            )
        );
    }
}
