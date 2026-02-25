<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Models\User;

final class GetAuthenticatedUserUseCase
{
    public function execute(User $user): User
    {
        return $user->refresh();
    }
}
