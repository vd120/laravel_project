<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('stories', function ($user) {
    return auth()->check(); // Allow all authenticated users to listen to story events
});

Broadcast::channel('post.{postId}', function ($user, $postId) {
    return auth()->check(); // Allow all authenticated users to listen to post events
});

Broadcast::channel('conversation.{conversationSlug}', function ($user, $conversationSlug) {
    return \App\Models\Conversation::where('slug', $conversationSlug)
        ->where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
        })->exists();
});
