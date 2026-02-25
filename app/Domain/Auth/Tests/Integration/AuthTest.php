<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([RoleNames::ADMIN, RoleNames::DEV, RoleNames::PLATFORM_USER] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }
    }

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

    public function test_login_allows_admin_role_user_on_platform_api(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);
        $user->assignRole(RoleNames::ADMIN);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.token_type', 'bearer');
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

        $response->assertForbidden();
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

    public function test_login_fails_when_user_is_suspended(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'suspended_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $response->assertJson([
            'message' => __('validation.suspended_user'),
        ]);
    }

    public function test_login_persists_audit_metadata_and_updates_user_audit_fields(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'last_login_at' => null,
            'last_activity_at' => null,
            'user_agent' => null,
        ]);

        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36';

        $response = $this
            ->withHeader('User-Agent', $userAgent)
            ->withHeader('CF-IPCountry', 'BR')
            ->withHeader('X-Forwarded-For', '177.11.22.33')
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('login_audits', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'success' => true,
            'country_code' => 'BR',
            'forwarded_for' => '177.11.22.33',
            'browser' => 'Chrome',
            'platform' => 'Linux',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_activity_at);
        $this->assertSame($userAgent, $user->user_agent);
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
