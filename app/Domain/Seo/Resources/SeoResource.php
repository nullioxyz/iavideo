<?php

namespace App\Domain\Seo\Resources;

use App\Domain\Seo\Models\Seo;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Seo */
class SeoResource extends JsonResource
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
            'slug' => $localized['slug'],
            'meta_title' => $localized['meta_title'],
            'meta_description' => $localized['meta_description'],
            'meta_keywords' => $localized['meta_keywords'],
            'canonical_url' => $localized['canonical_url'],
            'og_title' => $localized['og_title'],
            'og_description' => $localized['og_description'],
            'twitter_title' => $localized['twitter_title'],
            'twitter_description' => $localized['twitter_description'],
            'images' => $this->getMedia('images')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->name,
            ])->values()->all(),
            'active' => (bool) $this->active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

