<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Hashtag;
use App\Services\HashtagService;
use Illuminate\Console\Command;

class ExtractHashtags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hashtags:extract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract hashtags from all existing posts';

    /**
     * Execute the console command.
     */
    public function handle(HashtagService $hashtagService): int
    {
        $this->info('Extracting hashtags from existing posts...');
        
        $posts = Post::with('hashtags')->get();
        $totalHashtags = 0;
        
        foreach ($posts as $post) {
            if ($post->content) {
                // Extract hashtags from content
                preg_match_all('/#(\w+)/', $post->content, $matches);
                $hashtagNames = array_unique($matches[1] ?? []);
                
                if (!empty($hashtagNames)) {
                    $this->line("Processing post #{$post->id} with " . count($hashtagNames) . " hashtags");
                    
                    foreach ($hashtagNames as $hashtagName) {
                        $hashtag = Hashtag::findOrCreate($hashtagName);
                        
                        // Check if this hashtag is already associated with the post
                        if (!$post->hashtags->contains($hashtag)) {
                            $post->hashtags()->attach($hashtag);
                            $hashtag->incrementUsage();
                            $totalHashtags++;
                        }
                    }
                }
            }
        }
        
        $this->info("Extracted {$totalHashtags} hashtag associations from " . $posts->count() . " posts.");
        $this->info('Total unique hashtags: ' . Hashtag::count());
        
        return Command::SUCCESS;
    }
}
