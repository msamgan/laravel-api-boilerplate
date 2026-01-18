<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        Event::fake();

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('v1.auth.register'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'payload' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                    ],
                ],
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole(\App\Enums\Role::EMPLOYEE->value));

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_requires_name_email_and_password(): void
    {
        $response = $this->postJson(route('v1.auth.register'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_fails_if_email_is_already_taken(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('v1.auth.register'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_if_password_is_not_confirmed(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->postJson(route('v1.auth.register'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
