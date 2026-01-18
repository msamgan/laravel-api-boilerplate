<?php

declare(strict_types=1);

namespace Tests\Feature\v1;

use App\Models\User;
use App\Notifications\EntityChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_can_list_notifications_with_pagination(): void
    {
        // Create some notifications for the user
        for ($i = 0; $i < 20; $i++) {
            $this->user->notify(new EntityChangedNotification($this->user, 'created', 'Test System'));
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.notifications.index', ['per_page' => 10]));

        $response->assertStatus(200)
            ->assertJsonCount(10, 'payload.data')
            ->assertJsonStructure([
                'payload' => [
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'data',
                            'read_at',
                            'formatted_read_at',
                            'created_at',
                            'formatted_created_at',
                        ],
                    ],
                    'links',
                    'meta',
                ],
            ]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $this->user->notify(new EntityChangedNotification($this->user, 'created', 'Test System'));
        $notification = $this->user->unreadNotifications->first();

        $response = $this->actingAs($this->user)
            ->patchJson(route('v1.notifications.mark-as-read', ['id' => $notification->id]));

        $response->assertStatus(200)
            ->assertJsonPath('payload.read_at', fn ($readAt): bool => ! is_null($readAt));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new EntityChangedNotification($this->user, 'created', 'Test System'));
        }

        $this->assertEquals(5, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->postJson(route('v1.notifications.mark-all-as-read'));

        $response->assertStatus(200);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_cannot_mark_other_users_notification_as_read(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->notify(new EntityChangedNotification($otherUser, 'created', 'Test System'));
        $notification = $otherUser->unreadNotifications->first();

        $response = $this->actingAs($this->user)
            ->patchJson(route('v1.notifications.mark-as-read', ['id' => $notification->id]));

        $response->assertStatus(404);
    }

    public function test_can_get_unread_notifications_count(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new EntityChangedNotification($this->user, 'created', 'Test System'));
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('v1.notifications.unread-count'));

        $response->assertStatus(200)
            ->assertJsonPath('payload.unread_count', 3);
    }
}
