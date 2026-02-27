<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Videos\UseCases\GetDailyGenerationQuotaUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class JobsQuotaController extends Controller
{
    public function __construct(
        private readonly GetDailyGenerationQuotaUseCase $useCase,
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        return response()->json([
            'data' => $this->useCase->execute($user),
        ]);
    }
}

