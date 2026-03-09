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

        // Note: Real-time notifications are now handled via polling
        // No WebSocket events needed
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
            'group_invite' => $this->getGroupInviteNotificationMessage(),
            default => 'You have a new notification'
        };
    }

    /**
     * Get message notification text
     */
    private function getMessageNotificationMessage(): string
    {
        $sender = $this->data['sender_username'] ?? __('chat.user');
        $preview = $this->data['message_preview'] ?? '';
        $messageType = $this->data['message_type'] ?? 'text';

        // Check if this is a story reply
        if (str_starts_with($preview, '📸 Reply to your story:')) {
            $preview = trim(str_replace('📸 Reply to your story:', '', $preview));
            return __('notifications.story_reply_message', ['sender' => $sender, 'preview' => $preview]);
        }

        // For media messages, show "username: sent an image/video/etc"
        if ($messageType !== 'text' && !empty($messageType)) {
            return "{$sender}: {$preview}";
        }

        // For text messages, show the preview
        return "{$sender}: {$preview}";
    }

    /**
     * Get like notification text
     */
    private function getLikeNotificationMessage(): string
    {
        $liker = $this->data['liker_name'] ?? __('chat.user');
        return __('notifications.liked_your_post', ['user' => $liker]);
    }

    /**
     * Get comment notification text
     */
    private function getCommentNotificationMessage(): string
    {
        $commenter = $this->data['commenter_name'] ?? __('chat.user');
        return __('notifications.commented_on_your_post', ['user' => $commenter]);
    }

    /**
     * Get follow notification text
     */
    private function getFollowNotificationMessage(): string
    {
        $follower = $this->data['follower_name'] ?? __('chat.user');
        return __('notifications.new_follower', ['user' => $follower]);
    }

    /**
     * Get mention notification text
     */
    private function getMentionNotificationMessage(): string
    {
        $mentioner = $this->data['mentioner_name'] ?? __('chat.user');
        $mentionableType = $this->data['mentionable_type'] ?? 'post';

        if ($mentionableType === 'App\\Models\\Post') {
            return __('notifications.mentioned_you_in_post', ['user' => $mentioner]);
        } elseif ($mentionableType === 'App\\Models\\Comment') {
            return __('notifications.mentioned_you_in_comment', ['user' => $mentioner]);
        }

        return __('notifications.mentioned_you', ['user' => $mentioner]);
    }

    /**
     * Get group invite notification text
     */
    private function getGroupInviteNotificationMessage(): string
    {
        if (empty($this->data) || !is_array($this->data)) {
            return __('notifications.invited_to_group_generic');
        }

        $inviter = $this->data['inviter_username'] ?? null;
        $groupName = $this->data['group_name'] ?? null;

        // If data is not loaded yet, return generic message
        if (!$inviter && !$groupName) {
            return __('notifications.invited_to_group_generic');
        }

        $inviterText = $inviter ?? __('chat.someone');
        $groupNameText = $groupName ?? __('chat.group');

        return __('notifications.invited_to_group', ['user' => $inviterText, 'group' => $groupNameText]);
    }
}
