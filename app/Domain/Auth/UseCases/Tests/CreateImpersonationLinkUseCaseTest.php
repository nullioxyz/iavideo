<?php

namespace App\Domain\Auth\UseCases\Tests;

use App\Domain\Auth\Models\ImpersonationLink;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Domain\Auth\UseCases\CreateImpersonationLinkUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CreateImpersonationLinkUseCaseTest extends TestCase
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

    public function test_it_creates_a_single_use_hash_link(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $useCase = new CreateImpersonationLinkUseCase;
        $token = $useCase->execute($actor, $target, 15);

        $this->assertSame(64, strlen($token));

        $this->assertDatabaseHas('impersonation_links', [
            'actor_user_id' => $actor->getKey(),
            'target_user_id' => $target->getKey(),
            'token_hash' => hash('sha256', $token),
        ]);
    }

    public function test_it_throws_when_actor_has_no_admin_or_dev_role(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::PLATFORM_USER);

        $target = User::factory()->create(['active' => true]);

        $useCase = new CreateImpersonationLinkUseCase;

        $this->expectException(ValidationException::class);
        $useCase->execute($actor, $target);
    }

    public function test_it_throws_when_target_is_inactive_or_suspended(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::DEV);

        $target = User::factory()->create([
            'active' => false,
            'suspended_at' => now(),
        ]);

        $useCase = new CreateImpersonationLinkUseCase;

        $this->expectException(ValidationException::class);
        $useCase->execute($actor, $target);
    }
}

