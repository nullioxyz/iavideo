<?php

namespace App\Domain\Credits\Services;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Credits\DTO\GenerationCreditQuote;
use App\Domain\Credits\Support\UsdValue;
use DomainException;

final class GenerationPricingService
{
    public function quote(AIModel $model, Preset $preset, ?int $requestedDurationSeconds = null): GenerationCreditQuote
    {
        if (! $preset->isActive()) {
            throw new DomainException('Selected preset is inactive.');
        }

        if (! $model->isActive()) {
            throw new DomainException('Selected model is inactive.');
        }

        if (! $model->isPubliclyVisible()) {
            throw new DomainException('Selected model is unavailable for users.');
        }

        if ((int) $preset->default_model_id !== (int) $model->getKey()) {
            throw new DomainException('Selected preset is not compatible with the selected model.');
        }

        $costPerSecondUsd = (string) ($model->cost_per_second_usd ?? '');
        if ($costPerSecondUsd === '' || UsdValue::toScaledInt($costPerSecondUsd) <= 0) {
            throw new DomainException('Selected model does not have a defined generation cost.');
        }

        $creditsPerSecond = (string) ($model->credits_per_second ?? '');
        if ($creditsPerSecond === '' || UsdValue::toScaledInt($creditsPerSecond) <= 0) {
            throw new DomainException('Selected model does not have a defined credits rate.');
        }

        $durationSeconds = $requestedDurationSeconds ?? (int) ($preset->duration_seconds ?? 0);
        if ($durationSeconds <= 0) {
            throw new DomainException('Generation duration must be greater than zero.');
        }

        $generationCostUsd = UsdValue::multiplyByInteger($costPerSecondUsd, $durationSeconds);
        $creditsRequired = $this->calculateCreditsRequired($creditsPerSecond, $durationSeconds);

        return new GenerationCreditQuote(
            modelId: (int) $model->getKey(),
            presetId: (int) $preset->getKey(),
            durationSeconds: $durationSeconds,
            modelCostPerSecondUsd: UsdValue::normalize($costPerSecondUsd),
            modelCreditsPerSecond: UsdValue::normalize($creditsPerSecond),
            generationCostUsd: $generationCostUsd,
            creditsRequired: $creditsRequired,
        );
    }

    private function calculateCreditsRequired(string $creditsPerSecond, int $durationSeconds): int
    {
        // Official billing rule: effective_duration_seconds * model.credits_per_second.
        // Wallet balances are stored as whole credits, so fractional results round up.
        return max(1, UsdValue::ceilDivide(
            UsdValue::multiplyByInteger($creditsPerSecond, $durationSeconds),
            '1.0000'
        ));
    }
}
