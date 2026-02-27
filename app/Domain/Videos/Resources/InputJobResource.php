<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Videos\Models\Input;
use App\Support\FrontendAssetUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Input */
class InputJobResource extends JsonResource
{
    public function toArray($request): array
    {
        $startImageUrl = $this->resolveStartImageUrl();

        return [
            'id' => $this->id,
            'preset_id' => $this->preset_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'title' => $this->title,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
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
