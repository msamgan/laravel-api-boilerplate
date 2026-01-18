<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class GenerateApiJsonCommandTest extends TestCase
{
    /**
     * Test the app:generate-api-json command.
     */
    public function test_it_generates_api_details_json_file(): void
    {
        $filePath = public_path('api-docs/data.json');

        // Ensure the file does not exist before running the command
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $this->artisan('app:generate-api-documentation')
            ->assertExitCode(0)
            ->expectsOutput("API details have been saved to {$filePath}");

        $this->assertTrue(File::exists($filePath));

        $content = json_decode(File::get($filePath), true);

        $this->assertIsArray($content);
        $this->assertArrayHasKey('info', $content);
        $this->assertArrayHasKey('baseUrl', $content['info']);
        $this->assertArrayHasKey('endpoints', $content);
        // The endpoints are keyed by route name or URI.
        // Let's check for a known route name like 'v1.api.up' or URI 'api/v1/up'
    }

    /**
     * Test the app:generate-api-documentation command with --UI option.
     */
    public function test_it_generates_api_details_json_and_ui_file(): void
    {
        $jsonPath = public_path('api-docs/data.json');
        $uiPath = public_path('api-docs/index.html');

        // Ensure the files do not exist before running the command
        if (File::exists($jsonPath)) {
            File::delete($jsonPath);
        }
        if (File::exists($uiPath)) {
            File::delete($uiPath);
        }

        $this->artisan('app:generate-api-documentation', ['--UI' => true])
            ->assertExitCode(0)
            ->expectsOutput("API details have been saved to {$jsonPath}")
            ->expectsOutput("API documentation UI has been generated at {$uiPath}");

        $this->assertTrue(File::exists($jsonPath));
        $this->assertTrue(File::exists($uiPath));

        $this->assertStringContainsString('Nexora API Documentation', File::get($uiPath));
        $this->assertStringContainsString('fetch(\'data.json\')', File::get($uiPath));
        $this->assertStringContainsString('id="base-url"', File::get($uiPath));
    }
}
