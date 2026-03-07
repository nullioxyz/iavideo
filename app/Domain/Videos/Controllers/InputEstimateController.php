<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Credits\Resources\GenerationEstimateResource;
use App\Domain\Credits\UseCases\EstimateGenerationCreditsUseCase;
use App\Domain\Videos\Requests\EstimateGenerationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class InputEstimateController extends Controller
{
    public function __construct(
        private readonly EstimateGenerationCreditsUseCase $useCase,
    ) {}

    public function __invoke(EstimateGenerationRequest $request): GenerationEstimateResource|JsonResponse
    {
        try {
            $quote = $this->useCase->execute(
                modelId: (int) $request->integer('model_id'),
                presetId: (int) $request->integer('preset_id'),
                durationSeconds: $request->filled('duration_seconds')
                    ? (int) $request->integer('duration_seconds')
                    : null,
            );
        } catch (\DomainException|\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return new GenerationEstimateResource($quote);
    }
}
