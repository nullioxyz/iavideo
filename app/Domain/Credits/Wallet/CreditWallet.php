<?php

namespace App\Domain\Credits\Wallet;

use App\Domain\Credits\Repositories\CreditWalletRepository;

class CreditWallet implements \App\Domain\Credits\Contracts\CreditWalletInterface
{
    public function __construct(
        private readonly CreditWalletRepository $repository
    ) {}

    public function charge(\App\Domain\Auth\Models\User $user, int $amount, array $data = []): void
    {
        $this->repository->charge($user, $amount, $data);
    }

    public function refund(\App\Domain\Auth\Models\User $user, int $amount, array $data = []): void
    {
        $this->repository->refund($user, $amount, $data);
    }

    public function getBalance(\App\Domain\Auth\Models\User $user): int
    {
        return $this->repository->getBalance($user);
    }

    public function canCharge(\App\Domain\Auth\Models\User $user, int $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }
}
