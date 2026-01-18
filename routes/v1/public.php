<?php

declare(strict_types=1);

use App\Http\Controllers\Api\v1\PublicUserController;
use App\Http\Responses\ApiResponse;

/**
 * Health check.
 */
Route::get('/up', fn (): Illuminate\Http\JsonResponse => ApiResponse::success(['status' => 'up']))->name('v1.api.up');
Route::get('/public/users/{user}', PublicUserController::class)->name('v1.public.users.show');
