<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_payload(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        // Estrutura padrão do JsonResource: { "data": {...} }
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $response->assertJsonPath('data.token_type', 'bearer');

        $json = $response->json('data');
        $this->assertIsString($json['access_token']);
        $this->assertNotEmpty($json['access_token']);

        $this->assertIsInt($json['expires_in']);
        $this->assertGreaterThan(0, $json['expires_in']);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);
    }

    public function test_login_fails_when_user_is_inactive(): void
    {
        $user = User::factory()->create([
            'active' => false,
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertForbidden(403);
        $response->assertJson([
            'message' => __('validation.inactive_user'),
        ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
                'password',
            ],
        ]);
    }

    public function test_login_inactive_user_message_in_portuguese_when_accept_language_pt_br(): void
    {
        $user = User::factory()->create([
            'active' => false,
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeader('Accept-Language', 'pt-BR')
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Usuário inativo.',
        ]);
    }

    public function test_login_inactive_user_message_in_english_when_accept_language_en(): void
    {
        $user = User::factory()->create([
            'active' => false,
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'User is inactive.',
        ]);
    }

    public function test_login_validation_messages_in_portuguese(): void
    {
        $response = $this->withHeader('Accept-Language', 'pt-BR')
            ->postJson('/api/auth/login', []);

        $response->assertStatus(422);

        $response->assertJsonPath('errors.email.0', 'Informe o e-mail.');
        $response->assertJsonPath('errors.password.0', 'Informe a senha.');
    }

    public function test_login_validation_messages_in_english(): void
    {
        $response = $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/auth/login', []);

        $response->assertStatus(422);

        $response->assertJsonPath('errors.email.0', 'Email is required.');
        $response->assertJsonPath('errors.password.0', 'Password is required.');
    }
}