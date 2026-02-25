<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\FirstLoginResetPasswordRequest;
use App\Domain\Auth\Resources\MeResource;
use App\Domain\Auth\UseCases\ResetFirstLoginPasswordUseCase;
use App\Http\Controllers\Controller;

class FirstLoginResetPasswordController extends Controller
{
    public function __construct(
        private readonly ResetFirstLoginPasswordUseCase $useCase,
    ) {}

    public function __invoke(FirstLoginResetPasswordRequest $request): MeResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $updatedUser = $this->useCase->execute(
            $user,
            (string) $request->input('current_password'),
            (string) $request->input('password'),
        );

        return new MeResource($updatedUser);
    }
}
