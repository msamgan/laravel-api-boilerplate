<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ApiResponseTest extends TestCase
{
    public function test_api_up_endpoint_returns_unified_response(): void
    {
        $response = $this->getJson(route('v1.api.up'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Operation successful.',
                'payload' => [
                    'status' => 'up',
                ],
            ]);
    }
}
