<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id', 'last_message_at', 'slug'];

    protected $dates = ['last_message_at'];

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
        $currentUserId = auth()->id();
        return $this->user1_id === $currentUserId ? $this->user2 : $this->user1;
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->where('messages.sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public static function getConversationBetween($user1Id, $user2Id)
    {
        return static::where(function ($query) use ($user1Id, $user2Id) {
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
            'slug' => \Illuminate\Support\Str::random(24),
        ]);
    }
}
