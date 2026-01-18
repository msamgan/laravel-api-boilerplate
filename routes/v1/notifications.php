<?php

declare(strict_types=1);

use App\Http\Controllers\v1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('notifications')->group(static function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('v1.notifications.index');
    Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('v1.notifications.unread-count');
    Route::patch('{id}/mark-as-read', [NotificationController::class, 'update'])->name('v1.notifications.mark-as-read');
    Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('v1.notifications.mark-all-as-read');
});
