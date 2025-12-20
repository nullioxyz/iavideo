<?php

namespace App\Domain\AIModels\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PresetsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'default_model_id' => $this->default_model_id,
            'name' => $this->name,
            'prompt' => $this->prompt,
            'negative_prompt' => $this->negative_prompt,
            'duration_seconds' => $this->duration_seconds,
            'preview_video_url' => $this->preview_video_url,
            'aspect_ratio' => $this->aspect_ratio,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
