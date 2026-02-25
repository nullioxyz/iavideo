<?php

namespace App\Domain\Payments\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Domain\Payments\UseCases\CreateCreditPurchaseUseCase;
use App\Domain\Settings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCreditPurchaseUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_purchase_order_and_returns_pending_checkout(): void
    {
        config()->set('services.payments.default_provider', 'fakepay');
        config()->set('services.payments.fake.default_checkout_status', 'pending');

        $user = User::factory()->create();

        $useCase = $this->app->make(CreateCreditPurchaseUseCase::class);

        $order = $useCase->execute($user, 10, 'idem-1');

        $this->assertInstanceOf(CreditPurchaseOrder::class, $order);
        $this->assertSame(10, (int) $order->credits);
        $this->assertSame('pending', $order->status->value);
        $this->assertNotNull($order->external_id);
    }

    public function test_it_is_idempotent_by_user_and_key(): void
    {
        config()->set('services.payments.default_provider', 'fakepay');

        $user = User::factory()->create();

        $useCase = $this->app->make(CreateCreditPurchaseUseCase::class);

        $first = $useCase->execute($user, 10, 'idem-2');
        $second = $useCase->execute($user, 10, 'idem-2');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, CreditPurchaseOrder::query()->count());
    }

    public function test_it_blocks_purchase_above_governance_limit(): void
    {
        Setting::query()->create([
            'key' => 'max_credits_per_purchase',
            'value' => '5',
        ]);

        $user = User::factory()->create();

        $useCase = $this->app->make(CreateCreditPurchaseUseCase::class);

        $this->expectException(\DomainException::class);

        $useCase->execute($user, 10, 'idem-3');
    }
}
