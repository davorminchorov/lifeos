<?php

namespace App\Subscriptions\UI\Api;

use App\Models\SubscriptionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get all subscription notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubscriptionNotification::query();

        // Filter by read status if provided
        if ($request->has('read')) {
            $query->where('read', $request->boolean('read'));
        }

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // Get notifications with pagination
        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = SubscriptionNotification::findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        DB::table('subscription_notifications')
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = SubscriptionNotification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Get the count of unread notifications.
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = SubscriptionNotification::where('read', false)->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
