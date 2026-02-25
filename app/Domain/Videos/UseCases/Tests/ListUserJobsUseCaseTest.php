<?php

namespace App\Domain\Videos\UseCases\Tests;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\UseCases\ListUserJobsUseCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use PHPUnit\Framework\TestCase;

class ListUserJobsUseCaseTest extends TestCase
{
    public function test_it_returns_paginated_jobs_from_repository(): void
    {
        $paginator = new PaginationLengthAwarePaginator(
            items: [['id' => 10]],
            total: 1,
            perPage: 15,
            currentPage: 1,
        );

        $repository = new class($paginator) implements InputRepositoryInterface
        {
            public function __construct(private readonly LengthAwarePaginator $paginator) {}

            public int $receivedUserId = 0;

            public int $receivedPerPage = 0;

            public int $receivedPage = 0;

            public function paginate(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                return $this->paginator;
            }

            public function paginateWithRelations(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                $this->receivedUserId = $userId;
                $this->receivedPerPage = $perPage;
                $this->receivedPage = $page;

                return $this->paginator;
            }

            public function findById(int $id): ?\App\Domain\Videos\Models\Input
            {
                return null;
            }

            public function findOwnedById(int $userId, int $id): ?\App\Domain\Videos\Models\Input
            {
                return null;
            }

            public function findOwnedByIdWithRelations(int $userId, int $id): ?\App\Domain\Videos\Models\Input
            {
                return null;
            }

            public function create(array $data): \App\Domain\Videos\Models\Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function update(\App\Domain\Videos\Models\Input $input, array $data): \App\Domain\Videos\Models\Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function delete(\App\Domain\Videos\Models\Input $input): bool
            {
                throw new \BadMethodCallException('not needed');
            }
        };

        $useCase = new ListUserJobsUseCase($repository);

        $result = $useCase->execute(99, 20, 2);

        $this->assertSame($paginator, $result);
        $this->assertSame(99, $repository->receivedUserId);
        $this->assertSame(20, $repository->receivedPerPage);
        $this->assertSame(2, $repository->receivedPage);
    }
}
