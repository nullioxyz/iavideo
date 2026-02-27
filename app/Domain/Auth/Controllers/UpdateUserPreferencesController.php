<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\UpdateUserPreferencesRequest;
use App\Domain\Auth\Resources\MeResource;
use App\Domain\Auth\UseCases\UpdateUserPreferencesUseCase;
use App\Domain\Observability\Support\StructuredActivityLogger;
use App\Http\Controllers\Controller;

class UpdateUserPreferencesController extends Controller
{
    public function __construct(
        private readonly UpdateUserPreferencesUseCase $useCase,
        private readonly StructuredActivityLogger $activityLogger,
    ) {}

    public function __invoke(UpdateUserPreferencesRequest $request): MeResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $payload = $request->validated();
        $updated = $this->useCase->execute($user, $payload);

        if (array_key_exists('language_id', $payload)) {
            $this->activityLogger->log('language_changed', $updated, [
                'language_id' => $updated->language_id,
            ]);
        }

        if (array_key_exists('theme_preference', $payload)) {
            $this->activityLogger->log('theme_changed', $updated, [
                'theme_preference' => $updated->theme_preference,
            ]);
        }

        return new MeResource($updated);
    }
}
