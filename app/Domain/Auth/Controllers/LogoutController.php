<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\UseCases\LogoutUseCase;
use App\Domain\Broadcasting\Events\UserSessionLoggedOutBroadcast;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    public function __construct(
        private readonly LogoutUseCase $useCase,
    ) {}

    public function __invoke(): Response
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        event(new UserSessionLoggedOutBroadcast((int) $user->getKey()));
        $this->useCase->execute($user);

        return response()->noContent();
    }
}
