<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

final class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_activity_is_logged(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $user->update([
            'name' => 'New Name',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'updated',
        ]);

        $activity = Activity::all()->last();

        $this->assertEquals('Old Name', $activity->properties['old']['name']);
        $this->assertEquals('New Name', $activity->properties['attributes']['name']);
    }

    public function test_pruning_configuration(): void
    {
        $this->assertEquals(180, config('activitylog.delete_records_older_than_days'));
    }
}
