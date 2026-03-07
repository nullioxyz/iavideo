<?php

namespace App\Domain\Credits\DTO;

final class GenerationCreditQuote
{
    public function __construct(
        public readonly int $modelId,
        public readonly int $presetId,
        public readonly int $durationSeconds,
        public readonly string $modelCostPerSecondUsd,
        public readonly string $generationCostUsd,
        public readonly string $creditUnitValueUsd,
        public readonly int $creditsRequired,
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'model_id' => $this->modelId,
            'preset_id' => $this->presetId,
            'duration_seconds' => $this->durationSeconds,
            'model_cost_per_second_usd' => $this->modelCostPerSecondUsd,
            'estimated_generation_cost_usd' => $this->generationCostUsd,
            'credit_unit_value_usd' => $this->creditUnitValueUsd,
            'credits_required' => $this->creditsRequired,
        ];
    }
}
