<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Auth\UseCases\CreateImpersonationLinkUseCase;
use App\Domain\Auth\Models\ImpersonationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminAssumeUserTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_admin_can_exchange_hash_and_impersonate_platform_user(): void
    {
        $this->seedRoles();

        $actor = User::factory()->create([
            'active' => true,
        ]);
        $actor->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create([
            'active' => true,
        ]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $hash = app(CreateImpersonationLinkUseCase::class)->execute($actor, $target);
        $token = $this->loginAndGetToken($actor);

        $response = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => $hash,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.token_type', 'bearer');
        $response->assertJsonPath('data.impersonation.is_impersonating', true);
        $response->assertJsonPath('data.impersonation.actor_id', $actor->getKey());
        $response->assertJsonPath('data.impersonation.subject_id', $target->getKey());
        $this->assertIsString($response->json('data.access_token'));
    }

    public function test_exchange_rejects_invalid_hash(): void
    {
        $this->seedRoles();

        $actor = User::factory()->create([
            'active' => true,
        ]);
        $actor->assignRole(RoleNames::ADMIN);

        $token = $this->loginAndGetToken($actor);

        $response = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => str_repeat('a', 64),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.hash.0', __('validation.invalid_impersonation_hash'));
    }

    public function test_exchange_rejects_hash_from_other_actor(): void
    {
        $this->seedRoles();

        $actorOne = User::factory()->create(['active' => true]);
        $actorOne->assignRole(RoleNames::ADMIN);

        $actorTwo = User::factory()->create(['active' => true]);
        $actorTwo->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $hash = app(CreateImpersonationLinkUseCase::class)->execute($actorOne, $target);
        $token = $this->loginAndGetToken($actorTwo);

        $response = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => $hash,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.hash.0', __('validation.invalid_impersonation_hash'));
    }

    public function test_exchange_hash_is_one_time_use(): void
    {
        $this->seedRoles();

        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::DEV);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $hash = app(CreateImpersonationLinkUseCase::class)->execute($actor, $target);
        $token = $this->loginAndGetToken($actor);

        $first = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => $hash,
        ]);
        $first->assertOk();

        $second = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => $hash,
        ]);
        $second->assertUnprocessable();
        $second->assertJsonPath('errors.hash.0', __('validation.invalid_impersonation_hash'));
    }

    public function test_exchange_requires_authenticated_api_user(): void
    {
        $response = $this->postJson('/api/auth/impersonation/exchange', [
            'hash' => str_repeat('a', 64),
        ]);

        $response->assertUnauthorized();
    }

    public function test_exchange_rejects_expired_hash(): void
    {
        $this->seedRoles();

        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $hash = app(CreateImpersonationLinkUseCase::class)->execute($actor, $target);

        ImpersonationLink::query()
            ->where('token_hash', hash('sha256', $hash))
            ->update(['expires_at' => now()->subMinute()]);

        $token = $this->loginAndGetToken($actor);

        $response = $this->withJwt($token)->postJson('/api/auth/impersonation/exchange', [
            'hash' => $hash,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.hash.0', __('validation.invalid_impersonation_hash'));
    }

    private function seedRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            RoleNames::ADMIN,
            RoleNames::DEV,
            RoleNames::PLATFORM_USER,
        ] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }
    }
}
