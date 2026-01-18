<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    /**
     * List all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            NotificationResource::collection($notifications)
        );
    }

    /**
     * Mark a specific notification as read.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return $this->successResponse(
            new NotificationResource($notification),
            __('Notification marked as read')
        );
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return $this->successResponse(
            null,
            __('All notifications marked as read')
        );
    }

    /**
     * Get the count of unread notifications for the authenticated user.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return $this->successResponse([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
