<?php

declare(strict_types=1);

namespace Tests\Feature\v1\Common;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ToggleActiveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::SUPER_ADMIN->value);
    }

    public function test_fails_if_model_not_supported(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('v1.common.toggle-active'), [
                'model' => 'invalid-model',
                'id' => 'some-id',
            ])
            ->assertStatus(422);
    }
}
