<?php

namespace App\Domain\AIModels\Resources;

use App\Domain\AIModels\Models\PresetTag;
use App\Domain\Languages\Support\UserLanguageContextResolver;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PresetTag */
class PresetTagResource extends JsonResource
{
    public function toArray($request): array
    {
        $context = app(UserLanguageContextResolver::class)->fromRequest($request);
        $preferredLanguageId = $context['preferred_language_id'] ?? null;
        $defaultLanguageId = $context['default_language_id'] ?? null;

        return [
            'id' => $this->id,
            'name' => $this->localizedName($preferredLanguageId, $defaultLanguageId),
            'slug' => $this->localizedSlug($preferredLanguageId, $defaultLanguageId),
        ];
    }
}
