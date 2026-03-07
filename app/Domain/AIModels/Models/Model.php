<?php

namespace App\Domain\AIModels\Models;

use App\Domain\Platforms\Models\Platform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class Model extends EloquentModel
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'platform_id',
        'name',
        'slug',
        'provider_model_key',
        'version',
        'cost_per_second_usd',
        'active',
        'public_visible',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'cost_per_second_usd' => 'decimal:4',
            'active' => 'boolean',
            'public_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function isPubliclyVisible(): bool
    {
        return (bool) $this->public_visible;
    }

    public function isAvailableForGeneration(): bool
    {
        return $this->isActive()
            && $this->isPubliclyVisible()
            && $this->cost_per_second_usd !== null;
    }

    public function presets(): HasMany
    {
        return $this->hasMany(Preset::class, 'default_model_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ModelLang::class, 'model_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\ModelFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\ModelFactory::new();
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

    public function providerModelKey(): string
    {
        return (string) ($this->provider_model_key ?: $this->slug);
    }

    private function resolveTranslation(?int $preferredLanguageId, ?int $defaultLanguageId): ?ModelLang
    {
        $translations = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if (! $translations instanceof Collection) {
            return null;
        }

        if ($preferredLanguageId !== null) {
            $preferred = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($preferred instanceof ModelLang) {
                return $preferred;
            }
        }

        if ($defaultLanguageId !== null) {
            $fallback = $translations->firstWhere('language_id', $defaultLanguageId);
            if ($fallback instanceof ModelLang) {
                return $fallback;
            }
        }

        $first = $translations->first();

        return $first instanceof ModelLang ? $first : null;
    }
}
