<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_email_can_be_sent(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->postJson(route('v1.auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'We have emailed your password reset link.',
            ]);

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson(route('v1.auth.forgot-password'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_fails_for_non_existent_user(): void
    {
        $response = $this->postJson(route('v1.auth.forgot-password'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
