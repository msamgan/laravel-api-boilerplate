<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UserMediaStoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'media.create',
            'media.view',
            'media.delete',
        ]);
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
    }

    public function test_can_upload_media_with_user_id_creates_user_media_entry(): void
    {
        Storage::fake('public');
        $client = User::factory()->create();
        $file = UploadedFile::fake()->image('client-media.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
                'user_id' => $client->id,
            ]);

        $response->assertStatus(201);

        $mediaId = $response->json('payload.id');

        $this->assertDatabaseHas('user_media', [
            'user_id' => $client->id,
            'media_id' => $mediaId,
            'created_by' => $this->user->id,
            'super_admin_id' => $this->user->id,
        ]);
    }

    public function test_can_upload_media_without_user_id_does_not_create_user_media_entry(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('regular-media.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201);

        $mediaId = $response->json('payload.id');

        $this->assertDatabaseMissing('user_media', [
            'media_id' => $mediaId,
        ]);
    }

    public function test_deleting_media_automatically_removes_user_media_entry(): void
    {
        Storage::fake('public');
        $client = User::factory()->create();
        $file = UploadedFile::fake()->image('to-be-deleted.jpg');

        // First upload it
        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
                'user_id' => $client->id,
            ]);

        $response->assertStatus(201);
        $mediaId = $response->json('payload.id');
        $media = Media::query()->findOrFail($mediaId);

        $this->assertDatabaseHas('user_media', [
            'user_id' => $client->id,
            'media_id' => $mediaId,
        ]);

        // Then delete it
        $this->actingAs($this->user)
            ->deleteJson(route('v1.media.destroy', ['media' => $media->id]))
            ->assertOk();

        $this->assertDatabaseMissing('media', [
            'id' => $mediaId,
        ]);

        $this->assertDatabaseMissing('user_media', [
            'media_id' => $mediaId,
        ]);
    }
}
