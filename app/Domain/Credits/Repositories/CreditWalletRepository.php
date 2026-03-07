<?php

namespace App\Domain\Credits\Repositories;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Models\CreditLedger;
use Illuminate\Support\Facades\DB;

class CreditWalletRepository implements \App\Domain\Credits\Contracts\Repositories\CreditWalletRepositoryInterface
{
    public function charge(User $user, int $amount, array $data = []): User
    {
        return DB::transaction(function () use ($user, $amount, $data): User {
            // Lock the wallet row so balance validation and debit stay atomic under concurrent requests.
            /** @var User|null $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser instanceof User) {
                throw new \RuntimeException('User not found.');
            }

            if ($this->isAlreadyApplied($data['idempotency_key'] ?? null)) {
                return $lockedUser->refresh();
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
        $delta = ! $isRefund ? -$amount : +$amount;
        $balanceAfter = ! $isRefund ? $currentCreditBalance - $amount : $currentCreditBalance + $amount;

        $payload = [
            'delta' => $delta,
            'balance_before' => $currentCreditBalance,
            'balance_after' => $balanceAfter,
            'reason' => (string) ($data['reason'] ?? ($isRefund ? 'Credits refund' : 'Credits charge')),
            'operation_type' => $data['operation_type'] ?? ($isRefund ? 'credit_refund' : 'credit_debit'),
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => isset($data['reference_id']) ? (int) $data['reference_id'] : null,
            'model_id' => isset($data['model_id']) ? (int) $data['model_id'] : null,
            'preset_id' => isset($data['preset_id']) ? (int) $data['preset_id'] : null,
            'duration_seconds' => isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null,
            'generation_cost_usd' => $data['generation_cost_usd'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'metadata' => isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : null,
            'created_at' => $data['created_at'] ?? now(),
        ];

        $user->creditLedger()->create($payload);
    }

    public function refund(User $user, int $amount, array $data = []): User
    {
        return DB::transaction(function () use ($user, $amount, $data): User {
            // Refunds share the same locking strategy so charge/refund races do not corrupt the balance.
            /** @var User|null $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser instanceof User) {
                throw new \RuntimeException('User not found.');
            }

            if ($this->isAlreadyApplied($data['idempotency_key'] ?? null)) {
                return $lockedUser->refresh();
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

    private function isAlreadyApplied(mixed $idempotencyKey): bool
    {
        if (! is_string($idempotencyKey) || trim($idempotencyKey) === '') {
            return false;
        }

        return CreditLedger::query()
            ->where('idempotency_key', $idempotencyKey)
            ->exists();
    }
}
