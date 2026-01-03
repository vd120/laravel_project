<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUnliked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commentId;
    public $userId;
    public $userName;
    public $likesCount;

    public function __construct($commentId, $userId, $userName, $likesCount)
    {
        $this->commentId = $commentId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->likesCount = $likesCount;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("comment.{$this->commentId}")];
    }

    public function broadcastAs(): string
    {
        return "comment.unliked";
    }

    public function broadcastWith(): array
    {
        return [
            "comment_id" => $this->commentId,
            "user_id" => $this->userId,
            "user_name" => $this->userName,
            "likes_count" => $this->likesCount,
        ];
    }
}