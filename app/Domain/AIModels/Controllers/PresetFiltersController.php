<?php

namespace App\Domain\AIModels\Controllers;

use App\Domain\AIModels\UseCases\PresetFiltersUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PresetFiltersController extends Controller
{
    public function __construct(
        private readonly PresetFiltersUseCase $useCase,
    ) {}

    public function __invoke(int $model): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->execute($model),
        ]);
    }
}
