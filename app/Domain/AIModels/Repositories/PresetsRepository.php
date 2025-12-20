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

    public function paginate(int $modelId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        return $this->preset->newQuery()
            ->where('active', true)
            ->where('default_model_id', $modelId)
            ->orderBy('id', 'desc')
            ->paginate(perPage: $perPage, page: $page);
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
}
