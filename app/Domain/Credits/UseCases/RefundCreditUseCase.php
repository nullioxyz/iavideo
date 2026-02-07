<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Auth\Models\User;

class RefundCreditUseCase
{
    const AMOUNT = 1;

    public function __construct(
        private readonly \App\Domain\Credits\Contracts\CreditWalletInterface $creditWallet,
    ) {}

    public function execute(User $user, array $data = []): void
    {
        $this->creditWallet->refund($user, self::AMOUNT, $data);
    }
}
