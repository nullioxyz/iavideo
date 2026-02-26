<?php

namespace App\Domain\Credits\Tests\Stress;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\ReserveCreditUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditWalletStressTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_charges_keep_wallet_consistent_under_load(): void
    {
        $user = User::factory()->create([
            'credit_balance' => 120,
        ]);

        /** @var ReserveCreditUseCase $useCase */
        $useCase = $this->app->make(ReserveCreditUseCase::class);

        for ($i = 1; $i <= 120; $i++) {
            $useCase->execute($user, [
                'reason' => 'stress test charge',
                'reference_type' => 'stress_charge',
                'reference_id' => $i,
            ]);
        }

        $this->assertSame(0, (int) $user->fresh()->credit_balance);
        $this->assertSame(120, $user->creditLedger()->where('reference_type', 'stress_charge')->count());

        $this->expectException(\DomainException::class);
        $useCase->execute($user, [
            'reference_type' => 'stress_charge',
            'reference_id' => 999,
        ]);
    }
}

