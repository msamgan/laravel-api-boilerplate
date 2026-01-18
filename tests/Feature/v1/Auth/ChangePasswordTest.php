<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_their_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.auth.change-password'), [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.auth.change-password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_if_confirmation_does_not_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.auth.change-password'), [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_unauthenticated_user_cannot_access_change_password_endpoint(): void
    {
        $response = $this->postJson(route('v1.auth.change-password'), [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertStatus(401);
    }
}
