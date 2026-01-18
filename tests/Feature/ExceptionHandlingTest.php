<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ExceptionHandlingTest extends TestCase
{
    public function test_api_not_found_exception_returns_json(): void
    {
        $response = $this->getJson('/api/v1/non-existent-route');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }

    public function test_api_validation_exception_returns_json(): void
    {
        Route::post('/api/test-validation', function (): void {
            request()->validate([
                'name' => 'required',
            ]);
        });

        $response = $this->postJson('/api/test-validation', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid.',
            ]);
    }

    public function test_api_authentication_exception_returns_json(): void
    {
        Route::get('/api/test-auth', function (): never {
            throw new \Illuminate\Auth\AuthenticationException();
        });

        $response = $this->getJson('/api/test-auth');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
            ]);
    }
}
