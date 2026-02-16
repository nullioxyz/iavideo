<?php

namespace App\Domain\AIModels\UseCases;

use App\Domain\AIModels\Contracts\Repositories\AIModelsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AIModelsListUseCase
{
    public function __construct(
        private readonly AIModelsRepositoryInterface $modelRepository
    ) {}

    public function execute(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->modelRepository->paginate($perPage, $page);
    }
}
