<?php

declare(strict_types=1);

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

final class ArchitectureTest extends TestCase
{
    /**
     * Ensure that request() and auth() helpers and facades are not used outside the Http layer.
     */
    public function test_request_and_auth_not_used_in_actions_and_models(): void
    {
        $forbidden = [
            'request(',
            'auth(',
            'Request::',
            'Auth::',
            'facades\request',
            'facades\auth',
        ];

        $directories = [
            app_path('Actions'),
            app_path('Models'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());

                foreach ($forbidden as $pattern) {
                    $this->assertStringNotContainsString(
                        $pattern,
                        $content,
                        "Forbidden usage of '{$pattern}' found in {$file->getPathname()}. Please pass the required data as parameters."
                    );
                }
            }
        }
    }
}
