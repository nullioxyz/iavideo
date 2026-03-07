<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Auth\Models\User;

class RefundCreditUseCase
{
    public const AMOUNT = 1;

    public function __construct(
        private readonly \App\Domain\Credits\Contracts\CreditWalletInterface $creditWallet,
    ) {}

    /**
     * @deprecated Use the generation billing service for generation refunds.
     *
     * @param  int|array<string, mixed>  $amountOrData
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, int|array $amountOrData = self::AMOUNT, array $data = []): void
    {
        if (is_array($amountOrData)) {
            $this->creditWallet->refund($user, self::AMOUNT, $amountOrData);

            return;
        }

        $this->creditWallet->refund($user, $amountOrData, $data);
    }
}
