<?php

namespace App\Domain\Payments\UseCases;

use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Payments\DTO\WebhookPayloadDTO;
use App\Domain\Payments\Enums\CreditPurchaseStatus;
use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Domain\Payments\Models\PaymentGatewayEvent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandlePaymentWebhookUseCase
{
    public function __construct(
        private readonly CreditWalletInterface $wallet,
    ) {}

    public function execute(WebhookPayloadDTO $dto): void
    {
        DB::transaction(function () use ($dto): void {
            try {
                PaymentGatewayEvent::query()->create([
                    'provider' => $dto->provider,
                    'event_id' => $dto->eventId,
                    'external_id' => $dto->externalId,
                    'payload' => $dto->raw,
                    'processed_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($this->isDuplicateKey($exception)) {
                    Log::info('payments.webhook.idempotent_hit', [
                        'provider' => $dto->provider,
                        'event_id' => $dto->eventId,
                    ]);

                    return;
                }

                throw $exception;
            }

            $order = CreditPurchaseOrder::query()
                ->with('user')
                ->where('provider', $dto->provider)
                ->where('external_id', $dto->externalId)
                ->lockForUpdate()
                ->first();

            if (! $order instanceof CreditPurchaseOrder) {
                Log::warning('payments.webhook.order_not_found', [
                    'provider' => $dto->provider,
                    'event_id' => $dto->eventId,
                    'external_id' => $dto->externalId,
                ]);

                return;
            }

            if ($order->status instanceof CreditPurchaseStatus && $order->status->isTerminal()) {
                return;
            }

            if ($dto->status === CreditPurchaseStatus::SUCCEEDED->value) {
                $order->update([
                    'status' => CreditPurchaseStatus::SUCCEEDED,
                    'paid_at' => now(),
                    'failure_code' => null,
                    'failure_message' => null,
                ]);

                if ($order->user) {
                    $this->wallet->refund($order->user, (int) $order->credits, [
                        'reason' => 'Credits purchase approved',
                        'reference_type' => 'credit_purchase',
                        'reference_id' => $order->getKey(),
                    ]);
                }

                Log::info('payments.webhook.succeeded', [
                    'order_id' => $order->getKey(),
                    'credits' => $order->credits,
                ]);

                return;
            }

            if (in_array($dto->status, [CreditPurchaseStatus::FAILED->value, CreditPurchaseStatus::CANCELED->value], true)) {
                $order->update([
                    'status' => $dto->status,
                    'failed_at' => now(),
                    'failure_code' => $dto->failureCode,
                    'failure_message' => $dto->failureMessage,
                ]);

                Log::warning('payments.webhook.failed', [
                    'order_id' => $order->getKey(),
                    'failure_code' => $dto->failureCode,
                ]);

                return;
            }

            $order->update([
                'status' => CreditPurchaseStatus::PENDING,
            ]);
        });
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        return ((string) $exception->getCode()) === '23000';
    }
}
