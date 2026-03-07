<?php

namespace App\Domain\Credits\Services;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Credits\Contracts\CostToCreditsConverterInterface;
use App\Domain\Credits\DTO\GenerationCreditQuote;
use App\Domain\Credits\Support\UsdValue;
use DomainException;

final class GenerationPricingService
{
    public function __construct(
        private readonly CostToCreditsConverterInterface $converter,
    ) {}

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

        $durationSeconds = $requestedDurationSeconds ?? (int) ($preset->duration_seconds ?? 0);
        if ($durationSeconds <= 0) {
            throw new DomainException('Generation duration must be greater than zero.');
        }

        $generationCostUsd = UsdValue::multiplyByInteger($costPerSecondUsd, $durationSeconds);
        $creditsRequired = $this->converter->convertUsdCostToCredits($generationCostUsd);

        return new GenerationCreditQuote(
            modelId: (int) $model->getKey(),
            presetId: (int) $preset->getKey(),
            durationSeconds: $durationSeconds,
            modelCostPerSecondUsd: UsdValue::normalize($costPerSecondUsd),
            generationCostUsd: $generationCostUsd,
            creditUnitValueUsd: $this->converter->creditUnitValueUsd(),
            creditsRequired: $creditsRequired,
        );
    }
}
