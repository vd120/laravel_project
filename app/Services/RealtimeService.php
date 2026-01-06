<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RealtimeService
{
    /**
     * Update cache for real-time data
     */
    public function updateCache(string $key, $data, int $ttl = 300): void
    {
        Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached data
     */
    public function getCache(string $key)
    {
        return Cache::get($key);
    }

    /**
     * Check if WebSocket is available
     */
    public function isRealtimeAvailable(): bool
    {
        return config('broadcasting.default') !== 'log' &&
               config('broadcasting.default') !== 'null';
    }

    /**
     * Get real-time configuration for frontend
     */
    public function getRealtimeConfig(): array
    {
        return [
            'enabled' => $this->isRealtimeAvailable(),
            'driver' => config('broadcasting.default'),
            'key' => config('broadcasting.connections.' . config('broadcasting.default') . '.key'),
            'host' => config('broadcasting.connections.' . config('broadcasting.default') . '.options.host'),
            'port' => config('broadcasting.connections.' . config('broadcasting.default') . '.options.port'),
            'scheme' => config('broadcasting.connections.' . config('broadcasting.default') . '.options.scheme'),
        ];
    }

    /**
     * Generate cache key for user-specific data
     */
    public function getUserCacheKey(int $userId, string $type): string
    {
        return "user:{$userId}:{$type}";
    }

    /**
     * Generate cache key for post-specific data
     */
    public function getPostCacheKey(int $postId, string $type): string
    {
        return "post:{$postId}:{$type}";
    }

    /**
     * Update user notification count cache
     */
    public function updateUserNotificationCount(int $userId): int
    {
        $count = \App\Models\Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        $this->updateCache($this->getUserCacheKey($userId, 'notifications'), $count, 3600);
        return $count;
    }

    /**
     * Update post data cache
     */
    public function updatePostData(int $postId): array
    {
        $post = \App\Models\Post::with(['user', 'likes', 'comments.user'])->find($postId);

        if (!$post) {
            return [];
        }

        $data = [
            'likes_count' => $post->likes->count(),
            'comments_count' => $post->comments->count(),
            'latest_comments' => $post->comments()
                ->with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => app(MentionService::class)->convertMentionsToLinks($comment->content),
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                            'avatar' => $comment->user->profile && $comment->user->profile->avatar
                                ? asset('storage/' . $comment->user->profile->avatar)
                                : null,
                        ],
                        'created_at' => $comment->created_at->diffForHumans(),
                        'likes_count' => $comment->likes->count(),
                    ];
                }),
        ];

        $this->updateCache($this->getPostCacheKey($postId, 'data'), $data, 1800);
        return $data;
    }

    /**
     * Get optimized data for real-time updates
     */
    public function getRealtimeData(int $userId, array $postIds = []): array
    {
        $data = [
            'notifications' => $this->getCache($this->getUserCacheKey($userId, 'notifications')) ?? 0,
            'posts' => [],
        ];

        foreach ($postIds as $postId) {
            $postData = $this->getCache($this->getPostCacheKey($postId, 'data'));
            if ($postData) {
                $data['posts'][$postId] = $postData;
            }
        }

        return $data;
    }

    /**
     * Log real-time activity for debugging
     */
    public function logRealtimeActivity(string $action, array $data = []): void
    {
        Log::info('Realtime Activity: ' . $action, $data);
    }

    /**
     * Clean up old cache entries
     */
    public function cleanupCache(): void
    {
        
        Cache::flush();
        Log::info('Realtime cache cleaned up');
    }
}
