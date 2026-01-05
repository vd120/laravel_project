<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Story;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Profile;
use App\Models\SavedPost;
use App\Models\StoryReaction;
use App\Models\StoryView;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds to remove all test data.
     */
    public function run(): void
    {
        $this->command->warn('🧹 Cleaning up all test data from database...');
        $this->command->warn('This will remove ALL user-generated content!');

        // Confirm with user (in production you'd want more sophisticated confirmation)
        if (!$this->command->confirm('Are you sure you want to remove all test data? This cannot be undone.', false)) {
            $this->command->info('Cleanup cancelled.');
            return;
        }

        // Temporarily disable foreign key checks to allow truncating tables with constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Remove in reverse order of dependencies

            // 1. Remove notifications first
            $notificationCount = Notification::count();
            Notification::truncate();
            $this->command->info("Removed {$notificationCount} notifications");

            // 2. Remove story-related data
            $storyReactionCount = StoryReaction::count();
            StoryReaction::truncate();

            $storyViewCount = StoryView::count();
            StoryView::truncate();
            $this->command->info("Removed {$storyReactionCount} story reactions and {$storyViewCount} story views");

            // 3. Remove saved posts
            $savedPostCount = SavedPost::count();
            SavedPost::truncate();
            $this->command->info("Removed {$savedPostCount} saved posts");

            // 4. Remove messages first (before conversations due to foreign key)
            $messageCount = Message::count();
            Message::truncate();
            $this->command->info("Removed {$messageCount} messages");

            // 5. Remove conversations
            $conversationCount = Conversation::count();
            Conversation::truncate();
            $this->command->info("Removed {$conversationCount} conversations");

            // 6. Remove comments (including nested replies)
            $commentCount = Comment::count();
            Comment::truncate();
            $this->command->info("Removed {$commentCount} comments and replies");

            // 7. Remove likes
            $likeCount = Like::count();
            Like::truncate();
            $this->command->info("Removed {$likeCount} likes");

            // 8. Remove stories
            $storyCount = Story::count();
            Story::truncate();
            $this->command->info("Removed {$storyCount} stories");

            // 9. Remove posts and associated media files
            $posts = Post::with('media')->get();
            $mediaFileCount = 0;

            foreach ($posts as $post) {
                // Remove media files if they exist
                if ($post->media && $post->media->count() > 0) {
                    foreach ($post->media as $media) {
                        $filePath = storage_path('app/public/' . $media->media_path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                            $mediaFileCount++;
                        }
                    }
                }
            }

            $postCount = Post::count();
            Post::truncate();
            $this->command->info("Removed {$postCount} posts and {$mediaFileCount} media files");

            // 10. Remove follow relationships
            $followCount = Follow::count();
            Follow::truncate();
            $this->command->info("Removed {$followCount} follow relationships");

            // 11. Remove profiles (but keep admin profile if it exists)
            $profileCount = Profile::whereHas('user', function($query) {
                $query->where('email', 'not like', 'admin%');
            })->delete();
            $this->command->info("Removed {$profileCount} user profiles");

            // 12. Remove all users except admin and test user
            $userCount = User::where('email', 'not like', 'admin%')
                ->where('email', '!=', 'test@example.com')
                ->delete();
            $this->command->info("Removed {$userCount} test users");

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Clear any cached files that might contain sensitive data
            $this->command->info('Clearing application cache...');
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');

            $this->command->info('✅ All test data has been successfully removed!');
            $this->command->info('Remaining users: Admin user and basic test user (if they exist)');
            $this->command->warn('⚠️  Make sure to backup important data before running this seeder!');

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            throw $e;
        }
    }
}
