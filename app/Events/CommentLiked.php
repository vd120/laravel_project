<?php

namespace App\Events;

use App\Models\CommentLike;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentLiked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $like;

    public function __construct(CommentLike $like)
    {
        $this->like = $like;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("post.{$this->like->comment->post_id}")];
    }

    public function broadcastAs(): string
    {
        return "comment.liked";
    }

    public function broadcastWith(): array
    {
        return [
            "comment_id" => $this->like->comment_id,
            "user_id" => $this->like->user_id,
            "user_name" => $this->like->user->name,
            "likes_count" => $this->like->comment->likes()->count(),
        ];
    }
}