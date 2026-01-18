<?php

declare(strict_types=1);

namespace Tests\Feature\Permission;

use App\Actions\Permission\CreatePermissionAction;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CreatePermissionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_permissions_to_super_admin_role_not_to_user(): void
    {
        // Setup: Create Super Admin role
        $superAdminRole = Role::findOrCreate(RoleEnum::SUPER_ADMIN->value, 'web');

        // Create a Super Admin user
        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $permissions = ['test.permission.1', 'test.permission.2'];

        // Action
        app(CreatePermissionAction::class)->handle($permissions);

        // Assertions
        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->where('name', $permissionName)->first();
            $this->assertNotNull($permission);

            // EXPECTED behavior (currently failing): It should be assigned to the role
            $this->assertTrue($superAdminRole->hasPermissionTo($permissionName), 'Role should have the permission');

            // EXPECTED behavior (currently failing): It should NOT be directly assigned to the user
            $this->assertFalse($user->hasDirectPermission($permissionName), 'User should NOT have the permission directly');
        }
    }
}
