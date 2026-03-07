<?php

namespace App\Domain\Credits\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditWalletIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_charge_with_same_idempotency_key_is_applied_once(): void
    {
        $user = User::factory()->create([
            'credit_balance' => 5,
        ]);

        /** @var CreditWalletInterface $wallet */
        $wallet = app(CreditWalletInterface::class);

        $payload = [
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => 10,
            'idempotency_key' => 'input:10:generation:charge',
        ];

        $wallet->charge($user, 2, $payload);
        $wallet->charge($user, 2, $payload);

        $this->assertSame(3, (int) $user->fresh()->credit_balance);
        $this->assertSame(1, $user->creditLedger()->where('idempotency_key', 'input:10:generation:charge')->count());
    }
}
