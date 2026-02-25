<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Resources\InputJobResource;
use App\Domain\Videos\UseCases\GetUserJobDetailUseCase;
use App\Http\Controllers\Controller;

class JobDetailController extends Controller
{
    public function __construct(
        private readonly GetUserJobDetailUseCase $useCase,
    ) {}

    public function __invoke(int $job): InputJobResource
    {
        $userId = (int) auth('api')->id();

        $input = $this->useCase->execute($userId, $job);

        return new InputJobResource($input);
    }
}
