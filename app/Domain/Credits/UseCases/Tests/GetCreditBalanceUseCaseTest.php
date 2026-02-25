<?php

namespace App\Domain\Credits\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Credits\UseCases\GetCreditBalanceUseCase;
use PHPUnit\Framework\TestCase;

class GetCreditBalanceUseCaseTest extends TestCase
{
    public function test_it_returns_balance_from_wallet_contract(): void
    {
        $wallet = new class implements CreditWalletInterface
        {
            public function charge(User $user, int $amount, array $data = []): void {}

            public function refund(User $user, int $amount, array $data = []): void {}

            public function canCharge(User $user, int $amount): bool
            {
                return true;
            }

            public function getBalance(User $user): int
            {
                return 42;
            }
        };

        $useCase = new GetCreditBalanceUseCase($wallet);

        $user = new User;

        $this->assertSame(42, $useCase->execute($user));
    }
}
