<?php

namespace App\Domain\Credits\Contracts\Repositories;

use App\Domain\Auth\Models\User;

interface CreditWalletRepositoryInterface
{
    public function charge(User $user, int $amount, array $data = []): \App\Domain\Auth\Models\User;

    public function refund(User $user, int $amount): \App\Domain\Auth\Models\User;

    public function getBalance(User $user): int;
}
