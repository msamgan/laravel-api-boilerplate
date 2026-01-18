<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_email_verification_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('v1.auth.verification.send'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification link sent',
            ]);
    }

    public function test_user_cannot_request_link_if_already_verified(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('v1.auth.verification.send'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified',
            ]);
    }

    public function test_user_can_verify_email(): void
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'v1.auth.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)
            ->getJson($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully',
            ]);
    }

    public function test_user_cannot_verify_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'v1.auth.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->actingAs($user)
            ->getJson($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertStatus(403);
    }
}
