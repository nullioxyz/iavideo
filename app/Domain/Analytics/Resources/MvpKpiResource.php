<?php

namespace App\Domain\Analytics\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MvpKpiResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'activation_rate_percent' => (float) ($this['activation_rate_percent'] ?? 0),
            'avg_time_to_ready_seconds' => (float) ($this['avg_time_to_ready_seconds'] ?? 0),
            'prediction_failure_rate_percent' => (float) ($this['prediction_failure_rate_percent'] ?? 0),
            'retry_success_rate_percent' => (float) ($this['retry_success_rate_percent'] ?? 0),
            'credits_per_completed_video' => (float) ($this['credits_per_completed_video'] ?? 0),
            'refund_rate_percent' => (float) ($this['refund_rate_percent'] ?? 0),
            'contacts_volume_last_30d' => (int) ($this['contacts_volume_last_30d'] ?? 0),
        ];
    }
}
