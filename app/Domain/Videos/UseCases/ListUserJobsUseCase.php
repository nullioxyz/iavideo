<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListUserJobsUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginateWithRelations($userId, $perPage, $page);
    }
}
