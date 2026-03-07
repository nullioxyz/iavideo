<?php

namespace App\Domain\Credits\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Credits\UseCases\ReserveCreditUseCase;
use PHPUnit\Framework\TestCase;

class ReserveCreditUseCaseTest extends TestCase
{
    public function test_it_delegates_charge_to_wallet(): void
    {
        $user = new User;
        $user->id = 123;

        $state = (object) [
            'called' => false,
            'receivedAmount' => 0,
            'receivedData' => [],
        ];

        $wallet = new class($state) implements CreditWalletInterface
        {
            public function __construct(private object $state) {}

            public function charge(User $user, int $amount, array $data = []): void
            {
                $this->state->called = true;
                $this->state->receivedAmount = $amount;
                $this->state->receivedData = $data;
            }

            public function refund(User $user, int $amount, array $data): void {}

            public function getBalance(User $user): int
            {
                return 0;
            }

            public function canCharge(User $user, int $amount): bool
            {
                return true;
            }
        };

        $useCase = new ReserveCreditUseCase($wallet);
        $useCase->execute($user, ['reference_type' => 'input_generation']);

        $this->assertTrue($state->called);
        $this->assertSame(1, $state->receivedAmount);
        $this->assertSame('input_generation', $state->receivedData['reference_type']);
    }
}
