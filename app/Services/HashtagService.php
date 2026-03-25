<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;

class HashtagService
{
    /**
     * Extract hashtags from content and sync with post
     */
    public function syncHashtags(Post $post, string $content): void
    {
        // Extract all hashtags from content
        preg_match_all('/#(\w+)/', $content, $matches);
        
        $hashtagNames = array_unique($matches[1]);
        $hashtagIds = [];

        foreach ($hashtagNames as $hashtagName) {
            $hashtag = Hashtag::findOrCreate($hashtagName);
            $hashtagIds[] = $hashtag->id;
            $hashtag->incrementUsage();
        }

        // Remove old hashtags that are no longer in the content
        $oldHashtagIds = $post->hashtags()->pluck('hashtags.id')->toArray();
        $removedHashtagIds = array_diff($oldHashtagIds, $hashtagIds);

        foreach ($removedHashtagIds as $hashtagId) {
            $hashtag = Hashtag::find($hashtagId);
            if ($hashtag) {
                $hashtag->decrementUsage();
            }
        }

        // Sync hashtags
        $post->hashtags()->sync($hashtagIds);
    }

    /**
     * Remove all hashtags from a post
     */
    public function removePostHashtags(Post $post): void
    {
        foreach ($post->hashtags as $hashtag) {
            $hashtag->decrementUsage();
        }
        $post->hashtags()->detach();
    }

    /**
     * Convert hashtags in content to clickable links
     */
    public function linkify(string $content): string
    {
        return preg_replace_callback(
            '/#(\w+)/',
            function ($matches) {
                $hashtag = $matches[1];
                $slug = strtolower($hashtag);
                // Add dir="ltr" and unicode-bidi to ensure proper display in RTL
                return '<a href="' . route('hashtags.show', $slug) . '" class="hashtag-link" dir="ltr" style="unicode-bidi: isolate;">#' . $hashtag . '</a>';
            },
            $content
        );
    }

    /**
     * Convert both mentions and hashtags in text to links
     */
    public function convertToLinks(string $text): string
    {
        $text = app(\App\Services\MentionService::class)->convertMentionsToLinks($text);
        return $this->linkify($text);
    }
}
