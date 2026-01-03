<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryUnreacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $storyId;
    public $userId;
    public $userName;
    public $reactionsCount;

    public function __construct($storyId, $userId, $userName, $reactionsCount)
    {
        $this->storyId = $storyId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->reactionsCount = $reactionsCount;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("story.{$this->storyId}")];
    }

    public function broadcastAs(): string
    {
        return "story.unreacted";
    }

    public function broadcastWith(): array
    {
        return [
            "story_id" => $this->storyId,
            "user_id" => $this->userId,
            "user_name" => $this->userName,
            "reactions_count" => $this->reactionsCount,
        ];
    }
}