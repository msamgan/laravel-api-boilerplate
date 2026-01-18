<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RoleListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing roles and permissions.
     */
    public function test_can_list_roles_and_permissions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('roles.view'));

        $role = Role::create(['name' => 'Editor']);
        $permission = Permission::findOrCreate('edit-posts');
        $role->givePermissionTo($permission);

        $response = $this->actingAs($user)
            ->getJson(route('v1.auth.roles.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'payload' => [
                    '*' => [
                        'id',
                        'name',
                        'permissions' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Editor',
            ])
            ->assertJsonFragment([
                'name' => 'edit-posts',
            ]);
    }

    /**
     * Test listing roles requires roles.view permission.
     */
    public function test_listing_roles_requires_roles_view_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('v1.auth.roles.index'));

        $response->assertStatus(403);
    }

    /**
     * Test listing roles requires authentication.
     */
    public function test_listing_roles_requires_authentication(): void
    {
        $response = $this->getJson(route('v1.auth.roles.index'));

        $response->assertStatus(401);
    }
}
