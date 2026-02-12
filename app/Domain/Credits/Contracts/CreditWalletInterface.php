<?php

namespace App\Domain\Credits\Contracts;

use App\Domain\Auth\Models\User;

interface CreditWalletInterface
{
    public function charge(User $user, int $amount, array $data = []): void;

    public function refund(User $user, int $amount, array $data): void;

    public function getBalance(User $user): int;

    public function canCharge(User $user, int $amount): bool;
}
