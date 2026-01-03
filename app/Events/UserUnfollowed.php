<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnfollowed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $followerId;
    public $followerName;
    public $followedId;
    public $followedName;
    public $followersCount;

    public function __construct($followerId, $followerName, $followedId, $followedName, $followersCount)
    {
        $this->followerId = $followerId;
        $this->followerName = $followerName;
        $this->followedId = $followedId;
        $this->followedName = $followedName;
        $this->followersCount = $followersCount;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->followedId}"),
            new PrivateChannel("user.{$this->followerId}")
        ];
    }

    public function broadcastAs(): string
    {
        return "user.unfollowed";
    }

    public function broadcastWith(): array
    {
        return [
            "follower_id" => $this->followerId,
            "follower_name" => $this->followerName,
            "followed_id" => $this->followedId,
            "followed_name" => $this->followedName,
            "followers_count" => $this->followersCount,
            "following_count" => \App\Models\User::find($this->followerId)?->follows()->count() ?? 0,
        ];
    }
}
