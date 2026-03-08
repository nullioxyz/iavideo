<?php

namespace App\Domain\AIModels\Models;

use App\Domain\AIModels\Jobs\AttachPresetMediaJob;
use App\Domain\Videos\Models\Input;
use App\Infra\Storage\UploadStorageResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Preset extends EloquentModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'prompt',
        'negative_prompt',
        'aspect_ratio',
        'duration_seconds',
        'default_model_id',
        'cost_estimate_usd',
        'preview_video_url',
        'preview_image_upload_path',
        'preview_video_upload_path',
        'active',
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
        ];
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class, 'default_model_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            PresetTag::class,
            'preset_tag_preset',
            'preset_id',
            'preset_tag_id'
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PresetLang::class, 'preset_id');
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(Input::class, 'preset_id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('preview_image')
            ->useDisk(UploadStorageResolver::mediaDisk())
            ->singleFile();

        $this
            ->addMediaCollection('preview_video')
            ->useDisk(UploadStorageResolver::mediaDisk())
            ->singleFile();
    }

    public function previewImageUrl(): ?string
    {
        $url = (string) $this->getFirstMediaUrl('preview_image');

        return $url !== '' ? $url : null;
    }

    public function previewVideoUrl(): ?string
    {
        $mediaUrl = (string) $this->getFirstMediaUrl('preview_video');
        if ($mediaUrl !== '') {
            return $mediaUrl;
        }

        $fallback = (string) ($this->preview_video_url ?? '');

        return $fallback !== '' ? $fallback : null;
    }

    protected static function booted(): void
    {
        static::saved(function (Preset $preset): void {
            $imagePath = $preset->getAttribute('preview_image_upload_path');
            $shouldDispatchImage = (
                $preset->wasChanged('preview_image_upload_path') || $preset->wasRecentlyCreated
            ) && is_string($imagePath) && $imagePath !== '';

            if ($shouldDispatchImage) {
                AttachPresetMediaJob::dispatch(
                    (int) $preset->getKey(),
                    'image',
                    $imagePath,
                    UploadStorageResolver::tempDisk(),
                )->onQueue('media');
            }

            $videoPath = $preset->getAttribute('preview_video_upload_path');
            $shouldDispatchVideo = (
                $preset->wasChanged('preview_video_upload_path') || $preset->wasRecentlyCreated
            ) && is_string($videoPath) && $videoPath !== '';

            if ($shouldDispatchVideo) {
                AttachPresetMediaJob::dispatch(
                    (int) $preset->getKey(),
                    'video',
                    $videoPath,
                    UploadStorageResolver::tempDisk(),
                )->onQueue('media');
            }
        });
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\PresetFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\PresetFactory::new();
    }

    public function localizedName(?int $preferredLanguageId = null, ?int $defaultLanguageId = null): string
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return (string) ($translation?->name ?: $this->name);
    }

    public function localizedPrompt(?int $preferredLanguageId = null, ?int $defaultLanguageId = null): string
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);

        return (string) ($translation?->prompt ?: $this->prompt);
    }

    public function localizedNegativePrompt(?int $preferredLanguageId = null, ?int $defaultLanguageId = null): ?string
    {
        $translation = $this->resolveTranslation($preferredLanguageId, $defaultLanguageId);
        $value = $translation?->negative_prompt ?: $this->negative_prompt;

        return $value !== null ? (string) $value : null;
    }

    private function resolveTranslation(?int $preferredLanguageId, ?int $defaultLanguageId): ?PresetLang
    {
        $translations = $this->relationLoaded('translations')
            ? $this->getRelation('translations')
            : $this->translations()->get();

        if (! $translations instanceof Collection) {
            return null;
        }

        if ($preferredLanguageId !== null) {
            $preferred = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($preferred instanceof PresetLang) {
                return $preferred;
            }
        }

        if ($defaultLanguageId !== null) {
            $fallback = $translations->firstWhere('language_id', $defaultLanguageId);
            if ($fallback instanceof PresetLang) {
                return $fallback;
            }
        }

        $first = $translations->first();

        return $first instanceof PresetLang ? $first : null;
    }
}
