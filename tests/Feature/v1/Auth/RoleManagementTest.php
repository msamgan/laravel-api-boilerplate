<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_role(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('roles.create', 'web'));
        Permission::findOrCreate('test-permission', 'web');

        $response = $this->actingAs($user, 'web')
            ->postJson(route('v1.auth.roles.store'), [
                'name' => 'New Role',
                'permissions' => ['test-permission'],
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Role']);

        $this->assertDatabaseHas('roles', ['name' => 'New Role', 'guard_name' => 'web']);
        $role = Role::findByName('New Role', 'web');
        $this->assertTrue($role->hasPermissionTo('test-permission', 'web'));
    }

    public function test_cannot_create_role_without_permission(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('test-permission', 'web');

        $response = $this->actingAs($user, 'web')
            ->postJson(route('v1.auth.roles.store'), [
                'name' => 'New Role',
                'permissions' => ['test-permission'],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_update_role(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('roles.update', 'web'));
        Permission::findOrCreate('old-permission', 'web');
        Permission::findOrCreate('new-permission', 'web');

        $role = Role::create(['name' => 'Old Name', 'guard_name' => 'web']);
        $role->givePermissionTo('old-permission');

        $response = $this->actingAs($user, 'web')
            ->putJson(route('v1.auth.roles.update', $role), [
                'name' => 'New Name',
                'permissions' => ['new-permission'],
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $role->refresh();
        $this->assertEquals('New Name', $role->name);
        $this->assertTrue($role->hasPermissionTo('new-permission', 'web'));
        $this->assertFalse($role->hasPermissionTo('old-permission', 'web'));
    }

    public function test_can_delete_role(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('roles.delete', 'web'));

        $role = Role::create(['name' => 'To Be Deleted', 'guard_name' => 'web']);

        $response = $this->actingAs($user, 'web')
            ->deleteJson(route('v1.auth.roles.destroy', $role));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['name' => 'To Be Deleted']);
    }

    public function test_cannot_delete_role_without_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Safe Role', 'guard_name' => 'web']);

        $response = $this->actingAs($user, 'web')
            ->deleteJson(route('v1.auth.roles.destroy', $role));

        $response->assertStatus(403);
    }
}
