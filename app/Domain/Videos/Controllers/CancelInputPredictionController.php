<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Events\CancelPredictionInput;
use App\Domain\Videos\Requests\CancelInputPredictionRequest;
use App\Domain\Auth\Models\User;
use App\Http\Controllers\Controller;

class CancelInputPredictionController extends Controller
{
    public function __invoke(CancelInputPredictionRequest $request)
    {
        $inputId = (int) $request->input('input_id');
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        CancelPredictionInput::dispatch((int) $user->getKey(), $inputId);

        return response()->noContent();
    }
}
