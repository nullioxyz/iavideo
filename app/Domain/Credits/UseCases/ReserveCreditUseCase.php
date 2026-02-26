<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Auth\Models\User;

class ReserveCreditUseCase
{
    const AMOUNT = 1;

    public function __construct(
        private readonly \App\Domain\Credits\Contracts\CreditWalletInterface $creditWallet,
    ) {}

    public function execute(User $user, array $data = []): void
    {
        $this->creditWallet->charge($user, self::AMOUNT, $data);
    }

    public function canCharge(User $user): bool
    {
        return $this->creditWallet->canCharge($user, self::AMOUNT);
    }
}
