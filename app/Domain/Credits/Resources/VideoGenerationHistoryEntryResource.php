<?php

namespace App\Domain\Credits\Resources;

use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\PredictionOutput;
use App\Support\FrontendAssetUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Input */
class VideoGenerationHistoryEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        $outputVideoUrl = $this->resolveOutputVideoUrl();

        return [
            'input_id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'model' => $this->model ? [
                'id' => $this->model->getKey(),
                'name' => $this->model->name,
                'provider_model_key' => $this->model->providerModelKey(),
            ] : null,
            'preset' => [
                'id' => $this->preset?->id,
                'name' => $this->preset?->name,
            ],
            'duration_seconds' => $this->duration_seconds,
            'estimated_cost_usd' => $this->estimated_cost_usd,
            'model_cost_per_second_usd' => $this->model_cost_per_second_usd,
            'model_credits_per_second' => $this->model_credits_per_second,
            'prediction' => [
                'id' => $this->prediction?->id,
                'status' => $this->prediction?->status,
                'error_code' => $this->prediction?->error_code,
                'error_message' => $this->prediction?->error_message,
                'output_video_url' => $outputVideoUrl,
            ],
            'credits_debited' => (int) ($this->credits_debited ?? 0),
            'credits_refunded' => (int) ($this->credits_refunded ?? 0),
            'credits_used' => (int) ($this->credits_used ?? 0),
            'credits_charged' => (int) ($this->credits_charged ?? 0),
            'billing_status' => $this->billing_status,
            'is_failed' => (bool) ($this->is_failed ?? false),
            'is_canceled' => (bool) ($this->is_canceled ?? false),
            'is_refunded' => (bool) ($this->is_refunded ?? false),
            'ledger_entries_count' => (int) ($this->ledger_entries_count ?? 0),
            'failure' => [
                'code' => $this->failure_code,
                'message' => $this->failure_message,
                'reason' => $this->failure_reason,
            ],
            'cancellation' => [
                'reason' => $this->cancel_reason,
            ],
            'ledger_entries' => $this->ledger_entries ?? [],
            'credit_events' => $this->credit_events ?? [],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function resolveOutputVideoUrl(): ?string
    {
        if (! $this->prediction) {
            return null;
        }

        /** @var PredictionOutput|null $videoOutput */
        $videoOutput = $this->prediction->outputs
            ->first(fn (PredictionOutput $output) => $output->kind === 'video');

        if (! $videoOutput instanceof PredictionOutput) {
            return null;
        }

        $media = $videoOutput->getMediaFile();
        if ($media) {
            return FrontendAssetUrl::video($media);
        }

        $path = (string) $videoOutput->path;
        if ($path !== '' && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return null;
    }
}
