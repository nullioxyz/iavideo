<?php

namespace App\Domain\Credits\Repositories;

use App\Domain\Auth\Models\User;

class CreditWalletRepository implements \App\Domain\Credits\Contracts\Repositories\CreditWalletRepositoryInterface
{
    public function charge(User $user, int $amount, array $data = []): User
    {
        $currentCreditBalance = $user->credit_balance;
        $user->credit_balance -= $amount;
        $user->save();

        $user->creditLedger()->create(
            array_merge($data, [
                'delta' => -$amount,
                'balance_after' => $currentCreditBalance - $amount,
            ])
        );

        $this->createCreditLedgerEntry($user, $amount, $currentCreditBalance, $data);

        return $user->refresh();
    }

    public function createCreditLedgerEntry(User $user, int $amount, int $currentCreditBalance, array $data = [], bool $isRefund = false): void
    {
        $user->creditLedger()->create(
            array_merge($data, [
                'delta' => !$isRefund ? -$amount : +$amount,
                'balance_after' => !$isRefund ? $currentCreditBalance - $amount : $currentCreditBalance + $amount,
            ])
        );

        $user->save();
    }

    public function refund(User $user, int $amount, array $data = []): User
    {   
        $this->createCreditLedgerEntry($user, $amount, $user->credit_balance, $data, true);
        $user->credit_balance += $amount;
        $user->save();
        
        return $user;
    }

    public function getBalance(User $user): int
    {
        return $user->credit_balance;
    }
}
