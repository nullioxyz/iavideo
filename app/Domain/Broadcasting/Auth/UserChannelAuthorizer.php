<?php

namespace App\Domain\Broadcasting\Auth;

use App\Domain\Auth\Models\User;
use App\Domain\Broadcasting\Contracts\ChannelAuthorizerContract;

class UserChannelAuthorizer implements ChannelAuthorizerContract
{
    public function join(User $user, mixed ...$params): bool|array
    {
        $userId = isset($params[0]) ? (int) $params[0] : 0;

        return $user->getKey() === $userId;
    }
}
