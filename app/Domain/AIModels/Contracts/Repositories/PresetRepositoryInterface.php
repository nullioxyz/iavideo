<?php

namespace App\Domain\AIModels\Contracts\Repositories;

use App\Domain\AIModels\Models\Preset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PresetRepositoryInterface
{
    /**
     * @param  array{aspect_ratio?:?string,tags?:list<string>}  $filters
     */
    public function paginate(int $modelId, int $perPage = 15, int $page = 1, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Preset;

    public function findByModel(int $modelId): LengthAwarePaginator;

    /**
     * @return array{
     *     aspect_ratios:list<string>,
     *     tags:list<array{id:int,name:string,slug:string}>
     * }
     */
    public function listFilterOptions(int $modelId): array;
}
