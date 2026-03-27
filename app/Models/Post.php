<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'slug',
        'media_type',
        'media_path',
        'media_thumbnail',
        'is_private',
        'pinned_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
        'is_private' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function generateUniqueSlug()
    {
        do {
            $slug = Str::random(24);
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function getContentHtmlAttribute(): string
    {
        if (!$this->content) {
            return '';
        }
        return app(\App\Services\HashtagService::class)->convertToLinks($this->content);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function likedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function savedBy(User $user)
    {
        return $this->savedPosts()->where('user_id', $user->id)->exists();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class)->ordered();
    }

    public function savedPosts()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function reports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function pendingReports()
    {
        return $this->hasMany(PostReport::class)->where('status', PostReport::STATUS_PENDING);
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class)->withTimestamps();
    }

    /**
     * Get the life event associated with this post
     */
    public function event()
    {
        return $this->hasOne(Event::class);
    }

    /**
     * Check if the post is pinned
     */
    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    /**
     * Pin the post
     */
    public function pin(): void
    {
        $this->update(['pinned_at' => now()]);
    }

    /**
     * Unpin the post
     */
    public function unpin(): void
    {
        $this->update(['pinned_at' => null]);
    }

    /**
     * Scope for pinned posts
     */
    public function scopePinned($query)
    {
        return $query->whereNotNull('pinned_at');
    }

    /**
     * Scope for non-pinned posts
     */
    public function scopeNotPinned($query)
    {
        return $query->whereNull('pinned_at');
    }
}