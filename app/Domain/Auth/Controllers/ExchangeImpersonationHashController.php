<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\ExchangeImpersonationHashRequest;
use App\Domain\Auth\UseCases\ConsumeImpersonationLinkUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\JWTGuard;

final class ExchangeImpersonationHashController extends Controller
{
    public function __construct(
        private readonly ConsumeImpersonationLinkUseCase $useCase,
    ) {}

    public function __invoke(ExchangeImpersonationHashRequest $request): JsonResponse
    {
        $actor = auth('api')->user();
        if (! $actor instanceof User) {
            abort(401);
        }

        $targetUser = $this->useCase->execute(
            $actor,
            (string) $request->input('hash'),
        );

        $guard = auth('api');
        if (! $guard instanceof JWTGuard) {
            abort(500, 'JWT guard is not configured.');
        }

        /** @var string $token */
        $token = $guard->claims([
            'impersonated_by' => (int) $actor->getKey(),
            'impersonation' => true,
        ])->login($targetUser);

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'impersonation' => [
                    'is_impersonating' => true,
                    'actor_id' => (int) $actor->getKey(),
                    'subject_id' => (int) $targetUser->getKey(),
                ],
            ],
        ]);
    }
}

