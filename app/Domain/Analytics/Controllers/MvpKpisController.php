<?php

namespace App\Domain\Analytics\Controllers;

use App\Domain\Analytics\Resources\MvpKpiResource;
use App\Domain\Analytics\UseCases\GetMvpKpisUseCase;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Http\Controllers\Controller;

class MvpKpisController extends Controller
{
    public function __construct(
        private readonly GetMvpKpisUseCase $useCase,
    ) {}

    public function __invoke(): MvpKpiResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        if (! $user->hasAnyRole(RoleNames::adminPanelRoles())) {
            abort(403);
        }

        return new MvpKpiResource($this->useCase->execute());
    }
}
