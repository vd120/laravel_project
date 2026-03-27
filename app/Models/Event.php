<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'event_type',
        'title',
        'description',
        'event_date',
        'year',
        'is_anniversary',
        'is_private',
        'badge_icon',
        'metadata',
        'post_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_anniversary' => 'boolean',
        'is_private' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Event type labels
     */
    public const EVENT_TYPES = [
        'new_job' => 'New Job',
        'graduation' => 'Graduation',
        'engagement' => 'Engagement',
        'baby' => 'Baby',
        'moved' => 'Moved',
        'birthday' => 'Birthday',
    ];

    /**
     * Default emoji icons for each event type
     */
    public const EVENT_ICONS = [
        'new_job' => '💼',
        'graduation' => '🎓',
        'engagement' => '💍',
        'baby' => '👶',
        'moved' => '🏠',
        'birthday' => '🎂',
    ];

    /**
     * Special emojis for reactions
     */
    public const REACTION_EMOJIS = [
        'new_job' => ['🎉', '👏', '🚀', '💪', '🔥'],
        'graduation' => ['🎉', '👏', '🎓', '✨', '🌟'],
        'engagement' => ['💍', '❤️', '🎉', '💕', '😍'],
        'baby' => ['👶', '🍼', '🧸', '💕', '😍'],
        'moved' => ['🏠', '🎉', '📦', '🚚', '✨'],
        'birthday' => ['🎂', '🎉', '🎁', '🎈', '🕯️'],
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->badge_icon)) {
                $event->badge_icon = self::EVENT_ICONS[$event->event_type] ?? '🎉';
            }
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug();
            }
        });
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate unique slug
     */
    public static function generateUniqueSlug()
    {
        do {
            $slug = 'event-' . Str::random(24);
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    /**
     * Get the user who created the event
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reactions for this event
     */
    public function reactions()
    {
        return $this->hasMany(EventReaction::class);
    }

    /**
     * Get the post associated with this event
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Check if a user has reacted to this event
     */
    public function reactedBy(User $user)
    {
        return $this->reactions()->where('user_id', $user->id)->exists();
    }

    /**
     * Get reaction count by emoji
     */
    public function getReactionCountByEmoji(string $emoji): int
    {
        return $this->reactions()->where('reaction_type', $emoji)->count();
    }

    /**
     * Get all reactions grouped by emoji
     */
    public function getGroupedReactions()
    {
        return $this->reactions()
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->with('user')
            ->get()
            ->keyBy('reaction_type');
    }

    /**
     * Get the icon for this event type
     */
    public function getIconAttribute(): string
    {
        return $this->badge_icon ?? (self::EVENT_ICONS[$this->event_type] ?? '🎉');
    }

    /**
     * Get formatted event date
     */
    public function getFormattedDateAttribute(): string
    {
        // Get current locale
        $locale = app()->getLocale();
        
        if ($this->is_anniversary && $this->year) {
            // Anniversary format: "March 27 • 2020" or "27 مارس • 2020"
            if ($locale === 'ar') {
                $monthDay = $this->event_date->translatedFormat('j F');
                return "{$monthDay} • {$this->year}";
            }
            $monthDay = $this->event_date->format('F j');
            return "{$monthDay} • {$this->year}";
        }

        // Regular format: "March 27, 2026" or "27 مارس 2026"
        if ($locale === 'ar') {
            return $this->event_date->translatedFormat('j F Y');
        }
        
        return $this->event_date->format('F j, Y');
    }

    /**
     * Get years since event (for anniversaries)
     */
    public function getYearsSinceAttribute(): ?int
    {
        if (!$this->is_anniversary || !$this->year) {
            return null;
        }

        return now()->year - $this->year;
    }

    /**
     * Check if event is today
     */
    public function isToday(): bool
    {
        return $this->event_date->isToday();
    }

    /**
     * Check if event is upcoming (within 7 days)
     */
    public function isUpcoming(): bool
    {
        $today = now()->startOfDay();
        $eventDate = $this->event_date->copy()->year(now()->year)->startOfDay();

        // For anniversaries, check if it's within the next 7 days
        if ($this->is_anniversary) {
            return $eventDate >= $today && $eventDate <= $today->addDays(7);
        }

        return $this->event_date->between($today, $today->addDays(7));
    }

    /**
     * Scope for public events
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope for specific event type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        $today = now()->startOfDay();
        return $query->where('event_date', '>=', $today)
            ->where('event_date', '<=', $today->addDays(30));
    }

    /**
     * Scope for past events
     */
    public function scopePast($query)
    {
        return $query->where('event_date', '<', now()->startOfDay());
    }
}
