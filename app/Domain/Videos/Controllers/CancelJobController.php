<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Resources\InputJobResource;
use App\Domain\Videos\UseCases\CancelInputPredictionUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class CancelJobController extends Controller
{
    public function __construct(
        private readonly CancelInputPredictionUseCase $useCase,
    ) {}

    public function __invoke(int $job): InputJobResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $input = Input::query()->find($job);

        if (! $input instanceof Input || (int) $input->user_id !== (int) $user->getKey()) {
            throw ValidationException::withMessages([
                'job' => [__('validation.custom.input_id.exists')],
            ]);
        }

        if (! in_array($input->status, [Input::CREATED, Input::PROCESSING], true)) {
            throw ValidationException::withMessages([
                'job' => [__('validation.custom.input_id.exists')],
            ]);
        }

        $updatedInput = $this->useCase->execute((int) $user->getKey(), (int) $input->getKey());

        return new InputJobResource($updatedInput);
    }
}
