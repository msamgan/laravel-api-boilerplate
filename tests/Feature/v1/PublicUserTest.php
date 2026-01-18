<?php

declare(strict_types=1);

namespace Tests\Feature\v1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_public_user_info(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'John Doe',
        ]);

        $response = $this->getJson(route('v1.public.users.show', $user));

        $response->assertOk()
            ->assertJson([
                'payload' => [
                    'id' => $user->uuid,
                    'name' => 'John Doe',
                ],
                'message' => 'User information retrieved successfully.',
                'success' => true,
            ])
            ->assertJsonMissing([
                'email' => $user->email,
            ]);
    }

    public function test_returns_404_if_user_not_found(): void
    {
        $response = $this->getJson('/api/v1/public/users/non-existent-uuid');

        $response->assertNotFound();
    }
}
