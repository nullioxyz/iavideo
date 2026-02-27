<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Models\User;

class UpdateUserPreferencesUseCase
{
    public function execute(User $user, array $data): User
    {
        $updates = [];

        if (array_key_exists('language_id', $data)) {
            $updates['language_id'] = $data['language_id'];
        }

        if (array_key_exists('theme_preference', $data)) {
            $updates['theme_preference'] = $data['theme_preference'] ?? 'system';
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }

        return $user->refresh();
    }
}
