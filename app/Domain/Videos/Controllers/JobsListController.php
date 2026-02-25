<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Requests\ListUserJobsRequest;
use App\Domain\Videos\Resources\InputJobResource;
use App\Domain\Videos\UseCases\ListUserJobsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;

class JobsListController extends Controller
{
    public function __construct(
        private readonly ListUserJobsUseCase $useCase,
    ) {}

    public function __invoke(ListUserJobsRequest $request): PaginatedResource
    {
        $userId = (int) auth('api')->id();
        $perPage = (int) $request->integer('per_page', 15);
        $page = (int) $request->integer('page', 1);

        $paginator = $this->useCase->execute($userId, $perPage, $page);

        return new PaginatedResource($paginator, InputJobResource::class);
    }
}
