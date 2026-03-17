<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'content_encoding',
        'settings',
        'last_used_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification setting for a specific type.
     */
    public function getSetting(string $key, bool $default = true): bool
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Update a notification setting.
     */
    public function updateSetting(string $key, bool $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get default settings for new subscriptions.
     */
    public static function getDefaultSettings(): array
    {
        return [
            'likes' => true,
            'comments' => true,
            'follows' => true,
            'messages' => true,
            'mentions' => true,
        ];
    }

    /**
     * Check if subscription is valid and active.
     */
    public function isValid(): bool
    {
        // Check if all required fields are present
        if (!$this->endpoint || !$this->p256dh || !$this->auth) {
            return false;
        }

        // Check if subscription was used in the last 6 months
        if ($this->last_used_at && $this->last_used_at->diffInMonths() > 6) {
            return false;
        }

        return true;
    }

    /**
     * Update last used timestamp.
     */
    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
