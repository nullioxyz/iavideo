<?php

namespace App\Domain\AIModels\Contracts\Repositories;

use App\Domain\AIModels\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AIModelsRepositoryInterface
{
    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?Model;

    public function findBySlug(string $slug): ?Model;
}
