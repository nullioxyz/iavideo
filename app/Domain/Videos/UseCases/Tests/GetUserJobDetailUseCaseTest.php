<?php

namespace App\Domain\Videos\UseCases\Tests;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\UseCases\GetUserJobDetailUseCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

class GetUserJobDetailUseCaseTest extends TestCase
{
    public function test_it_returns_input_when_job_belongs_to_user(): void
    {
        $expected = new Input(['id' => 10, 'user_id' => 99]);

        $repository = new class($expected) implements InputRepositoryInterface
        {
            public function __construct(private readonly Input $input) {}

            public int $receivedUserId = 0;

            public int $receivedInputId = 0;

            public function paginate(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                throw new \BadMethodCallException('not needed');
            }

            public function paginateWithRelations(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                throw new \BadMethodCallException('not needed');
            }

            public function findById(int $id): ?Input
            {
                return null;
            }

            public function findOwnedById(int $userId, int $id): ?Input
            {
                return null;
            }

            public function findOwnedByIdWithRelations(int $userId, int $id): ?Input
            {
                $this->receivedUserId = $userId;
                $this->receivedInputId = $id;

                return $this->input;
            }

            public function create(array $data): Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function update(Input $input, array $data): Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function delete(Input $input): bool
            {
                throw new \BadMethodCallException('not needed');
            }
        };

        $useCase = new GetUserJobDetailUseCase($repository);

        $result = $useCase->execute(99, 10);

        $this->assertSame($expected, $result);
        $this->assertSame(99, $repository->receivedUserId);
        $this->assertSame(10, $repository->receivedInputId);
    }

    public function test_it_throws_when_job_is_not_owned_or_not_found(): void
    {
        $repository = new class implements InputRepositoryInterface
        {
            public function paginate(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                throw new \BadMethodCallException('not needed');
            }

            public function paginateWithRelations(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
            {
                throw new \BadMethodCallException('not needed');
            }

            public function findById(int $id): ?Input
            {
                return null;
            }

            public function findOwnedById(int $userId, int $id): ?Input
            {
                return null;
            }

            public function findOwnedByIdWithRelations(int $userId, int $id): ?Input
            {
                return null;
            }

            public function create(array $data): Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function update(Input $input, array $data): Input
            {
                throw new \BadMethodCallException('not needed');
            }

            public function delete(Input $input): bool
            {
                throw new \BadMethodCallException('not needed');
            }
        };

        $useCase = new GetUserJobDetailUseCase($repository);

        $this->expectException(ModelNotFoundException::class);

        $useCase->execute(99, 10);
    }
}
