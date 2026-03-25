<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'region',
        'isp',
        'timezone',
        'latitude',
        'longitude',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    // Disable updated_at, only use created_at
    const UPDATED_AT = null;

    /**
     * Get the user that owns the activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for user's activity logs
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for recent activity
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('logged_at', '>=', now()->subDays($days));
    }

    /**
     * Get human-readable action name
     */
    public function getActionNameAttribute(): string
    {
        return match($this->action) {
            'login' => __('activity.login'),
            'logout' => __('activity.logout'),
            'password_change' => __('activity.password_change'),
            'password_reset' => __('activity.password_reset'),
            'email_verification' => __('activity.email_verification'),
            'profile_update' => __('activity.profile_update'),
            'username_change' => __('activity.username_change'),
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get device icon
     */
    public function getDeviceIconAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            'desktop' => 'fas fa-desktop',
            default => 'fas fa-laptop',
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'password_change' => 'fas fa-key',
            'password_reset' => 'fas fa-undo',
            'email_verification' => 'fas fa-envelope',
            'profile_update' => 'fas fa-user-edit',
            'username_change' => 'fas fa-at',
            default => 'fas fa-history',
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'login' => 'text-success',
            'logout' => 'text-muted',
            'password_change' => 'text-warning',
            'password_reset' => 'text-info',
            'email_verification' => 'text-primary',
            'profile_update' => 'text-secondary',
            'username_change' => 'text-purple',
            default => 'text-muted',
        };
    }
}
