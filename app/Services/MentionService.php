<?php

namespace App\Services;

use App\Models\User;
use App\Models\Mention;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;

class MentionService
{
    /**
     * Parse mentions from text and return an array of mentioned usernames
     */
    public function parseMentions(string $text): array
    {
        
        preg_match_all('/@([a-zA-Z0-9_-]+)/', $text, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Process mentions for a given model (Post or Comment)
     */
    public function processMentions(Model $mentionable, string $text, int $mentionerId): void
    {
        $mentionedUsernames = $this->parseMentions($text);

        if (empty($mentionedUsernames)) {
            return;
        }

        
        $mentionedUsers = User::whereIn('name', $mentionedUsernames)
            ->where('id', '!=', $mentionerId)
            ->whereDoesntHave('blockedBy', function($query) use ($mentionerId) {
                $query->where('blocker_id', $mentionerId);
            })
            ->whereDoesntHave('blockedUsers', function($query) use ($mentionerId) {
                $query->where('blocked_id', $mentionerId);
            })
            ->get();

        foreach ($mentionedUsers as $mentionedUser) {
            
            Mention::create([
                'mentioner_id' => $mentionerId,
                'mentioned_id' => $mentionedUser->id,
                'mentionable_type' => get_class($mentionable),
                'mentionable_id' => $mentionable->id,
            ]);

            
            Notification::create([
                'user_id' => $mentionedUser->id,
                'type' => 'mention',
                'data' => [
                    'mentioner_name' => User::find($mentionerId)->name,
                    'mentionable_type' => get_class($mentionable),
                ],
                'related_type' => get_class($mentionable),
                'related_id' => $mentionable->id,
            ]);
        }
    }

    /**
     * Convert mentions in text to clickable links
     */
    public function convertMentionsToLinks(string $text): string
    {
        return preg_replace_callback(
            '/@([a-zA-Z0-9_-]+)/',
            function ($matches) {
                $username = $matches[1];
                $user = User::where('name', $username)->first();

                if ($user) {
                    return '<a href="' . route('users.show', $user->name) . '" class="mention-link">@' . $username . '</a>';
                }

                return '@' . $username; 
            },
            $text
        );
    }
}
