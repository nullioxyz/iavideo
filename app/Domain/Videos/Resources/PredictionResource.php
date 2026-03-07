<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Videos\Models\Prediction;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Prediction */
class PredictionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'status' => $this->status,
            'source' => $this->source,
            'attempt' => $this->attempt,
            'queued_at' => $this->queued_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'canceled_at' => $this->canceled_at?->toISOString(),
            'duration_seconds' => $this->duration_seconds,
            'cost_estimate_usd' => $this->cost_estimate_usd,
            'cost_actual_usd' => $this->cost_actual_usd,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'outputs' => PredictionOutputResource::collection($this->whenLoaded('outputs')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
