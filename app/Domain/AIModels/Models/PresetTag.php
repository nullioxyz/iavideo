<?php

namespace App\Domain\AIModels\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PresetTag extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function presets(): BelongsToMany
    {
        return $this->belongsToMany(
            Preset::class,
            'preset_tag_preset',
            'preset_tag_id',
            'preset_id'
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PresetTagLang::class, 'preset_tag_id');
    }

    protected static function booted(): void
    {
        static::saving(function (PresetTag $tag): void {
            if (! is_string($tag->slug) || $tag->slug === '') {
                $tag->slug = Str::slug((string) $tag->name);
            }
        });
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\PresetTagFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\PresetTagFactory::new();
    }

    public function localizedName(?int $preferredLanguageId = null, ?int $defaultLanguageId = null): string
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return (string) ($translation?->name ?: $this->name);
    }

    public function localizedSlug(?int $preferredLanguageId = null, ?int $defaultLanguageId = null): string
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return (string) ($translation?->slug ?: $this->slug);
    }

    private function resolveTranslation(?int $preferredLanguageId, ?int $defaultLanguageId): ?PresetTagLang
    {
        $translations = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if (! $translations instanceof Collection) {
            return null;
        }

        if ($preferredLanguageId !== null) {
            $preferred = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($preferred instanceof PresetTagLang) {
                return $preferred;
            }
        }

        if ($defaultLanguageId !== null) {
            $fallback = $translations->firstWhere('language_id', $defaultLanguageId);
            if ($fallback instanceof PresetTagLang) {
                return $fallback;
            }
        }

        $first = $translations->first();

        return $first instanceof PresetTagLang ? $first : null;
    }
}
