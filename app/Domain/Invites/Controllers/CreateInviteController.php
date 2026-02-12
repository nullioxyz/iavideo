<?php

namespace App\Domain\Invites\Controllers;

use App\Domain\Invites\DTO\InviteCreateDTO;
use App\Domain\Invites\Requests\CreateInviteRequest;
use App\Domain\Invites\Resources\InviteResource;
use App\Domain\Invites\UseCases\CreateInviteUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateInviteController extends Controller
{
    public function __construct(
        private readonly CreateInviteUseCase $useCase,
    ) {}

    public function __invoke(CreateInviteRequest $request): JsonResponse
    {
        $invite = $this->useCase->execute(
            $request->user('api'),
            InviteCreateDTO::fromArray($request->validated())
        );

        return response()->json([
            'data' => (new InviteResource($invite))->resolve($request),
        ], Response::HTTP_CREATED);
    }
}
