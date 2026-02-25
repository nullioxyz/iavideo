<?php

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ResetFirstLoginPasswordUseCase
{
    public function execute(User $user, string $currentPassword, string $newPassword): User
    {
        if (! $user->must_reset_password) {
            throw ValidationException::withMessages([
                'password' => [__('validation.first_login_password_already_reset')],
            ]);
        }

        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('validation.invalid_current_password')],
            ]);
        }

        $user->update([
            'password' => $newPassword,
            'must_reset_password' => false,
        ]);

        return $user->refresh();
    }
}
