<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
        'related_id',
        'related_type'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            // Fire the real-time notification event
            broadcast(new \App\Events\NotificationReceived($notification, $notification->user_id));
        });
    }

    /**
     * Relationship with User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic relationship)
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Get notification message based on type
     */
    public function getMessageAttribute(): string
    {
        return match($this->type) {
            'message' => $this->getMessageNotificationMessage(),
            'like' => $this->getLikeNotificationMessage(),
            'comment' => $this->getCommentNotificationMessage(),
            'follow' => $this->getFollowNotificationMessage(),
            'mention' => $this->getMentionNotificationMessage(),
            default => 'You have a new notification'
        };
    }

    /**
     * Get message notification text
     */
    private function getMessageNotificationMessage(): string
    {
        $sender = $this->data['sender_name'] ?? 'Someone';
        return "New message from {$sender}";
    }

    /**
     * Get like notification text
     */
    private function getLikeNotificationMessage(): string
    {
        $liker = $this->data['liker_name'] ?? 'Someone';
        return "{$liker} liked your post";
    }

    /**
     * Get comment notification text
     */
    private function getCommentNotificationMessage(): string
    {
        $commenter = $this->data['commenter_name'] ?? 'Someone';
        return "{$commenter} commented on your post";
    }

    /**
     * Get follow notification text
     */
    private function getFollowNotificationMessage(): string
    {
        $follower = $this->data['follower_name'] ?? 'Someone';
        return "{$follower} started following you";
    }

    /**
     * Get mention notification text
     */
    private function getMentionNotificationMessage(): string
    {
        $mentioner = $this->data['mentioner_name'] ?? 'Someone';
        $mentionableType = $this->data['mentionable_type'] ?? 'post';

        if ($mentionableType === 'App\\Models\\Post') {
            return "{$mentioner} mentioned you in a post";
        } elseif ($mentionableType === 'App\\Models\\Comment') {
            return "{$mentioner} mentioned you in a comment";
        }

        return "{$mentioner} mentioned you";
    }
}
