<?php

namespace App\Domain\AIModels\Repositories;

use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use App\Domain\AIModels\Models\Model;
use App\Domain\Languages\Support\UserLanguageContextResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AIModelsRepository implements AIModelsRepositoryInterface
{
    public function __construct(
        private readonly Model $model,
        private readonly UserLanguageContextResolver $languageContextResolver,
    ) {}

    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator
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

        return $this->model->newQuery()
            ->where('active', true)
            ->where('public_visible', true)
            ->whereNotNull('cost_per_second_usd')
            ->whereNotNull('credits_per_second')
            ->whereHas('presets', function ($query): void {
                $query->where('active', true);
            })
            ->with([
                'translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(perPage: $perPage, page: $page);
    }

    public function findById(int $id): ?Model
    {
        return $this->model->newQuery()
            ->where('id', $id)
            ->first();
    }

    public function findBySlug(string $slug): ?Model
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->first();
    }
}
