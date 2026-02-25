<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MeAndFirstLoginResetTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_me_returns_authenticated_user_data(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'must_reset_password' => false,
            'credit_balance' => 7,
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->getJson('/api/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->getKey());
        $response->assertJsonPath('data.credit_balance', 7);
        $response->assertJsonPath('data.must_reset_password', false);
    }

    public function test_first_login_password_reset_updates_password_and_flag(): void
    {
        $user = User::factory()->mustResetPassword()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->postJson('/api/auth/first-login/reset-password', [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.must_reset_password', false);

        $this->assertTrue(Hash::check('new-password-123', (string) $user->fresh()?->password));
    }

    public function test_first_login_password_reset_requires_user_in_first_login_state(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'must_reset_password' => false,
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->postJson('/api/auth/first-login/reset-password', [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.password.0', __('validation.first_login_password_already_reset'));
    }
}
