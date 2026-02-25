<?php

namespace App\Domain\Credits\Resources;

use App\Domain\Credits\Models\CreditLegder;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CreditLegder */
class CreditStatementEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'delta' => $this->delta,
            'balance_after' => $this->balance_after,
            'reason' => $this->reason,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
