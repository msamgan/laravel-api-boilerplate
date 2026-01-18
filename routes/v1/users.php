<?php

declare(strict_types=1);

use App\Http\Controllers\Api\v1\Users\ResetPasswordController;
use App\Http\Controllers\Api\v1\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('users')->as('v1.')->group(function (): void {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
    Route::post('/', [UsersController::class, 'store'])->name('users.store');
    Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::put('/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::put('/{user}/reset-password', ResetPasswordController::class)->name('users.reset-password');
});
