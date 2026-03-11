<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired stories and their media files from storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting expired stories cleanup...');
        $this->newLine();
        
        // Find all expired stories
        $expiredStories = Story::where('expires_at', '<=', now())
            ->with('user')
            ->get();
        
        $totalExpired = $expiredStories->count();
        
        if ($totalExpired === 0) {
            $this->info('✅ No expired stories found. Nothing to clean up.');
            return 0;
        }
        
        $this->info("📊 Found {$totalExpired} expired story/stories to delete.");
        $this->newLine();
        
        $deletedCount = 0;
        $failedCount = 0;
        $freedSpace = 0;
        
        foreach ($expiredStories as $story) {
            try {
                $storyInfo = "Story #{$story->id} (User: {$story->user->username ?? 'unknown'})";
                
                // Calculate file size before deletion
                if ($story->media_path && Storage::disk('public')->exists($story->media_path)) {
                    $freedSpace += Storage::disk('public')->size($story->media_path);
                }
                
                // Delete media file from storage
                if ($story->media_path && Storage::disk('public')->exists($story->media_path)) {
                    Storage::disk('public')->delete($story->media_path);
                    $this->line("  🗑️  Deleted media file: {$story->media_path}");
                }
                
                // Delete story thumbnail if exists
                if ($story->media_thumbnail && Storage::disk('public')->exists($story->media_thumbnail)) {
                    Storage::disk('public')->delete($story->media_thumbnail);
                    $this->line("  🗑️  Deleted thumbnail: {$story->media_thumbnail}");
                }
                
                // Delete story reactions
                $story->reactions()->delete();
                
                // Delete story views
                $story->storyViews()->delete();
                
                // Delete story record from database
                $story->delete();
                
                $deletedCount++;
                $this->info("  ✅ Deleted: {$storyInfo}");
                
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("  ❌ Failed to delete: {$storyInfo}");
                $this->error("     Error: " . $e->getMessage());
                
                // Log the error for manual review
                \Log::error('Failed to delete expired story', [
                    'story_id' => $story->id,
                    'user_id' => $story->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->newLine();
        $this->info('━'.str_repeat('━', 50).'━');
        $this->info('📈 Cleanup Summary:');
        $this->info("  • Total expired: {$totalExpired}");
        $this->info("  • Successfully deleted: {$deletedCount}");
        $this->info("  • Failed: {$failedCount}");
        
        // Format freed space
        if ($freedSpace > 0) {
            $freedSpaceFormatted = $this->formatBytes($freedSpace);
            $this->info("  • Storage freed: {$freedSpaceFormatted}");
        }
        
        $this->info('━'.str_repeat('━', 50).'━');
        $this->newLine();
        
        return $failedCount > 0 ? 1 : 0;
    }
    
    /**
     * Format bytes into human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
