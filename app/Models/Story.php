<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Story extends Model
{
    protected $fillable = ['user_id', 'slug', 'media_type', 'media_path', 'content', 'expires_at', 'views'];

    protected $dates = ['expires_at'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($story) {
            if (empty($story->slug)) {
                $story->slug = Str::random(24);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storyViews()
    {
        return $this->hasMany(StoryView::class);
    }

    public function reactions()
    {
        return $this->hasMany(StoryReaction::class);
    }

    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    public function getTimeRemainingAttribute()
    {
        return Carbon::now()->diffInHours($this->expires_at, false);
    }
}
