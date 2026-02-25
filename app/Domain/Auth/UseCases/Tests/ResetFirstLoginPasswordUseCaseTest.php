<?php

namespace App\Domain\Auth\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\UseCases\ResetFirstLoginPasswordUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResetFirstLoginPasswordUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_password_and_turns_off_first_login_flag(): void
    {
        $user = User::factory()->mustResetPassword()->create([
            'password' => bcrypt('password'),
        ]);

        $useCase = new ResetFirstLoginPasswordUseCase;

        $updated = $useCase->execute($user, 'password', 'new-password-123');

        $this->assertFalse((bool) $updated->must_reset_password);
        $this->assertTrue(Hash::check('new-password-123', (string) $updated->password));
    }

    public function test_it_throws_when_current_password_is_invalid(): void
    {
        $user = User::factory()->mustResetPassword()->create([
            'password' => bcrypt('password'),
        ]);

        $useCase = new ResetFirstLoginPasswordUseCase;

        $this->expectException(ValidationException::class);

        $useCase->execute($user, 'wrong-password', 'new-password-123');
    }

    public function test_it_throws_when_password_was_already_reset(): void
    {
        $user = User::factory()->create([
            'must_reset_password' => false,
            'password' => bcrypt('password'),
        ]);

        $useCase = new ResetFirstLoginPasswordUseCase;

        $this->expectException(ValidationException::class);

        $useCase->execute($user, 'password', 'new-password-123');
    }
}
