<?php

namespace App\Domain\Institutional\Models;

use App\Infra\Storage\UploadStorageResolver;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Institutional extends EloquentModel implements HasMedia
{
    use InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'short_description',
        'description',
        'active',
        'slug',
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
        return $this->hasMany(InstitutionalLang::class, 'institutional_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->useDisk(UploadStorageResolver::mediaDisk());
    }

    /**
     * @return array{title:string,subtitle:?string,short_description:?string,description:?string,slug:string}
     */
    public function localized(?int $preferredLanguageId, ?int $defaultLanguageId): array
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return [
            'title' => (string) ($translation?->title ?: $this->title),
            'subtitle' => $translation?->subtitle ?: $this->subtitle,
            'short_description' => $translation?->short_description ?: $this->short_description,
            'description' => $translation?->description ?: $this->description,
            'slug' => (string) ($translation?->slug ?: $this->slug),
        ];
    }

    private function resolveTranslation(?int $preferredLanguageId, ?int $defaultLanguageId): ?InstitutionalLang
    {
        $translations = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if (! $translations instanceof Collection) {
            return null;
        }

        if ($preferredLanguageId !== null) {
            $preferred = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($preferred instanceof InstitutionalLang) {
                return $preferred;
            }
        }

        if ($defaultLanguageId !== null) {
            $fallback = $translations->firstWhere('language_id', $defaultLanguageId);
            if ($fallback instanceof InstitutionalLang) {
                return $fallback;
            }
        }

        $first = $translations->first();

        return $first instanceof InstitutionalLang ? $first : null;
    }
}
