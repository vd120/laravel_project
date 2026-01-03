<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Story extends Model
{
    protected $fillable = ['user_id', 'media_type', 'media_path', 'content', 'expires_at', 'views'];

    protected $dates = ['expires_at'];

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
