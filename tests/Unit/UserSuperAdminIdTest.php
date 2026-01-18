<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

final class UserSuperAdminIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SpatieRole::findOrCreate(Role::SUPER_ADMIN->value);
    }

    public function test_super_admin_returns_own_id(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::SUPER_ADMIN->value);

        $this->assertEquals($user->id, $user->getSuperAdminId());
    }

    public function test_staff_returns_super_admin_creator_id(): void
    {
        $super_admin = User::factory()->create();
        $super_admin->assignRole(Role::SUPER_ADMIN->value);

        $staff = User::factory()->create(['created_by' => $super_admin->id]);

        $this->assertEquals($super_admin->id, $staff->getSuperAdminId());
    }

    public function test_nested_staff_returns_top_level_super_admin_id(): void
    {
        $super_admin = User::factory()->create();
        $super_admin->assignRole(Role::SUPER_ADMIN->value);

        $manager = User::factory()->create(['created_by' => $super_admin->id]);
        $staff = User::factory()->create(['created_by' => $manager->id]);

        $this->assertEquals($super_admin->id, $staff->getSuperAdminId());
    }

    public function test_returns_created_by_if_no_super_admin_found_in_chain(): void
    {
        $root = User::factory()->create(['created_by' => null]);
        $manager = User::factory()->create(['created_by' => $root->id]);
        $staff = User::factory()->create(['created_by' => $manager->id]);

        $this->assertEquals($manager->id, $staff->getSuperAdminId());
    }

    public function test_returns_self_id_if_no_super_admin_and_no_creator(): void
    {
        $user = User::factory()->create(['created_by' => null]);

        $this->assertEquals($user->id, $user->getSuperAdminId());
    }

    public function test_staff_returns_explicit_super_admin_id(): void
    {
        $super_admin = User::factory()->create();
        $staff = User::factory()->create(['super_admin_id' => $super_admin->id]);

        $this->assertEquals($super_admin->id, $staff->getSuperAdminId());
    }
}
