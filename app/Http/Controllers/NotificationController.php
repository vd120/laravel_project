<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(): JsonResponse
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->notifications()->unread()->count()
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        $count = Auth::user()->notifications()->unread()->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Create a notification for a user
     */
    public static function createNotification($userId, $type, $data, $relatedModel = null): Notification
    {
        $notificationData = [
            'user_id' => $userId,
            'type' => $type,
            'data' => $data
        ];

        if ($relatedModel) {
            $notificationData['related_id'] = $relatedModel->id;
            $notificationData['related_type'] = get_class($relatedModel);
        }

        return Notification::create($notificationData);
    }

    /**
     * Create message notification
     */
    public static function createMessageNotification($recipientId, $sender, $message): Notification
    {
        return self::createNotification(
            $recipientId,
            'message',
            [
                'sender_name' => $sender->name ?? 'Unknown',
                'sender_id' => $sender->id,
                'message_preview' => substr($message->content ?? '', 0, 50) . (strlen($message->content ?? '') > 50 ? '...' : ''),
                'conversation_id' => $message->conversation_id ?? null
            ],
            $message
        );
    }
}
