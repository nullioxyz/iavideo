<?php

namespace App\Domain\AIModels\Resources;

use App\Domain\AIModels\Models\Preset;
use App\Domain\Languages\Support\UserLanguageContextResolver;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Preset */
class PresetsResource extends JsonResource
{
    public function toArray($request): array
    {
        $context = app(UserLanguageContextResolver::class)->fromRequest($request);
        $preferredLanguageId = $context['preferred_language_id'] ?? null;
        $defaultLanguageId = $context['default_language_id'] ?? null;

        return [
            'id' => $this->id,
            'default_model_id' => $this->default_model_id,
            'name' => $this->localizedName($preferredLanguageId, $defaultLanguageId),
            'prompt' => $this->localizedPrompt($preferredLanguageId, $defaultLanguageId),
            'negative_prompt' => $this->localizedNegativePrompt($preferredLanguageId, $defaultLanguageId),
            'duration_seconds' => $this->duration_seconds,
            'preview_image_url' => $this->previewImageUrl(),
            'preview_video_url' => $this->previewVideoUrl(),
            'aspect_ratio' => $this->aspect_ratio,
            'tags' => PresetTagResource::collection($this->whenLoaded('tags')),
            'language_slug' => $context['preferred_language_slug'] ?? $context['default_language_slug'],
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
