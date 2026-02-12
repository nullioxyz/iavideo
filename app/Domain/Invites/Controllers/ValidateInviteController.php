<?php

namespace App\Domain\Invites\Controllers;

use App\Domain\Invites\DTO\ValidateInviteDTO;
use App\Domain\Invites\Requests\ValidateInviteRequest;
use App\Domain\Invites\UseCases\ValidateInviteUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ValidateInviteController extends Controller
{
    public function __construct(
        private readonly ValidateInviteUseCase $useCase,
    ) {}

    public function __invoke(ValidateInviteRequest $request): JsonResponse
    {
        $dto = ValidateInviteDTO::fromArray($request->validated());

        return response()->json(
            $this->useCase->execute($dto->hash)
        );
    }
}
