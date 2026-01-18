<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Common\StatusController;
use App\Http\Controllers\v1\Common\ToggleActiveController;
use Illuminate\Support\Facades\Route;

Route::post('toggle-active', ToggleActiveController::class)
    ->name('v1.common.toggle-active')
    ->middleware('auth:sanctum');

Route::get('statuses/{model}', StatusController::class)
    ->name('v1.common.statuses')
    ->middleware('auth:sanctum');
