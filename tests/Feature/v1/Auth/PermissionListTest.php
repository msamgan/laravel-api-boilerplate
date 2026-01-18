<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class PermissionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_permissions_if_authorized(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('permissions.view', 'web'));
        Permission::findOrCreate('test-permission-1', 'web');
        Permission::findOrCreate('test-permission-2', 'web');

        $response = $this->actingAs($user, 'web')
            ->getJson(route('v1.auth.permissions.index'));

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'test-permission-1'])
            ->assertJsonFragment(['name' => 'test-permission-2'])
            ->assertJsonFragment(['name' => 'roles.create']);
    }

    public function test_cannot_list_permissions_if_not_authorized(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->getJson(route('v1.auth.permissions.index'));

        $response->assertStatus(403);
    }
}
