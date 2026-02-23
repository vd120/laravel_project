<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User notification channel - for receiving notifications
Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('stories', function ($user) {
    return auth()->check(); // Allow all authenticated users to listen to story events
});

Broadcast::channel('post.{postId}', function ($user, $postId) {
    return auth()->check(); // Allow all authenticated users to listen to post events
});

Broadcast::channel('conversation.{conversationSlug}', function ($user, $conversationSlug) {
    $conversation = \App\Models\Conversation::where('slug', $conversationSlug)->first();
    if (!$conversation) {
        return false;
    }
    
    // For group conversations, check if user is a member
    if ($conversation->is_group) {
        return $conversation->isMember($user->id);
    }
    
    // For direct messages, check if user is participant
    return $conversation->user1_id === $user->id || $conversation->user2_id === $user->id;
});
