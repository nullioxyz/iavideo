<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Videos\Models\Input;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Input */
class InputResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'preset_id' => $this->preset_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'title' => $this->title,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'duration_seconds' => $this->duration_seconds,
            'estimated_cost_usd' => $this->estimated_cost_usd,
            'credits_charged' => (int) ($this->credits_charged ?? 0),
            'billing_status' => $this->billing_status,
        ];
    }
}
