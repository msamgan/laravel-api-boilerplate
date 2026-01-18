<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class UserResourcePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_returns_permissions_on_login(): void
    {
        $password = 'password';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $permission = Permission::findOrCreate('test-permission');
        $user->givePermissionTo($permission);

        $response = $this->postJson(route('v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('payload.user.permissions', ['test-permission']);
    }

    public function test_user_resource_does_not_return_permissions_on_me_endpoint(): void
    {
        $user = User::factory()->create();
        $permission = Permission::findOrCreate('test-permission');
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('v1.auth.me.index'));

        $response->assertStatus(200);

        $response->assertJsonMissingPath('payload.permissions');
    }

    public function test_user_resource_does_not_return_permissions_on_users_index(): void
    {
        $admin = User::factory()->create();
        Permission::findOrCreate('users.view');
        $admin->givePermissionTo('users.view');

        $user = User::factory()->create();
        $permission = Permission::findOrCreate('test-permission');
        $user->givePermissionTo($permission);

        Sanctum::actingAs($admin);

        $response = $this->getJson(route('v1.users.index'));

        $response->assertStatus(200);

        // Find the user in the collection and check for permissions
        $userData = collect($response->json('payload.data'))->firstWhere('id', $user->uuid);
        $this->assertArrayNotHasKey('permissions', $userData);
    }
}
