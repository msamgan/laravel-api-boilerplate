<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait InteractsWithApiResponse
{
    /**
     * Assert the response is a successful API response.
     */
    protected function assertApiSuccess(TestResponse $response, ?string $message = null, int $code = 200): void
    {
        $response->assertStatus($code)
            ->assertJson([
                'success' => true,
            ]);

        if ($message) {
            $response->assertJsonPath('message', $message);
        }
    }

    /**
     * Assert the response is a failed API response.
     */
    protected function assertApiFailure(TestResponse $response, ?string $message = null, int $code = 400): void
    {
        $response->assertStatus($code)
            ->assertJson([
                'success' => false,
            ]);

        if ($message) {
            $response->assertJsonPath('message', $message);
        }
    }
}
