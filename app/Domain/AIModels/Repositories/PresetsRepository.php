<?php

namespace App\Domain\AIModels\Repositories;

use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIModels\Models\PresetTag;
use App\Domain\Languages\Support\UserLanguageContextResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PresetsRepository implements PresetRepositoryInterface
{
    public function __construct(
        private readonly Preset $preset,
        private readonly UserLanguageContextResolver $languageContextResolver,
    ) {}

    public function paginate(int $modelId, int $perPage = 15, int $page = 1, array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);
        $context = $this->languageContextResolver->resolve(
            auth('api')->user()
        );

        $languageIds = array_values(array_filter([
            $context['preferred_language_id'] ?? null,
            $context['default_language_id'] ?? null,
        ]));

        $query = $this->preset->newQuery()
            ->where('active', true)
            ->where('default_model_id', $modelId)
            ->with([
                'translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
                'tags.translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
                'tags',
                'media',
            ])
            ->when(
                isset($filters['aspect_ratio']) && is_string($filters['aspect_ratio']) && $filters['aspect_ratio'] !== '',
                fn ($q) => $q->where('aspect_ratio', $filters['aspect_ratio'])
            )
            ->when(
                isset($filters['tags']) && is_array($filters['tags']) && $filters['tags'] !== [],
                fn ($q) => $q->whereHas('tags', function ($tagsQuery) use ($filters, $languageIds): void {
                    $tagsQuery->where(function ($innerQuery) use ($filters, $languageIds): void {
                        $innerQuery
                            ->whereIn('preset_tags.slug', $filters['tags'])
                            ->orWhereHas('translations', function ($translationQuery) use ($filters, $languageIds): void {
                                if ($languageIds !== []) {
                                    $translationQuery->whereIn('language_id', $languageIds);
                                }

                                $translationQuery->whereIn('slug', $filters['tags']);
                            });
                    });
                })
            )
            ->orderBy('id', 'desc')
            ->distinct();

        return $query->paginate(perPage: $perPage, page: $page);
    }

    public function findById(int $id): ?Preset
    {
        return $this->preset->newQuery()
            ->where('id', $id)
            ->first();
    }

    public function findByModel(int $modelId): LengthAwarePaginator
    {
        return $this->preset->newQuery()
            ->where('default_model_id', $modelId)
            ->paginate();
    }

    public function listFilterOptions(int $modelId): array
    {
        $context = $this->languageContextResolver->resolve(
            auth('api')->user()
        );
        $preferredLanguageId = $context['preferred_language_id'] ?? null;
        $defaultLanguageId = $context['default_language_id'] ?? null;
        $languageIds = array_values(array_filter([
            $preferredLanguageId,
            $defaultLanguageId,
        ]));

        $baseQuery = $this->preset->newQuery()
            ->where('active', true)
            ->where('default_model_id', $modelId);

        $aspectRatios = (clone $baseQuery)
            ->select('aspect_ratio')
            ->whereIn('aspect_ratio', ['16:9', '9:16', '1:1'])
            ->distinct()
            ->orderBy('aspect_ratio')
            ->pluck('aspect_ratio')
            ->filter(static fn ($value) => is_string($value) && $value !== '')
            ->values()
            ->all();

        $tags = PresetTag::query()
            ->select(['preset_tags.id', 'preset_tags.name', 'preset_tags.slug'])
            ->where('preset_tags.active', true)
            ->with([
                'translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
            ])
            ->whereHas('presets', function ($query) use ($modelId): void {
                $query->where('default_model_id', $modelId)
                    ->where('active', true);
            })
            ->orderBy('preset_tags.name')
            ->get()
            ->map(static fn (PresetTag $tag) => [
                'id' => (int) $tag->getKey(),
                'name' => (string) $tag->localizedName($preferredLanguageId, $defaultLanguageId),
                'slug' => (string) $tag->localizedSlug($preferredLanguageId, $defaultLanguageId),
            ])
            ->values()
            ->all();

        return [
            'aspect_ratios' => $aspectRatios,
            'tags' => $tags,
        ];
    }
}
