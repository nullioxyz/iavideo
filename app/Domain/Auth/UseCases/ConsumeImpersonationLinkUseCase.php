<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Models\ImpersonationLink;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use Illuminate\Validation\ValidationException;

final class ConsumeImpersonationLinkUseCase
{
    public function execute(User $actor, string $token): User
    {
        if (! $actor->hasAnyRole(RoleNames::adminPanelRoles())) {
            throw ValidationException::withMessages([
                'actor' => [__('validation.forbidden_admin_assume_user')],
            ]);
        }

        $link = ImpersonationLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $link) {
            throw ValidationException::withMessages([
                'hash' => [__('validation.invalid_impersonation_hash')],
            ]);
        }

        if ((int) $link->actor_user_id !== (int) $actor->getKey()) {
            throw ValidationException::withMessages([
                'hash' => [__('validation.invalid_impersonation_hash')],
            ]);
        }

        if ($link->used_at !== null || $link->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'hash' => [__('validation.invalid_impersonation_hash')],
            ]);
        }

        /** @var User|null $target */
        $target = User::query()->find($link->target_user_id);
        if (! $target || ! $target->active || $target->suspended_at !== null) {
            throw ValidationException::withMessages([
                'target_user' => [__('validation.target_user_unavailable')],
            ]);
        }

        $link->forceFill(['used_at' => now()])->save();

        return $target;
    }
}

