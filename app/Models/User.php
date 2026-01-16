<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_suspended',
        'verification_code',
        'verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the route key name for model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function follows()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isFollowing(User $user)
    {
        return $this->follows()->where('followed_id', $user->id)->exists();
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function blockedUsers()
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blockedBy()
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function isBlocking(User $user)
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user)
    {
        return $this->blockedBy()->where('blocker_id', $user->id)->exists();
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function storyViews()
    {
        return $this->hasMany(StoryView::class);
    }

    public function storyReactions()
    {
        return $this->hasMany(StoryReaction::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, function($query) {
            $query->where('user1_id', $this->id)
                  ->orWhere('user2_id', $this->id);
        });
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function activeStories()
    {
        return $this->stories()->active();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentLikes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function savedPosts()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Generate a 6-digit verification code
     */
    public function generateVerificationCode()
    {
        $this->verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verification_code_expires_at = now()->addMinutes(10); // Code expires in 10 minutes
        $this->save();

        return $this->verification_code;
    }

    /**
     * Verify the provided code
     */
    public function verifyCode($code)
    {
        if ($this->verification_code !== $code) {
            return false;
        }

        if (now()->isAfter($this->verification_code_expires_at)) {
            return false; // Code expired
        }

        // Mark email as verified
        $this->email_verified_at = now();
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->save();

        return true;
    }

    /**
     * Check if verification code is expired
     */
    public function isVerificationCodeExpired()
    {
        return $this->verification_code_expires_at && now()->isAfter($this->verification_code_expires_at);
    }
}
