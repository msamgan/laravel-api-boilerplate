<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class RateLimitingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_api_is_rate_limited(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/v1/up');
        }

        $this->getJson('/api/v1/up')->assertStatus(429);
    }
}
