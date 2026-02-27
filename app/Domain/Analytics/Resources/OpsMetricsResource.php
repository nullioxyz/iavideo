<?php

namespace App\Domain\Analytics\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OpsMetricsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'queue_pending_jobs' => (int) ($this['queue_pending_jobs'] ?? 0),
            'queue_failed_jobs_last_24h' => (int) ($this['queue_failed_jobs_last_24h'] ?? 0),
            'prediction_failures_last_24h' => (int) ($this['prediction_failures_last_24h'] ?? 0),
            'ai_latency_avg_ms_last_24h' => (float) ($this['ai_latency_avg_ms_last_24h'] ?? 0),
            'ai_latency_p95_ms_last_24h' => (float) ($this['ai_latency_p95_ms_last_24h'] ?? 0),
        ];
    }
}
