<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Models\Input;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetUserJobDetailUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $inputId): Input
    {
        $input = $this->repository->findOwnedByIdWithRelations($userId, $inputId);

        if (! $input instanceof Input) {
            throw (new ModelNotFoundException())->setModel(Input::class, [$inputId]);
        }

        return $input;
    }
}
