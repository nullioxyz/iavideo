<?php

namespace Database\Seeders;

use App\Domain\Auth\Support\RoleNames;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
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

