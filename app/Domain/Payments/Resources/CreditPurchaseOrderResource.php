<?php

namespace App\Domain\Payments\Resources;

use App\Domain\Payments\Models\CreditPurchaseOrder;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CreditPurchaseOrder */
class CreditPurchaseOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'status' => $this->status?->value ?? $this->status,
            'credits' => $this->credits,
            'amount_cents' => $this->amount_cents,
            'currency' => $this->currency,
            'checkout_url' => $this->checkout_url,
            'failure_code' => $this->failure_code,
            'failure_message' => $this->failure_message,
            'paid_at' => $this->paid_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
