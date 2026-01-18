<?php

declare(strict_types=1);

// create a prefix for api routes with versioning v1

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    include __DIR__ . '/v1/public.php';
    include __DIR__ . '/v1/auth.php';
    include __DIR__ . '/v1/common.php';
    include __DIR__ . '/v1/media.php';
    include __DIR__ . '/v1/roles.php';
    include __DIR__ . '/v1/users.php';
    include __DIR__ . '/v1/notifications.php';
});
