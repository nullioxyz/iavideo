<?php

namespace App\Domain\Broadcasting\Contracts;

use App\Domain\Auth\Models\User;

interface ChannelAuthorizerContract
{
    public function join(User $user, mixed ...$params): bool|array;
}
