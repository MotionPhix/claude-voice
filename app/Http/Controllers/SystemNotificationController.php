<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemNotificationController extends Controller
{
    public function __construct(
        private readonly SystemNotificationService $notificationService
    ) {}

    /**
     * Get notifications for the current user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get user-specific notifications
        $userNotifications = $this->notificationService->getUserNotifications($user);
        
        // Get system-wide notifications  
        $systemNotifications = $this->notificationService->getSystemNotifications();

        // Combine and sort by created_at desc
        $allNotifications = $userNotifications->merge($systemNotifications)
            ->sortByDesc('created_at')
            ->values();

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => $allNotifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'level' => $notification->level,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'icon' => $notification->icon,
                    'level_color' => $notification->level_color,
                    'notifiable_type' => $notification->notifiable_type,
                    'notifiable_id' => $notification->notifiable_id,
                    'is_read' => $notification->is_read,
                    'is_dismissed' => $notification->is_dismissed,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => $notification->read_at?->toISOString(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count only
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(SystemNotification $notification): JsonResponse
    {
        $user = Auth::user();
        
        // Ensure the user can access this notification
        if ($notification->user_id && $notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Dismiss a notification
     */
    public function dismiss(SystemNotification $notification): JsonResponse
    {
        $user = Auth::user();
        
        // Ensure the user can access this notification
        if ($notification->user_id && $notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->dismiss();

        return response()->json([
            'success' => true,
            'message' => 'Notification dismissed',
        ]);
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $updatedCount = $this->notificationService->markAllAsReadForUser($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updatedCount} notifications as read",
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Get notifications for a specific type
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $user = Auth::user();
        
        $notifications = SystemNotification::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereNull('user_id'); // System notifications
            })
            ->ofType($type)
            ->undismissed()
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Delete a notification (admin only or own notifications)
     */
    public function destroy(SystemNotification $notification): JsonResponse
    {
        $user = Auth::user();
        
        // Ensure the user can delete this notification
        if ($notification->user_id && $notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
