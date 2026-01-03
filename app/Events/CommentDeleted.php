<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commentId;
    public $postId;
    public $commentsCount;

    public function __construct($commentId, $postId, $commentsCount)
    {
        $this->commentId = $commentId;
        $this->postId = $postId;
        $this->commentsCount = $commentsCount;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("post.{$this->postId}")];
    }

    public function broadcastAs(): string
    {
        return "comment.deleted";
    }

    public function broadcastWith(): array
    {
        return [
            "comment_id" => $this->commentId,
            "post_id" => $this->postId,
            "comments_count" => $this->commentsCount,
        ];
    }
}