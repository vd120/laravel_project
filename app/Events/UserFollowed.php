<?php

namespace App\Events;

use App\Models\Follow;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $follow;

    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->follow->followed_id}"),
            new PrivateChannel("user.{$this->follow->follower_id}")
        ];
    }

    public function broadcastAs(): string
    {
        return "user.followed";
    }

    public function broadcastWith(): array
    {
        return [
            "follower_id" => $this->follow->follower_id,
            "follower_name" => $this->follow->follower->name,
            "followed_id" => $this->follow->followed_id,
            "followed_name" => $this->follow->followed->name,
            "followers_count" => $this->follow->followed->followers()->count(),
            "following_count" => $this->follow->follower->follows()->count(),
        ];
    }
}
