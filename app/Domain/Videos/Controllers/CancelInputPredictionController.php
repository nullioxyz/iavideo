<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Videos\Requests\CancelInputPredictionRequest;
use App\Domain\Videos\Resources\InputJobResource;
use App\Domain\Videos\UseCases\CancelInputPredictionUseCase;
use App\Http\Controllers\Controller;

class CancelInputPredictionController extends Controller
{
    public function __construct(
        private readonly CancelInputPredictionUseCase $useCase,
    ) {}

    public function __invoke(CancelInputPredictionRequest $request): InputJobResource
    {
        $inputId = (int) $request->input('input_id');
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $input = $this->useCase->execute((int) $user->getKey(), $inputId);

        return new InputJobResource($input);
    }
}
