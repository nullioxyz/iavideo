<?php

namespace App\Domain\AIModels\Controllers;

use App\Domain\AIModels\Requests\PresetsRequest;
use App\Domain\AIModels\Resources\PresetsResource;
use App\Domain\AIModels\UseCases\PresetsListUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;

class PresetsController extends Controller
{
    public function __construct(
        private PresetsListUseCase $useCase
    ) {}

    public function __invoke(PresetsRequest $request)
    {
        $paginator = $this->useCase->execute(
            $request,
            perPage: (int) $request->input('per_page', 15),
            page: (int) $request->input('page', 1),
        );

        return new PaginatedResource($paginator, PresetsResource::class);
    }
}
