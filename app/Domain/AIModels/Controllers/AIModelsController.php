<?php

namespace App\Domain\AIModels\Controllers;

use App\Domain\AIModels\Requests\IAModelsRequest;
use App\Domain\AIModels\Resources\AIModelResource;
use App\Domain\AIModels\UseCases\AIModelsListUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;

class AIModelsController extends Controller
{
    public function __construct(
        private AIModelsListUseCase $useCase
    ) {}

    public function __invoke(IAModelsRequest $request)
    {
        $paginator = $this->useCase->execute(
            perPage: (int) $request->input('per_page', 15),
            page: (int) $request->input('page', 1),
        );

        return new PaginatedResource($paginator, AIModelResource::class);
    }
}
