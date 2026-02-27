<?php

namespace App\Domain\Analytics\Controllers;

use App\Domain\Analytics\Resources\OpsMetricsResource;
use App\Domain\Analytics\UseCases\GetOpsMetricsUseCase;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Http\Controllers\Controller;

class OpsMetricsController extends Controller
{
    public function __construct(
        private readonly GetOpsMetricsUseCase $useCase,
    ) {}

    public function __invoke(): OpsMetricsResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        if (! $user->hasAnyRole(RoleNames::adminPanelRoles())) {
            abort(403);
        }

        return new OpsMetricsResource($this->useCase->execute());
    }
}
