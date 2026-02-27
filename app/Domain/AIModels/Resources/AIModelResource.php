<?php

namespace App\Domain\AIModels\Resources;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\Languages\Support\UserLanguageContextResolver;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AIModel */
class AIModelResource extends JsonResource
{
    public function toArray($request): array
    {
        $context = app(UserLanguageContextResolver::class)->fromRequest($request);
        $preferredLanguageId = $context['preferred_language_id'] ?? null;
        $defaultLanguageId = $context['default_language_id'] ?? null;

        return [
            'id' => $this->id,
            'platform_id' => $this->platform_id,
            'name' => $this->localizedName($preferredLanguageId, $defaultLanguageId),
            'slug' => $this->localizedSlug($preferredLanguageId, $defaultLanguageId),
            'version' => $this->version,
            'active' => (bool) $this->active,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
