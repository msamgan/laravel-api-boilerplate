<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response_and_shows_app_name(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertSee(config('app.name'));
    }
}
