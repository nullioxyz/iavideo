<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\FirstLoginResetPasswordRequest;
use App\Domain\Auth\Resources\MeResource;
use App\Domain\Observability\Support\StructuredActivityLogger;
use App\Domain\Auth\UseCases\ResetFirstLoginPasswordUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class FirstLoginResetPasswordController extends Controller
{
    public function __construct(
        private readonly ResetFirstLoginPasswordUseCase $useCase,
        private readonly StructuredActivityLogger $activityLogger,
    ) {}

    public function __invoke(FirstLoginResetPasswordRequest $request): MeResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        try {
            $updatedUser = $this->useCase->execute(
                $user,
                (string) $request->input('current_password'),
                (string) $request->input('password'),
            );
        } catch (ValidationException $exception) {
            $this->activityLogger->log('password_reset_failed', $user, [
                'reason' => 'validation_error',
            ]);
            throw $exception;
        }

        $this->activityLogger->log('password_reset_success', $updatedUser, [
            'flow' => 'first_login_reset',
        ]);

        return new MeResource($updatedUser);
    }
}
