<?php

namespace App\Events;

use App\Models\StoryReaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reaction;

    public function __construct(StoryReaction $reaction)
    {
        $this->reaction = $reaction;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("story.{$this->reaction->story_id}")];
    }

    public function broadcastAs(): string
    {
        return "story.reacted";
    }

    public function broadcastWith(): array
    {
        return [
            "story_id" => $this->reaction->story_id,
            "user_id" => $this->reaction->user_id,
            "user_name" => $this->reaction->user->name,
            "reaction_type" => $this->reaction->reaction_type,
            "reactions_count" => $this->reaction->story->reactions()->count(),
        ];
    }
}