<?php

namespace App\Domain\Credits\Repositories;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\DB;

class CreditWalletRepository implements \App\Domain\Credits\Contracts\Repositories\CreditWalletRepositoryInterface
{
    public function charge(User $user, int $amount, array $data = []): User
    {
        return DB::transaction(function () use ($user, $amount, $data): User {
            /** @var User|null $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser instanceof User) {
                throw new \RuntimeException('User not found.');
            }

            if ((int) $lockedUser->credit_balance < $amount) {
                throw new \DomainException('Insufficient balance');
            }

            $currentCreditBalance = (int) $lockedUser->credit_balance;
            $lockedUser->credit_balance = $currentCreditBalance - $amount;
            $lockedUser->save();

            $this->createCreditLedgerEntry($lockedUser, $amount, $currentCreditBalance, $data);

            return $lockedUser->refresh();
        });
    }

    public function createCreditLedgerEntry(User $user, int $amount, int $currentCreditBalance, array $data = [], bool $isRefund = false): void
    {
        $user->creditLedger()->create(
            array_merge($data, [
                'delta' => ! $isRefund ? -$amount : +$amount,
                'balance_after' => ! $isRefund ? $currentCreditBalance - $amount : $currentCreditBalance + $amount,
            ])
        );

    }

    public function refund(User $user, int $amount, array $data = []): User
    {
        return DB::transaction(function () use ($user, $amount, $data): User {
            /** @var User|null $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser instanceof User) {
                throw new \RuntimeException('User not found.');
            }

            $currentBalance = (int) $lockedUser->credit_balance;
            $lockedUser->credit_balance = $currentBalance + $amount;
            $lockedUser->save();

            $this->createCreditLedgerEntry($lockedUser, $amount, $currentBalance, $data, true);

            return $lockedUser->refresh();
        });
    }

    public function getBalance(User $user): int
    {
        return $user->credit_balance;
    }
}
