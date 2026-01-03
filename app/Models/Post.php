<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'media_type', 'media_path', 'media_thumbnail', 'is_private'];

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
}
