<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Resources\MeResource;
use App\Domain\Auth\UseCases\GetAuthenticatedUserUseCase;
use App\Http\Controllers\Controller;

class MeController extends Controller
{
    public function __construct(
        private readonly GetAuthenticatedUserUseCase $useCase,
    ) {}

    public function __invoke(): MeResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        return new MeResource($this->useCase->execute($user));
    }
}
