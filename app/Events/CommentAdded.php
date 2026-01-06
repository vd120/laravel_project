<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $postId;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment, int $postId)
    {
        $this->comment = $comment;
        $this->postId = $postId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('post.' . $this->postId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'comment.added';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'content' => app(\App\Services\MentionService::class)->convertMentionsToLinks($this->comment->content),
                'user' => [
                    'id' => $this->comment->user->id,
                    'name' => $this->comment->user->name,
                    'avatar' => $this->comment->user->profile && $this->comment->user->profile->avatar
                        ? asset('storage/' . $this->comment->user->profile->avatar)
                        : null,
                ],
                'created_at' => $this->comment->created_at->diffForHumans(),
                'likes_count' => $this->comment->likes->count(),
            ],
            'post_id' => $this->postId,
        ];
    }
}
