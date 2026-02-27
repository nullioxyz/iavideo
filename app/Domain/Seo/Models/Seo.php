<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Seo extends EloquentModel implements HasMedia
{
    use InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SeoLang::class, 'seo_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->useDisk('public');
    }

    /**
     * @return array<string, mixed>
     */
    public function localized(?int $preferredLanguageId, ?int $defaultLanguageId): array
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return [
            'slug' => (string) ($translation?->slug ?: $this->slug),
            'meta_title' => $translation?->meta_title ?: $this->meta_title,
            'meta_description' => $translation?->meta_description ?: $this->meta_description,
            'meta_keywords' => $translation?->meta_keywords ?: $this->meta_keywords,
            'canonical_url' => $this->canonical_url,
            'og_title' => $translation?->og_title ?: $this->og_title,
            'og_description' => $translation?->og_description ?: $this->og_description,
            'twitter_title' => $translation?->twitter_title ?: $this->twitter_title,
            'twitter_description' => $translation?->twitter_description ?: $this->twitter_description,
        ];
    }

    private function resolveTranslation(?int $preferredLanguageId, ?int $defaultLanguageId): ?SeoLang
    {
        $translations = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if (! $translations instanceof Collection) {
            return null;
        }

        if ($preferredLanguageId !== null) {
            $preferred = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($preferred instanceof SeoLang) {
                return $preferred;
            }
        }

        if ($defaultLanguageId !== null) {
            $fallback = $translations->firstWhere('language_id', $defaultLanguageId);
            if ($fallback instanceof SeoLang) {
                return $fallback;
            }
        }

        $first = $translations->first();

        return $first instanceof SeoLang ? $first : null;
    }
}

