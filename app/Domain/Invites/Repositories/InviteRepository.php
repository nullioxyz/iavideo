<?php

namespace App\Domain\Invites\Repositories;

use App\Domain\Invites\Contracts\Repositories\InviteRepositoryInterface;
use App\Domain\Invites\Models\Invite;

class InviteRepository implements InviteRepositoryInterface
{
    public function create(array $data): Invite
    {
        return Invite::query()->create($data);
    }

    public function findByToken(string $token): ?Invite
    {
        return Invite::query()->where('token', $token)->first();
    }

    public function save(Invite $invite): Invite
    {
        $invite->save();

        return $invite->refresh();
    }
}
