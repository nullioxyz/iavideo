<?php

namespace App\Domain\AIModels\Contracts\Repositories;

use App\Domain\AIModels\Models\Preset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PresetRepositoryInterface
{
    public function paginate(int $modelId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?Preset;

    public function findByModel(int $modelId): LengthAwarePaginator;
}
