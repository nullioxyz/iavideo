<?php

namespace App\Domain\AIModels\Repositories;

use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use App\Domain\AIModels\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AIModelsRepository implements AIModelsRepositoryInterface
{
    public function __construct(
        private readonly Model $model
    ) {}

    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        return $this->model->newQuery()
            ->where('active', true)
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
