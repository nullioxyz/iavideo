<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Videos\Requests\InputCreateRequest;
use App\Domain\Videos\Resources\InputResource;
use App\Domain\Videos\UseCases\CreateInputUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class InputCreateController extends Controller
{
    public function __construct(
        private CreateInputUseCase $useCase
    ) {}

    public function __invoke(InputCreateRequest $request): JsonResource|JsonResponse
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $file = $request->file('image');
        $dto = new \App\Domain\Videos\DTO\InputCreateDTO(
            modelId: (int) $request->input('model_id'),
            presetId: (int) $request->input('preset_id'),
            durationSeconds: $request->filled('duration_seconds')
                ? (int) $request->input('duration_seconds')
                : null,
            title: $request->input('title'),
            originalFilename: $request->file('image')?->getClientOriginalName(),
            mimeType: $request->file('image')?->getMimeType(),
            sizeBytes: $request->file('image')?->getSize(),
        );

        try {
            $input = $this->useCase->execute(
                $user,
                $dto,
                $file
            );
        } catch (\DomainException|\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return new InputResource($input);
    }
}
