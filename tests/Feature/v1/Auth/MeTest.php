<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_their_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('v1.auth.me.index'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'payload' => [
                    'id' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson(route('v1.auth.me.index'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_their_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.auth.me.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User information updated successfully',
                'payload' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_update_email_to_existing_one(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        User::factory()->create(['email' => 'user2@example.com']);

        Sanctum::actingAs($user1);

        $response = $this->postJson(route('v1.auth.me.update'), [
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_update_info_without_changing_email(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('v1.auth.me.update'), [
            'name' => 'New Name',
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('payload.name', 'New Name');
    }

    public function test_authenticated_user_can_update_their_profile_picture(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(route('v1.auth.me.update'), [
            'profile_picture' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User information updated successfully',
            ]);

        $user->refresh();

        $this->assertNotNull($user->profile_picture_id);
        $this->assertDatabaseHas('media', [
            'id' => $user->profile_picture_id,
            'file_name' => 'avatar.jpg',
        ]);

        Storage::disk('public')->assertExists('media/avatar.jpg');

        $response->assertJsonPath('payload.profile_picture.file_name', 'avatar.jpg');
        $this->assertNotEmpty($response->json('payload.profile_picture.url'));
    }

    public function test_authenticated_user_can_remove_their_profile_picture(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson(route('v1.auth.me.update'), [
            'profile_picture' => $file,
        ]);

        $user->refresh();
        $this->assertNotNull($user->profile_picture_id);

        $response = $this->postJson(route('v1.auth.me.update'), [
            'remove_profile_picture' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User information updated successfully',
            ]);

        $user->refresh();
        $this->assertNull($user->profile_picture_id);
    }
}
