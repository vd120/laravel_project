<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id', 'last_message_at', 'slug', 'is_group', 'group_id', 'name', 'avatar'];

    protected $dates = ['last_message_at'];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function getOtherUserAttribute()
    {
        if ($this->is_group) {
            return null;
        }
        
        $currentUserId = auth()->id();
        return $this->user1_id === $currentUserId ? $this->user2 : $this->user1;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->is_group) {
            return $this->name ?? $this->group?->name ?? 'Group Chat';
        }
        
        return $this->other_user?->name ?? 'Conversation';
    }

    public function getDisplayAvatarAttribute()
    {
        if ($this->is_group) {
            if ($this->avatar) {
                return asset('storage/' . $this->avatar);
            }
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->display_name) . '&background=25d366&color=fff&size=200';
        }
        
        return $this->other_user?->profile?->avatar 
            ? asset('storage/' . $this->other_user->profile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->display_name) . '&background=1DA1F2&color=fff&size=200';
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->where('messages.sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function isMember($userId): bool
    {
        if ($this->is_group && $this->group) {
            return $this->group->hasMember(User::find($userId));
        }
        
        return $this->user1_id === $userId || $this->user2_id === $userId;
    }

    public static function getConversationBetween($user1Id, $user2Id)
    {
        return static::where('is_group', false)
            ->where(function ($query) use ($user1Id, $user2Id) {
                $query->where('user1_id', $user1Id)->where('user2_id', $user2Id);
            })->orWhere(function ($query) use ($user1Id, $user2Id) {
                $query->where('user1_id', $user2Id)->where('user2_id', $user1Id);
            })->first();
    }

    public static function createConversation($user1Id, $user2Id)
    {
        // Ensure user1_id is always smaller than user2_id for consistency
        if ($user1Id > $user2Id) {
            [$user1Id, $user2Id] = [$user2Id, $user1Id];
        }

        return static::create([
            'user1_id' => $user1Id,
            'user2_id' => $user2Id,
            'slug' => Str::random(24),
            'is_group' => false,
        ]);
    }

    public static function createGroupConversation(Group $group): self
    {
        return static::create([
            'is_group' => true,
            'group_id' => $group->id,
            'name' => $group->name,
            'avatar' => $group->avatar,
            'slug' => Str::random(24),
        ]);
    }

    /**
     * Get all recipients for a message in this conversation
     */
    public function getRecipients($senderId): array
    {
        if ($this->is_group && $this->group) {
            return $this->group->members()
                ->where('user_id', '!=', $senderId)
                ->pluck('user_id')
                ->toArray();
        }
        
        // For direct messages, return the other user
        return [$this->user1_id === $senderId ? $this->user2_id : $this->user1_id];
    }
}
