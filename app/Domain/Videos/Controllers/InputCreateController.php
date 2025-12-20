<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Requests\InputCreateRequest;
use App\Domain\Videos\Resources\InputResource;
use App\Domain\Videos\UseCases\CreateInputUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InputCreateController extends Controller
{
    public function __construct(
        private CreateInputUseCase $useCase
    ) {}

    public function __invoke(InputCreateRequest $request): JsonResource
    {
        $userId = (int) auth('api')->id();
        $file = $request->file('image');
        $dto = new \App\Domain\Videos\DTO\InputCreateDTO(
            presetId: (int) $request->input('preset_id'),
            originalFilename: $request->file('image')?->getClientOriginalName(),
            mimeType: $request->file('image')?->getMimeType(),
            sizeBytes: $request->file('image')?->getSize(),
        );
        
        $input = $this->useCase->execute(
            $userId,
            $dto,
            $file
        );

        return new InputResource($input);

    }
}
