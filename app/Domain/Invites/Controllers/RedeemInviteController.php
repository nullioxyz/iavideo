<?php

namespace App\Domain\Invites\Controllers;

use App\Domain\Invites\DTO\InviteRedeemDTO;
use App\Domain\Invites\Requests\RedeemInviteRequest;
use App\Domain\Invites\Resources\InviteResource;
use App\Domain\Invites\UseCases\RedeemInviteUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RedeemInviteController extends Controller
{
    public function __construct(
        private readonly RedeemInviteUseCase $useCase,
    ) {}

    public function __invoke(RedeemInviteRequest $request): JsonResponse|InviteResource
    {
        try {
            $invite = $this->useCase->execute(
                $request->user('api'),
                InviteRedeemDTO::fromArray($request->validated())
            );

            return new InviteResource($invite);
        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
