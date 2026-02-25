<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Models\ImpersonationLink;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use Illuminate\Validation\ValidationException;

final class CreateImpersonationLinkUseCase
{
    public function execute(User $actor, User $target, int $ttlMinutes = 10): string
    {
        if (! $actor->hasAnyRole(RoleNames::adminPanelRoles())) {
            throw ValidationException::withMessages([
                'actor' => [__('validation.forbidden_admin_assume_user')],
            ]);
        }

        if (! $target->active || $target->suspended_at !== null) {
            throw ValidationException::withMessages([
                'target_user' => [__('validation.target_user_unavailable')],
            ]);
        }

        $token = bin2hex(random_bytes(32));

        ImpersonationLink::query()->create([
            'actor_user_id' => (int) $actor->getKey(),
            'target_user_id' => (int) $target->getKey(),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(max(1, $ttlMinutes)),
        ]);

        return $token;
    }
}

