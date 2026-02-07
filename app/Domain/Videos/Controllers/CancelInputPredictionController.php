<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\Events\CancelPredictionInput;
use App\Domain\Videos\Requests\CancelInputPredictionRequest;
use App\Http\Controllers\Controller;

class CancelInputPredictionController extends Controller
{
    public function __invoke(CancelInputPredictionRequest $request)
    {
        $inputId = (int) $request->input('input_id');

        CancelPredictionInput::dispatch($inputId);

        return response()->noContent();
    }
}
