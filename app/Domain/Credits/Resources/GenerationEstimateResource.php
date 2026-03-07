<?php

namespace App\Domain\Credits\Resources;

use App\Domain\Credits\DTO\GenerationCreditQuote;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GenerationCreditQuote */
class GenerationEstimateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'model_id' => $this->modelId,
            'preset_id' => $this->presetId,
            'duration_seconds' => $this->durationSeconds,
            'credits_required' => $this->creditsRequired,
            'model_cost_per_second_usd' => $this->modelCostPerSecondUsd,
            'estimated_generation_cost_usd' => $this->generationCostUsd,
            'credit_unit_value_usd' => $this->creditUnitValueUsd,
        ];
    }
}
