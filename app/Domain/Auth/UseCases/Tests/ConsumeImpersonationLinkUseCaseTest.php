<?php

namespace App\Domain\Auth\UseCases\Tests;

use App\Domain\Auth\Models\ImpersonationLink;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Domain\Auth\UseCases\ConsumeImpersonationLinkUseCase;
use App\Domain\Auth\UseCases\CreateImpersonationLinkUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ConsumeImpersonationLinkUseCaseTest extends TestCase
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

    public function test_it_consumes_link_and_marks_used_at(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $token = (new CreateImpersonationLinkUseCase)->execute($actor, $target);

        $useCase = new ConsumeImpersonationLinkUseCase;
        $resolvedTarget = $useCase->execute($actor, $token);

        $this->assertSame($target->getKey(), $resolvedTarget->getKey());

        /** @var ImpersonationLink $link */
        $link = ImpersonationLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        $this->assertNotNull($link->used_at);
    }

    public function test_it_throws_for_expired_link(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::DEV);

        $target = User::factory()->create(['active' => true]);

        $token = (new CreateImpersonationLinkUseCase)->execute($actor, $target);

        ImpersonationLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->update([
                'expires_at' => now()->subMinute(),
            ]);

        $useCase = new ConsumeImpersonationLinkUseCase;

        $this->expectException(ValidationException::class);
        $useCase->execute($actor, $token);
    }

    public function test_it_throws_for_actor_without_admin_or_dev_role(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $token = (new CreateImpersonationLinkUseCase)->execute($admin, $target);

        $noRoleActor = User::factory()->create(['active' => true]);
        $noRoleActor->assignRole(RoleNames::PLATFORM_USER);

        $useCase = new ConsumeImpersonationLinkUseCase;

        $this->expectException(ValidationException::class);
        $useCase->execute($noRoleActor, $token);
    }

    public function test_it_throws_when_target_becomes_suspended_before_consumption(): void
    {
        $actor = User::factory()->create(['active' => true]);
        $actor->assignRole(RoleNames::ADMIN);

        $target = User::factory()->create(['active' => true]);
        $target->assignRole(RoleNames::PLATFORM_USER);

        $token = (new CreateImpersonationLinkUseCase)->execute($actor, $target);

        $target->forceFill([
            'active' => false,
            'suspended_at' => now(),
        ])->save();

        $useCase = new ConsumeImpersonationLinkUseCase;

        $this->expectException(ValidationException::class);
        $useCase->execute($actor, $token);
    }
}

