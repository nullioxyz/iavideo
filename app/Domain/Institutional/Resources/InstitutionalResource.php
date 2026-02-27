<?php

namespace App\Domain\Institutional\Resources;

use App\Domain\Institutional\Models\Institutional;
use App\Support\FrontendAssetUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Institutional */
class InstitutionalResource extends JsonResource
{
    public function toArray($request): array
    {
        $preferredLanguageId = $request->attributes->get('preferred_language_id');
        $defaultLanguageId = $request->attributes->get('default_language_id');
        $localized = $this->localized(
            is_numeric($preferredLanguageId) ? (int) $preferredLanguageId : null,
            is_numeric($defaultLanguageId) ? (int) $defaultLanguageId : null,
        );

        return [
            'id' => $this->id,
            'title' => $localized['title'],
            'subtitle' => $localized['subtitle'],
            'short_description' => $localized['short_description'],
            'description' => $localized['description'],
            'slug' => $localized['slug'],
            'active' => (bool) $this->active,
            'images' => $this->getMedia('images')->map(fn ($media) => [
                'id' => $media->id,
                'url' => FrontendAssetUrl::resolve((string) $media->getUrl()),
                'name' => $media->name,
            ])->values()->all(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
