<?php

declare(strict_types=1);

namespace Tests\Feature\v1;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'm.samgan@mail.com')->first();
    }

    public function test_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson(route('v1.users.index'));

        $this->assertApiSuccess($response);
        $response->assertJsonCount(7, 'payload.data'); // 5 + 1 admin + 1 from DatabaseSeeder
    }

    public function test_can_filter_users_by_search(): void
    {
        User::factory()->create(['name' => 'UniqueName']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('v1.users.index', ['search' => 'UniqueName']));

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'payload.data')
            ->assertJsonPath('payload.data.0.name', 'UniqueName');
    }

    public function test_can_filter_users_by_role(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(RoleEnum::EMPLOYEE->value);

        $response = $this->actingAs($this->admin)
            ->getJson(route('v1.users.index', ['role' => RoleEnum::EMPLOYEE->value]));

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'payload.data')
            ->assertJsonPath('payload.data.0.role', RoleEnum::EMPLOYEE->value);
    }

    public function test_can_filter_users_by_active_status(): void
    {
        User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('v1.users.index', ['is_active' => false]));

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'payload.data')
            ->assertJsonPath('payload.data.0.is_active', false);
    }

    public function test_can_create_user_with_role(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleEnum::EMPLOYEE->value,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('v1.users.store'), $userData);

        $this->assertApiSuccess($response, __('User created successfully'), 201);
        $response->assertJsonPath('payload.name', 'John Doe')
            ->assertJsonPath('payload.email', 'john@example.com')
            ->assertJsonPath('payload.role', RoleEnum::EMPLOYEE->value);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole(RoleEnum::EMPLOYEE->value));
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson(route('v1.users.show', $user));

        $this->assertApiSuccess($response);
        $response->assertJsonPath('payload.email', $user->email);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::EMPLOYEE->value);

        $updateData = [
            'name' => 'Jane Doe',
            'role' => RoleEnum::SUPER_ADMIN->value,
        ];

        $response = $this->actingAs($this->admin)
            ->putJson(route('v1.users.update', $user), $updateData);

        $this->assertApiSuccess($response, __('User updated successfully'));
        $response->assertJsonPath('payload.name', 'Jane Doe')
            ->assertJsonPath('payload.role', RoleEnum::SUPER_ADMIN->value);

        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'name' => 'Jane Doe',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole(RoleEnum::SUPER_ADMIN->value));
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('v1.users.destroy', $user));

        $this->assertApiSuccess($response, __('User deleted successfully'));

        $this->assertDatabaseMissing('users', [
            'uuid' => $user->uuid,
        ]);
    }

    public function test_can_reset_user_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson(route('v1.users.reset-password', $user), [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $this->assertApiSuccess($response, __('Password reset successfully'));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_can_toggle_user_status_api(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('v1.common.toggle-active'), [
                'model' => 'user',
                'id' => $user->uuid,
            ]);

        $this->assertApiSuccess($response, 'User status toggled successfully');

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_can_toggle_user_status_command(): void
    {
        $user = User::factory()->create(['is_active' => true, 'email' => 'toggle@example.com']);

        Artisan::call('users:toggle-status', [
            'email' => 'toggle@example.com',
            '--active' => 'false',
        ]);

        $user->refresh();
        $this->assertFalse($user->is_active);

        Artisan::call('users:toggle-status', [
            'email' => 'toggle@example.com',
            '--active' => 'true',
        ]);

        $user->refresh();
        $this->assertTrue($user->is_active);

        Artisan::call('users:toggle-status', [
            'email' => 'toggle@example.com',
        ]);

        $user->refresh();
        $this->assertFalse($user->is_active);
    }
}
