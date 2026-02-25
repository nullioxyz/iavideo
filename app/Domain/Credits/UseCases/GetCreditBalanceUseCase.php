<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;

final class GetCreditBalanceUseCase
{
    public function __construct(
        private readonly CreditWalletInterface $wallet,
    ) {}

    public function execute(User $user): int
    {
        return $this->wallet->getBalance($user);
    }
}
