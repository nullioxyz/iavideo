<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Credits\DTO\GenerationCreditQuote;
use App\Domain\Credits\Services\GenerationPricingService;

final class EstimateGenerationCreditsUseCase
{
    public function __construct(
        private readonly GenerationPricingService $pricingService,
    ) {}

    public function execute(int $modelId, int $presetId, ?int $durationSeconds = null): GenerationCreditQuote
    {
        $model = AIModel::query()->findOrFail($modelId);
        $preset = Preset::query()->findOrFail($presetId);

        return $this->pricingService->quote($model, $preset, $durationSeconds);
    }
}
