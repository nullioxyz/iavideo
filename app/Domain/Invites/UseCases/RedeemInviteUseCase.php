<?php

namespace App\Domain\Invites\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Invites\Contracts\Repositories\InviteRepositoryInterface;
use App\Domain\Invites\DTO\InviteRedeemDTO;
use App\Domain\Invites\Models\Invite;
use Illuminate\Support\Facades\DB;

final class RedeemInviteUseCase
{
    public function __construct(
        private readonly InviteRepositoryInterface $repository,
        private readonly CreditWalletInterface $creditWallet,
    ) {}

    public function execute(User $user, InviteRedeemDTO $dto): Invite
    {
        $invite = $this->repository->findByToken($dto->token);

        if (! $invite) {
            throw new \DomainException('Invite not found');
        }

        if ($invite->used_at !== null) {
            throw new \DomainException('Invite already used');
        }

        if ($invite->expires_at !== null && $invite->expires_at->isPast()) {
            throw new \DomainException('Invite expired');
        }

        if (strtolower((string) $invite->email) !== strtolower((string) $user->email)) {
            throw new \DomainException('Invite email does not match authenticated user');
        }

        return DB::transaction(function () use ($invite, $user) {
            $invite->used_at = now();
            $this->repository->save($invite);

            if ($user->invited_by_user_id === null) {
                $user->invited_by_user_id = $invite->invited_by_user_id;
                $user->save();
            }

            $this->creditWallet->refund($user, (int) $invite->credits_granted, [
                'reason' => 'Invite redeemed',
                'reference_type' => 'invite_redemption',
                'reference_id' => $invite->getKey(),
            ]);

            return $invite->refresh();
        });
    }
}
