<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_or_dev_role_can_access_filament_panel(): void
    {
        $this->seedRoles();

        $platformUser = User::factory()->create([
            'active' => true,
            'suspended_at' => null,
        ]);
        $platformUser->assignRole(RoleNames::PLATFORM_USER);

        $devUser = User::factory()->create([
            'active' => true,
            'suspended_at' => null,
        ]);
        $devUser->assignRole(RoleNames::DEV);

        $platformAdminModel = Admin::query()->findOrFail($platformUser->getKey());
        $devAdminModel = Admin::query()->findOrFail($devUser->getKey());

        $this->assertFalse($platformAdminModel->canAccessFilament());
        $this->assertTrue($devAdminModel->canAccessFilament());
    }

    private function seedRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([RoleNames::ADMIN, RoleNames::DEV, RoleNames::PLATFORM_USER] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);
        }
    }
}

