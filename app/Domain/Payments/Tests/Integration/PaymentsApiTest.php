<?php

namespace App\Domain\Payments\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsApiTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_authenticated_user_can_create_credit_purchase(): void
    {
        config()->set('services.payments.default_provider', 'fakepay');
        config()->set('services.payments.fake.default_checkout_status', 'pending');

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->withHeader('Idempotency-Key', 'purchase-1')
            ->postJson('/api/payments/credits/purchase', [
                'credits' => 10,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.credits', 10);
        $response->assertJsonPath('data.status', 'pending');
    }

    public function test_create_credit_purchase_is_idempotent(): void
    {
        config()->set('services.payments.default_provider', 'fakepay');
        config()->set('services.payments.fake.default_checkout_status', 'pending');

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $first = $this->withJwt($token)
            ->withHeader('Idempotency-Key', 'purchase-2')
            ->postJson('/api/payments/credits/purchase', [
                'credits' => 10,
            ]);

        $second = $this->withJwt($token)
            ->withHeader('Idempotency-Key', 'purchase-2')
            ->postJson('/api/payments/credits/purchase', [
                'credits' => 10,
            ]);

        $first->assertCreated();
        $second->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
    }

    public function test_payment_webhook_succeeds_with_valid_signature_and_refunds_credits(): void
    {
        config()->set('services.payments.webhook_secret', 'secret-1');

        $user = User::factory()->create([
            'credit_balance' => 0,
        ]);

        $order = CreditPurchaseOrder::query()->create([
            'user_id' => $user->getKey(),
            'provider' => 'fakepay',
            'external_id' => 'ext-100',
            'status' => 'pending',
            'credits' => 7,
            'amount_cents' => 700,
            'currency' => 'USD',
        ]);

        $payload = [
            'event_id' => 'evt-100',
            'external_id' => 'ext-100',
            'status' => 'succeeded',
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $json, 'secret-1');

        $response = $this->call(
            'POST',
            '/api/payments/webhook/fakepay',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAYMENT_SIGNATURE' => $signature,
            ],
            $json
        );

        $response->assertNoContent();

        $this->assertSame('succeeded', $order->fresh()->status->value);
        $this->assertSame(7, (int) $user->fresh()->credit_balance);
    }

    public function test_payment_webhook_rejects_invalid_signature(): void
    {
        config()->set('services.payments.webhook_secret', 'secret-2');

        $payload = [
            'event_id' => 'evt-101',
            'external_id' => 'ext-101',
            'status' => 'succeeded',
        ];

        $response = $this->postJson('/api/payments/webhook/fakepay', $payload, [
            'X-Payment-Signature' => 'invalid',
        ]);

        $response->assertStatus(401);
    }
}
