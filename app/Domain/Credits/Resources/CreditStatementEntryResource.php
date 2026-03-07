<?php

namespace App\Domain\Credits\Resources;

use App\Domain\Credits\Models\CreditLedger;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CreditLedger */
class CreditStatementEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'delta' => $this->delta,
            'amount' => abs((int) $this->delta),
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'reason' => $this->reason,
            'operation_type' => $this->operation_type,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'model' => $this->model ? [
                'id' => $this->model->getKey(),
                'name' => $this->model->name,
                'provider_model_key' => $this->model->providerModelKey(),
            ] : null,
            'preset' => $this->preset ? [
                'id' => $this->preset->getKey(),
                'name' => $this->preset->name,
            ] : null,
            'duration_seconds' => $this->duration_seconds,
            'generation_cost_usd' => $this->generation_cost_usd,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
