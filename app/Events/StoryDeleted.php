<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $storyId;
    public $userId;
    public $userName;

    public function __construct($storyId, $userId, $userName)
    {
        $this->storyId = $storyId;
        $this->userId = $userId;
        $this->userName = $userName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->userId}"),
            new PrivateChannel("stories")
        ];
    }

    public function broadcastAs(): string
    {
        return "story.deleted";
    }

    public function broadcastWith(): array
    {
        return [
            "story_id" => $this->storyId,
            "user_id" => $this->userId,
            "user_name" => $this->userName,
        ];
    }
}
