<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $password = 'password';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson(route('v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
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
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(route('v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson(route('v1.auth.login'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $password = 'password';
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'is_active' => false,
        ]);

        $response = $this->postJson(route('v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
