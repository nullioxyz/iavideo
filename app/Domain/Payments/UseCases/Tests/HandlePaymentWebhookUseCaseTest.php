<?php

namespace App\Domain\Payments\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Payments\DTO\WebhookPayloadDTO;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Domain\Payments\Models\PaymentGatewayEvent;
use App\Domain\Payments\UseCases\HandlePaymentWebhookUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandlePaymentWebhookUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_order_succeeded_and_refunds_credits_once(): void
    {
        $user = User::factory()->create([
            'credit_balance' => 0,
        ]);

        $order = CreditPurchaseOrder::query()->create([
            'user_id' => $user->getKey(),
            'provider' => 'fakepay',
            'external_id' => 'ext-1',
            'status' => 'pending',
            'credits' => 10,
            'amount_cents' => 1000,
            'currency' => 'USD',
        ]);

        $useCase = $this->app->make(HandlePaymentWebhookUseCase::class);

        $payload = WebhookPayloadDTO::fromArray('fakepay', [
            'event_id' => 'evt-1',
            'external_id' => 'ext-1',
            'status' => 'succeeded',
        ]);

        $useCase->execute($payload);
        $useCase->execute($payload);

        $this->assertSame('succeeded', $order->fresh()->status->value);
        $this->assertSame(10, (int) $user->fresh()->credit_balance);
        $this->assertSame(1, PaymentGatewayEvent::query()->count());
    }

    public function test_it_marks_order_failed_without_refund(): void
    {
        $user = User::factory()->create([
            'credit_balance' => 2,
        ]);

        $order = CreditPurchaseOrder::query()->create([
            'user_id' => $user->getKey(),
            'provider' => 'fakepay',
            'external_id' => 'ext-2',
            'status' => 'pending',
            'credits' => 10,
            'amount_cents' => 1000,
            'currency' => 'USD',
        ]);

        $useCase = $this->app->make(HandlePaymentWebhookUseCase::class);

        $payload = WebhookPayloadDTO::fromArray('fakepay', [
            'event_id' => 'evt-2',
            'external_id' => 'ext-2',
            'status' => 'failed',
            'failure_code' => 'card_declined',
            'failure_message' => 'Card declined',
        ]);

        $useCase->execute($payload);

        $this->assertSame('failed', $order->fresh()->status->value);
        $this->assertSame('card_declined', $order->fresh()->failure_code);
        $this->assertSame(2, (int) $user->fresh()->credit_balance);
    }
}
