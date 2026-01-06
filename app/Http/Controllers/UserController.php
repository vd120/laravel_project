<?php

namespace App\Http\Controllers;

use App\Events\UserFollowed;
use App\Events\UserUnfollowed;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $user->load('profile');

        // Load blocked users only for the profile owner
        $blocked = null;
        if (auth()->check() && auth()->id() === $user->id) {
            $blocked = $user->blockedUsers()->with('blocked')->get();
        }

        return view('users.show', compact('user', 'blocked'));
    }

    public function follow(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->isFollowing($user)) {
            $currentUser->follows()->where('followed_id', $user->id)->delete();

            broadcast(new UserUnfollowed(
                $currentUser->id,
                $currentUser->name,
                $user->id,
                $user->name,
                $user->followers()->count()
            ))->toOthers();
        } else {
            $follow = $currentUser->follows()->create(['followed_id' => $user->id]);

            // Create notification for the followed user
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'follow',
                'data' => [
                    'follower_name' => $currentUser->name,
                    'follower_id' => $currentUser->id
                ],
                'related_type' => \App\Models\Follow::class,
                'related_id' => $follow->id
            ]);

            broadcast(new UserFollowed($follow))->toOthers();
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'following' => $currentUser->isFollowing($user),
                'followers_count' => $user->followers()->count()
            ]);
        }

        return back();
    }

    public function block(User $user)
    {
        // Prevent blocking admin users
        if ($user->is_admin) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot block admin users'
                ], 403);
            }
            return back()->with('error', 'Cannot block admin users');
        }

        if (auth()->user()->isBlocking($user)) {
            auth()->user()->blockedUsers()->where('blocked_id', $user->id)->delete();
        } else {
            auth()->user()->blockedUsers()->create(['blocked_id' => $user->id]);
            // Also unfollow if they were following
            auth()->user()->follows()->where('followed_id', $user->id)->delete();
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'blocking' => auth()->user()->isBlocking($user)
            ]);
        }

        return back();
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->with('follower')->get();
        return view('users.followers', compact('user', 'followers'));
    }

    public function following(User $user)
    {
        $following = $user->follows()->with('followed')->get();
        return view('users.following', compact('user', 'following'));
    }

    public function blocked(User $user)
    {
        // Only allow users to see their own blocked list for privacy
        if ($user->id !== auth()->id()) {
            abort(403, 'You can only view your own blocked users list.');
        }

        $blocked = $user->blockedUsers()->with('blocked')->get();
        return view('users.blocked', compact('user', 'blocked'));
    }

    public function savedPosts()
    {
        $user = auth()->user();
        $savedPosts = \App\Models\SavedPost::where('user_id', $user->id)
            ->with(['post.user', 'post.media', 'post.comments.replies.user', 'post.comments.likes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('users.saved-posts', compact('user', 'savedPosts'));
    }

    public function explore()
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        // Get all users except current user with their relationships
        $users = User::where('id', '!=', $currentUser->id)
            ->with(['profile', 'followers', 'follows'])
            ->withCount(['followers', 'follows'])
            ->orderBy('created_at', 'desc')
            ->paginate(20); // Paginate for better performance

        // Get blocked user IDs for current user
        $blockedByCurrentUser = $currentUser->blockedUsers()->pluck('blocked_id')->toArray();
        $blockedCurrentUser = Block::where('blocked_id', $currentUser->id)->pluck('blocker_id')->toArray();

        // Debug logging
        \Log::info('Explore page accessed', [
            'user_id' => $currentUser->id,
            'user_count' => $users->count(),
            'blocked_by_count' => count($blockedByCurrentUser),
            'blocked_current_count' => count($blockedCurrentUser)
        ]);

        return view('users.explore', compact('users', 'blockedByCurrentUser', 'blockedCurrentUser'));
    }

    public function searchPage()
    {
        return view('users.search');
    }

    public function editProfile(User $user)
    {
        // Only allow users to edit their own profile
        if ($user->id !== auth()->id()) {
            abort(403, 'You can only edit your own profile.');
        }

        $user->load('profile');

        // Ensure profile exists
        if (!$user->profile) {
            $user->profile()->create([]);
            $user->load('profile');
        }

        return view('users.edit-profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:users,name,' . $user->id,
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'bio' => 'nullable|string|max:500',
            'about' => 'nullable|string|max:1000',
            'website' => 'nullable|url',
            'location' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'is_private' => 'nullable|boolean',
            'birth_date' => 'nullable|date|before:today',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Update user basic information
        $user->update([
            'name' => $request->username,
            'email' => $request->email,
        ]);

        $data = $request->only([
            'bio', 'about', 'website', 'location', 'occupation',
            'phone', 'gender', 'is_private', 'birth_date'
        ]);

        // Ensure is_private is properly set (checkbox handling)
        $data['is_private'] = $request->has('is_private') ? true : false;

        // Handle avatar upload with compression
        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');

            // Compress and resize avatar (square, 200x200 max)
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $avatarImage = $manager->read($avatarFile);
            $avatarImage->cover(200, 200); // Square crop
            $avatarImage->toJpeg(90); // High quality compression

            $filename = time() . '_avatar_' . uniqid() . '.jpg';
            $avatarPath = 'avatars/' . $filename;
            $avatarImage->save(storage_path('app/public/' . $avatarPath));

            \Log::info('Avatar compressed and stored at: ' . $avatarPath);
            $data['avatar'] = $avatarPath;
        }

        // Handle cover image upload with compression
        if ($request->hasFile('cover_image')) {
            $coverFile = $request->file('cover_image');

            // Compress and resize cover image (1200px width max, maintain aspect ratio)
            $coverManager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $coverImage = $coverManager->read($coverFile);

            // Resize to max width while maintaining aspect ratio
            if ($coverImage->width() > 1200) {
                $coverImage->scale(width: 1200);
            }

            $coverImage->toJpeg(85); // Good quality compression

            $filename = time() . '_cover_' . uniqid() . '.jpg';
            $coverPath = 'covers/' . $filename;
            $coverImage->save(storage_path('app/public/' . $coverPath));

            \Log::info('Cover image compressed and stored at: ' . $coverPath);
            $data['cover_image'] = $coverPath;
        }



        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        // Refresh the user with updated profile data
        $user->load('profile');

        return redirect()->route('users.show', $user)->with('success', 'Profile updated successfully!');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->profile && $user->profile->avatar) {
            // Delete the file from storage
            $avatarPath = storage_path('app/public/' . $user->profile->avatar);
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }

            // Remove from database
            $user->profile->update(['avatar' => null]);

            return response()->json(['success' => true, 'message' => 'Avatar deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'No avatar to delete'], 400);
    }

    public function deleteCoverImage()
    {
        $user = auth()->user();

        if ($user->profile && $user->profile->cover_image) {
            // Delete the file from storage
            $coverPath = storage_path('app/public/' . $user->profile->cover_image);
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }

            // Remove from database
            $user->profile->update(['cover_image' => null]);

            return response()->json(['success' => true, 'message' => 'Cover image deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'No cover image to delete'], 400);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        // Start a database transaction to ensure data integrity
        \DB::beginTransaction();

        try {
            // Log the start of deletion process
            \Log::info('Starting account deletion for user ID: ' . $user->id . ', name: ' . $user->name);

            // Delete all media files from storage
            $this->deleteUserMediaFiles($user);

            // Delete all related data in proper order to avoid foreign key constraints

            // Delete story views first (references stories)
            $storyViewsCount = $user->storyViews()->delete();
            \Log::info('Deleted ' . $storyViewsCount . ' story views');

            // Delete story reactions
            $storyReactionsCount = $user->storyReactions()->delete();
            \Log::info('Deleted ' . $storyReactionsCount . ' story reactions');

            // Delete stories and their media
            $storiesCount = 0;
            foreach ($user->stories as $story) {
                if ($story->media_path && file_exists(storage_path('app/public/' . $story->media_path))) {
                    unlink(storage_path('app/public/' . $story->media_path));
                }
                $storiesCount++;
            }
            $user->stories()->delete();
            \Log::info('Deleted ' . $storiesCount . ' stories');

            // Delete comment likes
            $commentLikesCount = $user->commentLikes()->delete();
            \Log::info('Deleted ' . $commentLikesCount . ' comment likes');

            // Delete comments
            $commentsCount = $user->comments()->delete();
            \Log::info('Deleted ' . $commentsCount . ' comments');

            // Delete post likes
            $likesCount = $user->likes()->delete();
            \Log::info('Deleted ' . $likesCount . ' post likes');

            // Delete saved posts
            $savedPostsCount = $user->savedPosts()->delete();
            \Log::info('Deleted ' . $savedPostsCount . ' saved posts');

            // Delete blocks (both as blocker and blocked)
            $blockedUsersCount = $user->blockedUsers()->delete();
            $blockedByCount = \App\Models\Block::where('blocked_id', $user->id)->delete();
            \Log::info('Deleted ' . $blockedUsersCount . ' blocks as blocker, ' . $blockedByCount . ' blocks as blocked');

            // Delete follows (both as follower and followed)
            $followsCount = $user->follows()->delete();
            $followedByCount = \App\Models\Follow::where('followed_id', $user->id)->delete();
            \Log::info('Deleted ' . $followsCount . ' follows as follower, ' . $followedByCount . ' follows as followed');

            // Delete posts and their media
            $postsCount = 0;
            $mediaCount = 0;
            foreach ($user->posts as $post) {
                foreach ($post->media as $media) {
                    if ($media->media_path && file_exists(storage_path('app/public/' . $media->media_path))) {
                        unlink(storage_path('app/public/' . $media->media_path));
                    }
                    $mediaCount++;
                }
                $post->media()->delete();
                $postsCount++;
            }
            $user->posts()->delete();
            \Log::info('Deleted ' . $postsCount . ' posts with ' . $mediaCount . ' media files');

            // Delete profile (this will cascade to other profile-related data)
            if ($user->profile) {
                $user->profile->delete();
                \Log::info('Deleted user profile');
            }

            // Finally, delete the user account
            $user->delete();
            \Log::info('Deleted user account');

            // Commit the transaction
            \DB::commit();

            // Log out the user (though the user is already deleted, this clears the session)
            auth()->logout();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been permanently deleted.'
            ]);

        } catch (\Exception $e) {
            // Rollback on any error
            \DB::rollback();

            \Log::error('Account deletion failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Get username from user ID (API endpoint)
     */
    public function getUsername($userId)
    {
        try {
            $user = User::findOrFail($userId);

            return response()->json([
                'success' => true,
                'username' => $user->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Delete all media files associated with a user
     */
    private function deleteUserMediaFiles(User $user)
    {
        // Delete avatar
        if ($user->profile && $user->profile->avatar) {
            $avatarPath = storage_path('app/public/' . $user->profile->avatar);
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        // Delete cover image
        if ($user->profile && $user->profile->cover_image) {
            $coverPath = storage_path('app/public/' . $user->profile->cover_image);
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }
        }

        // Delete post media files
        foreach ($user->posts as $post) {
            foreach ($post->media as $media) {
                if ($media->media_path && file_exists(storage_path('app/public/' . $media->media_path))) {
                    unlink(storage_path('app/public/' . $media->media_path));
                }
            }
        }

        // Delete story media files
        foreach ($user->stories as $story) {
            if ($story->media_path && file_exists(storage_path('app/public/' . $story->media_path))) {
                unlink(storage_path('app/public/' . $story->media_path));
            }
        }
    }
}
