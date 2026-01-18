<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaTest extends TestCase
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

        Storage::fake(config('filesystems.default'));
    }

    public function test_can_upload_image(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'payload' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'creator',
                    'super_admin',
                    'created_at',
                    'formatted_created_at',
                ],
            ]);

        $this->assertDatabaseHas('media', [
            'file_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'created_by' => $this->user->id,
            'super_admin_id' => $this->user->id,
            'disk' => config('filesystems.default'),
        ]);

        Storage::disk(config('filesystems.default'))->assertExists('media/test-image.jpg');
    }

    public function test_can_upload_image_as_super_admin_sets_created_by_to_auth_id(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('payload.super_admin.id', $this->user->uuid);

        $this->assertDatabaseHas('media', [
            'file_name' => 'test-image.jpg',
            'created_by' => $this->user->id,
            'super_admin_id' => $this->user->id,
            'disk' => config('filesystems.default'),
        ]);
    }

    public function test_can_upload_image_as_non_super_admin_sets_created_by_to_auth_id(): void
    {
        $this->user->givePermissionTo('media.create');

        $creator = User::factory()->create();
        $creator->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
        $this->user->update(['created_by' => $creator->id]);

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $this->assertEquals($creator->uuid, $response->json('payload.super_admin.id'));

        $this->assertDatabaseHas('media', [
            'file_name' => 'test-image.jpg',
            'created_by' => $this->user->id,
            'super_admin_id' => $creator->id,
            'disk' => config('filesystems.default'),
        ]);
    }

    public function test_can_upload_image_with_recursive_super_admin_lookup(): void
    {
        $this->user->givePermissionTo('media.create');

        // Super Admin -> Manager -> Staff (user)
        $super_admin = User::factory()->create();
        $super_admin->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $manager = User::factory()->create(['created_by' => $super_admin->id]);
        $this->user->update(['created_by' => $manager->id]);

        $file = UploadedFile::fake()->image('recursive-test.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $this->assertEquals($super_admin->uuid, $response->json('payload.super_admin.id'));

        $this->assertDatabaseHas('media', [
            'file_name' => 'recursive-test.jpg',
            'created_by' => $this->user->id,
            'super_admin_id' => $super_admin->id,
            'disk' => config('filesystems.default'),
        ]);
    }

    public function test_can_upload_pdf(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $file = UploadedFile::fake()->create('test-document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('media', [
            'file_name' => 'test-document.pdf',
            'mime_type' => 'application/pdf',
            'disk' => config('filesystems.default'),
        ]);

        Storage::disk(config('filesystems.default'))->assertExists('media/test-document.pdf');
    }

    public function test_cannot_upload_file_larger_than_10mb(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $file = UploadedFile::fake()->create('large-file.jpg', 11000); // ~11MB

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_cannot_upload_invalid_file_type(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_can_list_media_as_super_admin_only_sees_own_media(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
        $otherUser = User::factory()->create();

        Media::factory()->count(3)->create(['super_admin_id' => $this->user->id]);
        Media::factory()->count(2)->create(['super_admin_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index'));

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('payload.data'));
    }

    public function test_can_list_media_as_non_super_admin_sees_media_of_creator(): void
    {
        $creator = User::factory()->create();
        $creator->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
        $this->user->update(['created_by' => $creator->id]);
        $this->user->givePermissionTo('media.view');

        Media::factory()->count(3)->create(['super_admin_id' => $creator->id]);
        Media::factory()->count(2)->create(['super_admin_id' => $this->user->id]);
        Media::factory()->count(1)->create(['super_admin_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index'));

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('payload.data'));
    }

    public function test_can_list_media_ordered_by_latest(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
        Media::query()->delete();

        $media1 = Media::factory()->create(['super_admin_id' => $this->user->id, 'created_at' => now()->subMinutes(10)]);
        $media2 = Media::factory()->create(['super_admin_id' => $this->user->id, 'created_at' => now()->subMinutes(5)]);
        $media3 = Media::factory()->create(['super_admin_id' => $this->user->id, 'created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index'));

        $response->assertStatus(200);

        $responseData = $response->json('payload.data');
        $this->assertEquals($media3->id, $responseData[0]['id']);
        $this->assertEquals($media2->id, $responseData[1]['id']);
        $this->assertEquals($media1->id, $responseData[2]['id']);
    }

    public function test_can_search_media_by_name(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        Media::factory()->create(['super_admin_id' => $this->user->id, 'name' => 'Specific Image']);
        Media::factory()->create(['super_admin_id' => $this->user->id, 'name' => 'Another File']);

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index', ['search' => 'Specific']));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('payload.data'));
        $this->assertEquals('Specific Image', $response->json('payload.data.0.name'));
    }

    public function test_can_filter_media_by_type(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        Media::factory()->create([
            'super_admin_id' => $this->user->id,
            'mime_type' => 'image/jpeg',
        ]);
        Media::factory()->create([
            'super_admin_id' => $this->user->id,
            'mime_type' => 'application/pdf',
        ]);

        // Filter by image
        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index', ['type' => 'image']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('payload.data'));
        $this->assertStringStartsWith('image/', $response->json('payload.data.0.mime_type'));

        // Filter by document
        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index', ['type' => 'document']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('payload.data'));
        $this->assertEquals('application/pdf', $response->json('payload.data.0.mime_type'));
    }

    public function test_can_filter_media_by_user_id(): void
    {
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $user1 = User::factory()->create(['super_admin_id' => $this->user->id]);
        $user2 = User::factory()->create(['super_admin_id' => $this->user->id]);

        $media1 = Media::factory()->create([
            'super_admin_id' => $this->user->id,
        ]);
        $media2 = Media::factory()->create([
            'super_admin_id' => $this->user->id,
        ]);

        \App\Models\UserMedia::query()->create([
            'user_id' => $user1->id,
            'media_id' => $media1->id,
            'super_admin_id' => $this->user->id,
        ]);

        \App\Models\UserMedia::query()->create([
            'user_id' => $user2->id,
            'media_id' => $media2->id,
            'super_admin_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.media.index', ['user_id' => $user1->uuid]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('payload.data'));
        $this->assertEquals($media1->id, $response->json('payload.data.0.id'));
    }

    public function test_client_can_only_see_their_own_media(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(\App\Enums\Role::SUPER_ADMIN->value);

        $client = User::factory()->create(['super_admin_id' => $superAdmin->id]);
        $client->assignRole(\App\Enums\Role::CLIENT->value);
        // Permission is granted via RoleAndPermissionSeeder in real app, but in tests we need to be explicit if not seeded
        $client->givePermissionTo('media.view');

        // Media owned by client
        $media = Media::factory()->count(2)->create([
            'super_admin_id' => $superAdmin->id,
        ]);
        foreach ($media as $m) {
            \App\Models\UserMedia::query()->create([
                'user_id' => $client->id,
                'media_id' => $m->id,
                'super_admin_id' => $superAdmin->id,
            ]);
        }

        // Media owned by someone else but same super admin
        Media::factory()->count(3)->create([
            'super_admin_id' => $superAdmin->id,
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('v1.media.index'));

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('payload.data'));
    }

    public function test_can_delete_media(): void
    {
        $this->user->givePermissionTo('media.delete');
        $this->user->assignRole(\App\Enums\Role::SUPER_ADMIN->value);
        $disk = config('filesystems.default');
        $media = Media::factory()->create([
            'path' => 'media/test-file.jpg',
            'disk' => $disk,
            'super_admin_id' => $this->user->id,
        ]);

        Storage::disk($disk)->put('media/test-file.jpg', 'content');

        $response = $this->actingAs($this->user)
            ->deleteJson(route('v1.media.destroy', ['media' => $media->id]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Media deleted successfully',
            ]);

        $this->assertDatabaseMissing('media', [
            'id' => $media->id,
        ]);

        Storage::disk($disk)->assertMissing('media/test-file.jpg');
    }
}
