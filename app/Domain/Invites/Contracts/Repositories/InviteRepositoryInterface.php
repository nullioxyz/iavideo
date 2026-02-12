<?php

namespace App\Domain\Invites\Contracts\Repositories;

use App\Domain\Invites\Models\Invite;

interface InviteRepositoryInterface
{
    public function create(array $data): Invite;

    public function findByToken(string $token): ?Invite;

    public function save(Invite $invite): Invite;
}
