<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteExpiredStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-stories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired stories that are older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredStories = \App\Models\Story::where('expires_at', '<', now())->get();

        if ($expiredStories->isEmpty()) {
            $this->info('No expired stories found.');
            return;
        }

        $count = $expiredStories->count();

        // Delete associated media files
        foreach ($expiredStories as $story) {
            if ($story->media_path && \Storage::disk('public')->exists($story->media_path)) {
                \Storage::disk('public')->delete($story->media_path);
            }
        }

        // Delete stories from database
        \App\Models\Story::where('expires_at', '<', now())->delete();

        $this->info("Successfully deleted {$count} expired stories.");
    }
}
