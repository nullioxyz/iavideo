<?php

namespace App\Domain\Payments\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Payments\Contracts\PaymentGatewayRegistryInterface;
use App\Domain\Payments\Enums\CreditPurchaseStatus;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Domain\Settings\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateCreditPurchaseUseCase
{
    public function __construct(
        private readonly PaymentGatewayRegistryInterface $registry,
    ) {}

    public function execute(User $user, int $credits, ?string $idempotencyKey = null): CreditPurchaseOrder
    {
        $this->assertGovernance($user, $credits);

        return DB::transaction(function () use ($user, $credits, $idempotencyKey): CreditPurchaseOrder {
            if ($idempotencyKey) {
                $existing = CreditPurchaseOrder::query()
                    ->where('user_id', $user->getKey())
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing instanceof CreditPurchaseOrder) {
                    Log::info('payments.purchase.idempotent_hit', [
                        'user_id' => $user->getKey(),
                        'order_id' => $existing->getKey(),
                    ]);

                    return $existing;
                }
            }

            $provider = (string) config('services.payments.default_provider', 'fakepay');
            $currency = (string) config('services.payments.currency', 'USD');
            $unitPriceCents = $this->settingInt('credit_unit_price_cents', 100);
            $amountCents = $credits * $unitPriceCents;

            $order = CreditPurchaseOrder::query()->create([
                'user_id' => $user->getKey(),
                'provider' => $provider,
                'idempotency_key' => $idempotencyKey,
                'status' => CreditPurchaseStatus::CREATED,
                'credits' => $credits,
                'amount_cents' => $amountCents,
                'currency' => $currency,
            ]);

            $gateway = $this->registry->get($provider);
            $checkout = $gateway->createCheckout([
                'order_id' => $order->getKey(),
                'user_id' => $user->getKey(),
                'credits' => $credits,
                'amount_cents' => $amountCents,
                'currency' => $currency,
            ]);

            $order->update([
                'external_id' => $checkout->externalId,
                'checkout_url' => $checkout->checkoutUrl,
                'status' => in_array($checkout->status, ['created', 'pending', 'succeeded', 'failed', 'canceled'], true)
                    ? $checkout->status
                    : CreditPurchaseStatus::PENDING->value,
                'metadata' => $checkout->raw,
            ]);

            Log::info('payments.purchase.created', [
                'order_id' => $order->getKey(),
                'user_id' => $user->getKey(),
                'provider' => $provider,
                'credits' => $credits,
                'amount_cents' => $amountCents,
            ]);

            return $order->refresh();
        });
    }

    private function assertGovernance(User $user, int $credits): void
    {
        $maxPerPurchase = $this->settingInt('max_credits_per_purchase', 200);
        if ($credits > $maxPerPurchase) {
            throw new \DomainException('Credits amount exceeds max per purchase limit.');
        }

        $maxDailyCredits = $this->settingInt('max_daily_credits_purchase', 1000);
        $todayPurchased = (int) CreditPurchaseOrder::query()
            ->where('user_id', $user->getKey())
            ->where('status', CreditPurchaseStatus::SUCCEEDED->value)
            ->whereDate('paid_at', now()->toDateString())
            ->sum('credits');

        if (($todayPurchased + $credits) > $maxDailyCredits) {
            throw new \DomainException('Daily credits purchase limit exceeded.');
        }
    }

    private function settingInt(string $key, int $default): int
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return is_numeric($value) ? (int) $value : $default;
    }
}
