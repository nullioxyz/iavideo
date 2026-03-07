<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Auth\Models\User;

class ReserveCreditUseCase
{
    public const AMOUNT = 1;

    public function __construct(
        private readonly \App\Domain\Credits\Contracts\CreditWalletInterface $creditWallet,
    ) {}

    /**
     * @deprecated Use the generation pricing and billing services for new flows.
     *
     * @param  int|array<string, mixed>  $amountOrData
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, int|array $amountOrData = self::AMOUNT, array $data = []): void
    {
        if (is_array($amountOrData)) {
            $this->creditWallet->charge($user, self::AMOUNT, $amountOrData);

            return;
        }

        $this->creditWallet->charge($user, $amountOrData, $data);
    }

    public function canCharge(User $user, int $amount = self::AMOUNT): bool
    {
        return $this->creditWallet->canCharge($user, $amount);
    }
}
