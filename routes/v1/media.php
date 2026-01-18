<?php

declare(strict_types=1);

use App\Http\Controllers\v1\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('media')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [MediaController::class, 'index'])->name('v1.media.index');
    Route::post('/', [MediaController::class, 'store'])->name('v1.media.store');
    Route::delete('/{media}', [MediaController::class, 'destroy'])->name('v1.media.destroy');
});
