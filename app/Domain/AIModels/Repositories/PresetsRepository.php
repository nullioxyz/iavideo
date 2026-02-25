<?php

namespace App\Domain\AIModels\Repositories;

use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;
use App\Domain\AIModels\Models\Preset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PresetsRepository implements PresetRepositoryInterface
{
    public function __construct(
        private readonly Preset $preset
    ) {}

    public function paginate(int $modelId, int $perPage = 15, int $page = 1, array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        $query = $this->preset->newQuery()
            ->where('active', true)
            ->where('default_model_id', $modelId)
            ->with(['tags', 'media'])
            ->when(
                isset($filters['aspect_ratio']) && is_string($filters['aspect_ratio']) && $filters['aspect_ratio'] !== '',
                fn ($q) => $q->where('aspect_ratio', $filters['aspect_ratio'])
            )
            ->when(
                isset($filters['tags']) && is_array($filters['tags']) && $filters['tags'] !== [],
                fn ($q) => $q->whereHas('tags', function ($tagsQuery) use ($filters): void {
                    $tagsQuery->whereIn('slug', $filters['tags']);
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

        $tags = \App\Domain\AIModels\Models\PresetTag::query()
            ->select(['preset_tags.id', 'preset_tags.name', 'preset_tags.slug'])
            ->where('preset_tags.active', true)
            ->whereHas('presets', function ($query) use ($modelId): void {
                $query->where('default_model_id', $modelId)
                    ->where('active', true);
            })
            ->orderBy('preset_tags.name')
            ->get()
            ->map(static fn (\App\Domain\AIModels\Models\PresetTag $tag) => [
                'id' => (int) $tag->getKey(),
                'name' => (string) $tag->name,
                'slug' => (string) $tag->slug,
            ])
            ->values()
            ->all();

        return [
            'aspect_ratios' => $aspectRatios,
            'tags' => $tags,
        ];
    }
}
