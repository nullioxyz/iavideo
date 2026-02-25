<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Requests\RenameInputTitleRequest;
use App\Domain\Videos\Resources\InputResource;
use App\Domain\Videos\UseCases\RenameInputTitleUseCase;
use App\Http\Controllers\Controller;

class RenameInputTitleController extends Controller
{
    public function __construct(
        private readonly RenameInputTitleUseCase $useCase,
    ) {}

    public function __invoke(RenameInputTitleRequest $request, int $job): InputResource
    {
        $userId = (int) auth('api')->id();

        $input = $this->useCase->execute(
            userId: $userId,
            inputId: $job,
            title: $request->input('title'),
        );

        return new InputResource($input);
    }
}
