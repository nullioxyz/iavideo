<?php

namespace App\Domain\Credits\Controllers;

use App\Domain\Credits\Requests\ListVideoGenerationsRequest;
use App\Domain\Credits\Resources\VideoGenerationHistoryEntryResource;
use App\Domain\Credits\UseCases\ListVideoGenerationsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;

class CreditsVideoGenerationsController extends Controller
{
    public function __construct(
        private readonly ListVideoGenerationsUseCase $useCase,
    ) {}

    public function __invoke(ListVideoGenerationsRequest $request): PaginatedResource
    {
        $userId = (int) auth('api')->id();
        $perPage = (int) $request->integer('per_page', 15);
        $page = (int) $request->integer('page', 1);

        $paginator = $this->useCase->execute($userId, $perPage, $page);

        return new PaginatedResource($paginator, VideoGenerationHistoryEntryResource::class);
    }
}
