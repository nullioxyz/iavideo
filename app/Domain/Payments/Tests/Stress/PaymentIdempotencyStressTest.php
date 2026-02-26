<?php

namespace App\Domain\Payments\Tests\Stress;

use App\Domain\Auth\Models\User;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Domain\Payments\UseCases\CreateCreditPurchaseUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIdempotencyStressTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_same_idempotency_key_creates_single_order(): void
    {
        config()->set('services.payments.default_provider', 'fakepay');
        config()->set('services.payments.fake.default_checkout_status', 'pending');

        $user = User::factory()->create();

        /** @var CreateCreditPurchaseUseCase $useCase */
        $useCase = $this->app->make(CreateCreditPurchaseUseCase::class);

        for ($i = 0; $i < 50; $i++) {
            $order = $useCase->execute($user, 10, 'stress-idempotency-key');
            $this->assertSame(10, (int) $order->credits);
        }

        $this->assertSame(1, CreditPurchaseOrder::query()->count());
    }
}

