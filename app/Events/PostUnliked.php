<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUnliked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $userId;
    public $userName;
    public $likesCount;

    /**
     * Create a new event instance.
     */
    public function __construct($postId, $userId, $userName, $likesCount)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->likesCount = $likesCount;
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
        return 'post.unliked';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'likes_count' => $this->likesCount,
        ];
    }
}
