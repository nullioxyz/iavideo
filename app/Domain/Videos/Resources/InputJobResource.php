<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Languages\Support\UserLanguageContextResolver;
use App\Domain\Videos\Models\Input;
use App\Support\FrontendAssetUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Input */
class InputJobResource extends JsonResource
{
    public function toArray($request): array
    {
        $startImageUrl = $this->resolveStartImageUrl();
        $context = app(UserLanguageContextResolver::class)->fromRequest($request);
        $preferredLanguageId = $context['preferred_language_id'] ?? null;
        $defaultLanguageId = $context['default_language_id'] ?? null;

        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'model' => $this->model ? [
                'id' => $this->model->getKey(),
                'name' => $this->model->localizedName($preferredLanguageId, $defaultLanguageId),
                'provider_model_key' => $this->model->providerModelKey(),
            ] : null,
            'preset_id' => $this->preset_id,
            'preset' => $this->preset ? [
                'id' => $this->preset->getKey(),
                'name' => $this->preset->localizedName($preferredLanguageId, $defaultLanguageId),
            ] : null,
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
            'credit_debited' => (bool) $this->credit_debited,
            'start_image_url' => $startImageUrl,
            'prediction' => $this->prediction
                ? new PredictionResource($this->prediction)
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function resolveStartImageUrl(): ?string
    {
        return FrontendAssetUrl::image($this->getFirstMedia('start_image'));
    }
}
