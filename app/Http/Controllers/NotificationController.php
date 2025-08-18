<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the user's notifications.
     */
    public function index(): View
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications data for AJAX requests.
     */
    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->when($request->boolean('unread_only'), function ($query) {
                return $query->whereNull('read_at');
            })
            ->limit($request->input('limit', 10))
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'general',
                    'action_url' => $notification->data['action_url'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Display notification preferences.
     */
    public function preferences(): View
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences()
            ->get()
            ->keyBy('notification_type');

        $defaultPreferences = UserNotificationPreference::getDefaultPreferences();

        // Ensure all notification types have entries
        foreach ($defaultPreferences as $type => $default) {
            if (! isset($preferences[$type])) {
                $preferences[$type] = new UserNotificationPreference([
                    'user_id' => $user->id,
                    'notification_type' => $type,
                    ...$default,
                ]);
            }
        }

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = Auth::user();
        $preferences = $request->input('preferences', []);

        foreach ($preferences as $type => $settings) {
            $user->notificationPreferences()->updateOrCreate(
                ['notification_type' => $type],
                [
                    'email_enabled' => $settings['email_enabled'] ?? false,
                    'database_enabled' => $settings['database_enabled'] ?? false,
                    'push_enabled' => $settings['push_enabled'] ?? false,
                    'settings' => [
                        'days_before' => $settings['days_before'] ?? [],
                    ],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
        ]);
    }

    /**
     * Get notification statistics for the user.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'by_type' => $user->notifications()
                ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) as type, COUNT(*) as count")
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return response()->json($stats);
    }
}
