<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\CreateUserAction;
use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateUserAction::class);
    }

    public function test_it_can_create_a_user_with_a_role(): void
    {
        // First, create the role since it's required for assignment
        RoleFactory::new()->create(['name' => 'Admin']);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'Admin',
        ];

        $user = $this->action->handle($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_it_throws_exception_if_role_does_not_exist(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'NonExistentRole',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role [NonExistentRole] does not exist.');

        $this->action->handle($data);
    }
}
