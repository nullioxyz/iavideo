<?php

namespace App\Domain\Credits\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Contracts\CreditWalletInterface;
use App\Domain\Credits\DTO\GenerationCreditQuote;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Models\Input;

final class GenerationBillingService
{
    public function __construct(
        private readonly CreditWalletInterface $wallet,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function chargeInput(User $user, Input $input, GenerationCreditQuote $quote, array $metadata = []): void
    {
        if ($input->credit_debited && (int) $input->credits_charged === $quote->creditsRequired) {
            return;
        }

        // We debit before the external provider call to keep the balance consistent.
        // Any unsuccessful generation is compensated by a separate idempotent refund entry.
        $idempotencyKey = $this->chargeIdempotencyKey((int) $input->getKey());

        $this->wallet->charge($user, $quote->creditsRequired, [
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'model_id' => $quote->modelId,
            'preset_id' => $quote->presetId,
            'duration_seconds' => $quote->durationSeconds,
            'generation_cost_usd' => $quote->generationCostUsd,
            'idempotency_key' => $idempotencyKey,
            'metadata' => array_merge([
                'cost_per_second_usd' => $quote->modelCostPerSecondUsd,
                'credits_per_second' => $quote->modelCreditsPerSecond,
            ], $metadata),
        ]);

        $chargeLedgerId = CreditLedger::query()
            ->where('idempotency_key', $idempotencyKey)
            ->value('id');

        $input->update([
            'credit_debited' => true,
            'credit_ledger_id' => $chargeLedgerId ? (int) $chargeLedgerId : null,
            'credits_charged' => $quote->creditsRequired,
            'billing_status' => 'charged',
            'estimated_cost_usd' => $quote->generationCostUsd,
            'model_cost_per_second_usd' => $quote->modelCostPerSecondUsd,
            'model_credits_per_second' => $quote->modelCreditsPerSecond,
            'duration_seconds' => $quote->durationSeconds,
            'model_id' => $quote->modelId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function refundInput(Input $input, string $reason, array $metadata = []): void
    {
        if (! $input->credit_debited || (int) $input->credits_charged <= 0) {
            return;
        }

        $user = $input->user;
        if (! $user instanceof User) {
            return;
        }

        // Refunds use a deterministic idempotency key so provider/webhook retries cannot double-credit the wallet.
        $idempotencyKey = $this->refundIdempotencyKey((int) $input->getKey());

        $this->wallet->refund($user, (int) $input->credits_charged, [
            'reason' => $reason,
            'operation_type' => 'generation_refund',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'model_id' => $input->model_id,
            'preset_id' => $input->preset_id,
            'duration_seconds' => $input->duration_seconds,
            'generation_cost_usd' => $input->estimated_cost_usd,
            'idempotency_key' => $idempotencyKey,
            'metadata' => array_merge([
                'cost_per_second_usd' => $input->model_cost_per_second_usd,
                'credits_per_second' => $input->model_credits_per_second,
            ], $metadata),
        ]);

        $input->update([
            'credit_debited' => false,
            'billing_status' => 'refunded',
        ]);
    }

    private function chargeIdempotencyKey(int $inputId): string
    {
        return "input:{$inputId}:generation:charge";
    }

    private function refundIdempotencyKey(int $inputId): string
    {
        return "input:{$inputId}:generation:refund";
    }
}
