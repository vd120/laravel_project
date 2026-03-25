<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\RealtimeService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated')
                ], 401);
            }

            // Get active conversation ID from request (when user is viewing a chat)
            $activeConversationId = $request->input('active_conversation_id');

            // Build query for notifications
            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(50);

            // If user is viewing a chat, exclude message notifications from that conversation
            if ($activeConversationId) {
                $query->where(function($q) use ($activeConversationId) {
                    $q->where('type', '!=', 'message')
                      ->orWhereRaw("JSON_EXTRACT(data, '$.conversation_id') != ?", [$activeConversationId]);
                });
            }

            $notifications = $query->get();

            // Build query for unread count (also exclude active conversation)
            $unreadQuery = Notification::where('user_id', $user->id)->whereNull('read_at');
            if ($activeConversationId) {
                $unreadQuery->where(function($q) use ($activeConversationId) {
                    $q->where('type', '!=', 'message')
                      ->orWhereRaw("JSON_EXTRACT(data, '$.conversation_id') != ?", [$activeConversationId]);
                });
            }
            $unreadCount = $unreadQuery->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications->map(function ($notification) {
                    try {

                        $triggerUser = null;
                        if ($notification->type === 'follow') {
                            $triggerUser = User::find($notification->data['follower_id'] ?? null);
                        } elseif ($notification->type === 'mention') {
                            $triggerUser = User::find($notification->data['mentioner_id'] ?? null);
                        }

                        // Generate link based on notification type
                        $link = null;
                        if ($notification->type === 'follow' && $triggerUser) {
                            $link = '/users/' . ($triggerUser->username ?? $triggerUser->id);
                        } elseif ($notification->type === 'like' && $notification->related_id) {
                            // Posts use slug, not ID
                            $post = \App\Models\Post::find($notification->related_id);
                            $link = $post ? '/posts/' . $post->slug : null;
                        } elseif ($notification->type === 'comment' && $notification->related_id) {
                            // For comment notifications, related_id is the Comment ID, so we need to get the Post from the Comment
                            $comment = \App\Models\Comment::find($notification->related_id);
                            $post = $comment ? $comment->post : null;
                            $link = $post ? '/posts/' . $post->slug : null;
                        } elseif ($notification->type === 'mention' && $notification->related_id) {
                            // For mention notifications, redirect to the post/comment where user was mentioned
                            if ($notification->related_type === 'App\\Models\\Post') {
                                $post = \App\Models\Post::find($notification->related_id);
                                $link = $post ? '/posts/' . $post->slug : null;
                            } elseif ($notification->related_type === 'App\\Models\\Comment') {
                                $comment = \App\Models\Comment::find($notification->related_id);
                                $post = $comment ? $comment->post : null;
                                $link = $post ? '/posts/' . $post->slug . '#comment-' . $comment->id : null;
                            } else {
                                // Fallback to mentioner's profile
                                $link = $triggerUser ? '/users/' . ($triggerUser->username ?? $triggerUser->id) : null;
                            }
                        } elseif ($notification->type === 'message' && ($notification->data['conversation_id'] ?? null)) {
                            // Conversations use slug, not ID
                            $conversation = \App\Models\Conversation::find($notification->data['conversation_id']);
                            $link = $conversation ? '/chat/' . $conversation->slug : null;
                        } elseif ($notification->type === 'group_invite' && ($notification->data['conversation_id'] ?? null)) {
                            // Group invite - redirect to chat conversation where invite was sent
                            $conversation = \App\Models\Conversation::find($notification->data['conversation_id']);
                            $link = $conversation ? '/chat/' . $conversation->slug : null;
                        }

                        return [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'message' => $notification->message,
                            'link' => $link,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                            'related_type' => $notification->related_type,
                            'related_id' => $notification->related_id,
                            'user' => $triggerUser ? [
                                'id' => $triggerUser->id,
                                'username' => $triggerUser->username,
                                'avatar' => $triggerUser->profile && $triggerUser->profile->avatar
                                    ? asset('storage/' . $triggerUser->profile->avatar)
                                    : null,
                            ] : null,
                        ];
                    } catch (\Exception $e) {

                        return [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'message' => $notification->message,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                            'related_type' => $notification->related_type,
                            'related_id' => $notification->related_id,
                            'user' => null,
                        ];
                    }
                }),
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated')
                ], 401);
            }

            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting unread count: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Notification $notification)
    {
        
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized')
            ], 403);
        }

        $notification->markAsRead();

        $unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0
        ]);
    }

    public function deleteAll(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated')
                ], 401);
            }

            
            $totalCount = Notification::where('user_id', $user->id)->count();

            
            $deletedCount = Notification::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.notifications_cleared'),
                'deleted_count' => $deletedCount,
                'total_count' => $totalCount,
                'unread_count' => 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Notification $notification)
    {
        
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized')
            ], 403);
        }

        $notification->delete();

        
        $unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Get real-time updates for the current user
     */
    public function getRealtimeUpdates(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated')
                ], 401);
            }

            $realtimeService = new RealtimeService();

            
            $postIds = $request->get('post_ids', []);

            $data = $realtimeService->getRealtimeData($user->id, $postIds);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->timestamp
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting real-time updates: ' . $e->getMessage()
            ], 500);
        }
    }
}
