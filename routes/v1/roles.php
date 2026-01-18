<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Auth\PermissionController;
use App\Http\Controllers\v1\Auth\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(static function (): void {
    Route::get('permissions', [PermissionController::class, 'index'])->name('v1.auth.permissions.index');

    Route::get('roles', [RoleController::class, 'index'])->name('v1.auth.roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('v1.auth.roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('v1.auth.roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('v1.auth.roles.destroy');
});
