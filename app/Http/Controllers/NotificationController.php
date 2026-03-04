<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the notifications page for the authenticated user
     */
    public function index()
    {
        return view('notifications.index');
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
        // Generate appropriate preview text based on message type
        $messagePreview = '';
        $messageType = $message->type ?? 'text';
        
        if ($messageType === 'text' || empty($messageType)) {
            $messagePreview = substr($message->content ?? '', 0, 35) . (strlen($message->content ?? '') > 35 ? '...' : '');
        } else {
            // For media messages, show type instead of content
            $messagePreview = self::getMessageTypeText($messageType);
        }
        
        return self::createNotification(
            $recipientId,
            'message',
            [
                'sender_name' => $sender->name ?? $sender->username ?? 'Someone',
                'sender_username' => $sender->username ?? 'Unknown',
                'sender_id' => $sender->id,
                'message_preview' => $messagePreview,
                'message_type' => $messageType,
                'conversation_id' => $message->conversation_id ?? null
            ],
            $message
        );
    }
    
    /**
     * Get human-readable text for message type
     */
    private static function getMessageTypeText($type): string
    {
        return match($type) {
            'image' => 'sent an image',
            'video' => 'sent a video',
            'audio' => 'sent an audio',
            'document' => 'sent a document',
            'gif' => 'sent a GIF',
            'sticker' => 'sent a sticker',
            'story_reply' => 'replied to your story',
            'group_invite' => 'sent a group invite',
            'system' => 'system message',
            default => 'sent a message'
        };
    }
}
