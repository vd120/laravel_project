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
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

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

                        return [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'message' => $notification->message,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                            'related_type' => $notification->related_type,
                            'related_id' => $notification->related_id,
                            'user' => $triggerUser ? [
                                'id' => $triggerUser->id,
                                'name' => $triggerUser->name,
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
                    'message' => 'User not authenticated'
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
                'message' => 'Unauthorized'
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
                    'message' => 'User not authenticated'
                ], 401);
            }

            
            $totalCount = Notification::where('user_id', $user->id)->count();

            
            $deletedCount = Notification::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications deleted successfully',
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
                'message' => 'Unauthorized'
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
                    'message' => 'User not authenticated'
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
