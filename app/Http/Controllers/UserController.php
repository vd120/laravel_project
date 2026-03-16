<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show(User $user)
    {
        $user->load(['profile']);
        
        $postsCount = $user->posts()->count();
        $followersCount = $user->followers()->count();
        $followingCount = $user->follows()->count();
        $blockedCount = 0;

        $isFollowing = false;
        $isBlocking = false;
        $isBlockedBy = false;

        if (auth()->check()) {
            $isFollowing = auth()->user()->isFollowing($user);
            $isBlocking = auth()->user()->isBlocking($user);
            $isBlockedBy = $user->isBlocking(auth()->user());

            if (auth()->id() === $user->id) {
                $blockedCount = $user->blockedUsers()->count();
            }
        }

        $posts = $user->posts()
            ->with(['media', 'comments.replies.user', 'comments.likes', 'likes'])
            ->latest()
            ->paginate(10);

        return view('users.show', compact('user', 'posts', 'postsCount', 'followersCount', 'followingCount', 'blockedCount', 'isFollowing', 'isBlocking', 'isBlockedBy'));
    }

    public function follow(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->isFollowing($user)) {
            $currentUser->follows()->where('followed_id', $user->id)->delete();
        } else {
            $follow = $currentUser->follows()->create(['followed_id' => $user->id]);

            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'follow',
                'data' => [
                    'follower_name' => $currentUser->username,
                    'follower_id' => $currentUser->id
                ],
                'related_type' => \App\Models\Follow::class,
                'related_id' => $follow->id
            ]);
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            // Check if the user has an active story
            $hasStory = false;
            $storySlug = null;
            $latestStory = $user->activeStories()->latest()->first();
            if ($latestStory) {
                $hasStory = true;
                $storySlug = $latestStory->slug;
            }

            return response()->json([
                'success' => true,
                'following' => $currentUser->isFollowing($user),
                'followers_count' => $user->followers()->count(),
                'user' => [
                    'has_story' => $hasStory,
                    'story_slug' => $storySlug,
                    'avatar_url' => $user->avatar_url
                ]
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
                    'message' => __('messages.cannot_block_admin')
                ], 403);
            }
            return back()->with('error', __('messages.cannot_block_admin'));
        }

        $currentUser = auth()->user();
        
        if ($currentUser->isBlocking($user)) {
            $currentUser->blockedUsers()->where('blocked_id', $user->id)->delete();
        } else {
            $currentUser->blockedUsers()->create(['blocked_id' => $user->id]);
            $currentUser->follows()->where('followed_id', $user->id)->delete();
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            $isBlocking = $currentUser->isBlocking($user);
            return response()->json([
                'success' => true,
                'blocking' => $isBlocking,
                'message' => $isBlocking ? __('messages.user_blocked') : __('messages.user_unblocked')
            ]);
        }

        return back();
    }

    public function followers(User $user)
    {
        // Eager load follower profiles and pre-calculate follow status
        $followers = $user->followers()->with('follower.profile')->get();
        
        // Pre-calculate which followers the current user is following
        $followingIds = [];
        if (auth()->check()) {
            $followingIds = auth()->user()->follows()->pluck('followed_id')->toArray();
        }
        
        return view('users.followers', compact('user', 'followers', 'followingIds'));
    }

    public function following(User $user)
    {
        // Eager load followed profiles and pre-calculate follow status
        $following = $user->follows()->with('followed.profile')->get();
        
        // Pre-calculate which users the current user is following
        $followingIds = [];
        if (auth()->check()) {
            $followingIds = auth()->user()->follows()->pluck('followed_id')->toArray();
        }
        
        return view('users.following', compact('user', 'following', 'followingIds'));
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
            return redirect()->route('home');
        }

        // Get all users except current user with relationships
        $users = User::where('id', '!=', $currentUser->id)
            ->with(['profile'])
            ->withCount(['followers', 'follows'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get blocked user IDs for current user (single query)
        $blockedByCurrentUser = $currentUser->blockedUsers()->pluck('blocked_id')->toArray();
        $blockedCurrentUser = Block::where('blocked_id', $currentUser->id)->pluck('blocker_id')->toArray();

        // Pre-calculate following IDs (single query instead of N queries in view)
        $followingIds = $currentUser->follows()->pluck('followed_id')->toArray();

        return view('users.explore', compact('users', 'blockedByCurrentUser', 'blockedCurrentUser', 'followingIds'));
    }

    public function searchPage()
    {
        if (!auth()->check()) {
            return redirect()->route('home');
        }
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:users,username,' . $user->id,
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
            'name' => $request->name,
            'username' => $request->username,
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

        return redirect()->route('users.show', $user)->with('success', __('messages.profile_updated'));
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

            return response()->json(['success' => true, 'message' => __('messages.avatar_deleted')]);
        }

        return response()->json(['success' => false, 'message' => __('messages.no_avatar_to_delete')], 400);
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

            return response()->json(['success' => true, 'message' => __('messages.cover_deleted')]);
        }

        return response()->json(['success' => false, 'message' => __('messages.no_cover_to_delete')], 400);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        // Validate password confirmation for security
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

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
            $userId = $user->id;
            $userEmail = $user->email;
            
            // Clear remember token before deletion
            $user->remember_token = null;
            $user->save();
            
            $user->delete();
            
            // Verify user was actually deleted
            $deletedUser = User::find($userId);
            if ($deletedUser) {
                \Log::error('User deletion failed - user still exists: ' . $userId);
                \DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => __('messages.account_delete_failed')
                ], 500);
            }
            
            \Log::info('Deleted user account: ' . $userId . ' (' . $userEmail . ')');

            // Commit the transaction
            \DB::commit();
            
            // Clear query cache to ensure fresh data on next request
            \DB::purge();

            // Log out the user and invalidate session
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Clear any remember me cookie
            if ($request->hasCookie('remember_web_user')) {
                \Cookie::queue(\Cookie::forget('remember_web_user'));
            }

            \Log::info('Account deletion complete, session invalidated for user: ' . $userId);

            return response()->json([
                'success' => true,
                'message' => __('messages.account_deleted')
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
                'message' => __('messages.account_delete_failed')
            ], 500);
        }
    }

    /**
     * Update user's online status (ping for chat)
     */
    public function updateOnlineStatus(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => __('messages.unauthenticated')], 401);
        }

        // Update last active timestamp and set online status
        $user->update([
            'last_active' => now(),
            'is_online' => true
        ]);

        return response()->json([
            'success' => true,
            'is_online' => true,
            'last_active' => now()->toISOString()
        ]);
    }

    /**
     * Set user as offline (when closing browser)
     */
    public function setOfflineStatus(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => __('messages.unauthenticated')], 401);
        }

        // Set user as offline
        $user->update([
            'is_online' => false
        ]);

        return response()->json([
            'success' => true,
            'is_online' => false
        ]);
    }

    /**
     * Get user's online status
     */
    public function getOnlineStatus($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => __('messages.user_not_found')], 404);
        }

        // Consider user offline if last active more than 30 seconds ago
        $isOnline = $user->is_online && $user->last_active && $user->last_active->diffInSeconds(now()) < 30;
        
        // Auto-update is_online flag if user has been inactive for more than 30 seconds
        if (!$isOnline && $user->is_online) {
            $user->update(['is_online' => false]);
        }

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'is_online' => $isOnline,
            'last_active' => $user->last_active ? \Carbon\Carbon::parse($user->last_active)->toISOString() : null,
            'last_active_human' => $user->last_active ? __('chat.last_active') . ' ' . \Carbon\Carbon::parse($user->last_active)->diffForHumans() : null
        ]);
    }

    /**
     * Get online status for multiple users (for chat list)
     */
    public function getMultipleOnlineStatus(Request $request)
    {
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return response()->json(['success' => true, 'statuses' => []]);
        }

        $users = User::whereIn('id', $userIds)->get(['id', 'is_online', 'last_active']);
        $statuses = [];
        $usersToUpdate = [];

        foreach ($users as $user) {
            $isOnline = $user->is_online && $user->last_active && $user->last_active->diffInSeconds(now()) < 30;

            // Mark for update if still marked online but inactive
            if (!$isOnline && $user->is_online) {
                $usersToUpdate[] = $user->id;
            }

            $statuses[$user->id] = [
                'is_online' => $isOnline,
                'last_active' => $user->last_active ? \Carbon\Carbon::parse($user->last_active)->toISOString() : null,
                'last_active_human' => $user->last_active ? __('chat.last_active') . ' ' . \Carbon\Carbon::parse($user->last_active)->diffForHumans() : null
            ];
        }

        // Bulk update offline status for inactive users
        if (!empty($usersToUpdate)) {
            User::whereIn('id', $usersToUpdate)->update(['is_online' => false]);
        }

        return response()->json([
            'success' => true,
            'statuses' => $statuses
        ]);
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
                'username' => $user->username
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.user_not_found')
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

    /**
     * Check current user's account status (for background polling)
     * Returns status info for: suspended, deleted, logged out, concurrent login, etc.
     */
    public function checkAccountStatus()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'logged_out',
                'message' => __('messages.unauthenticated'),
                'redirect' => route('login.view')
            ]);
        }

        // Refresh user to get latest data from database
        $user = User::find($user->id);

        // Check if user was deleted
        if (!$user) {
            return app(\App\Http\Controllers\Auth\LoginController::class)->logoutWithMessage(request(), 'deleted');
        }

        // Check if user is suspended
        if ($user->is_suspended) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->json([
                'status' => 'suspended',
                'message' => __('messages.account_suspended_api'),
                'redirect' => route('auth.suspended')
            ], 403);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'unverified',
                'message' => __('messages.please_verify_email_api'),
                'redirect' => route('verification.notice')
            ], 403);
        }

        // Check for concurrent login (different session ID)
        $currentSessionId = request()->session()->getId();
        $storedSessionId = session('session_id');

        if ($storedSessionId && $storedSessionId !== $currentSessionId) {
            // Another session exists - check if it's newer
            $lastActivity = session('last_activity');
            if ($lastActivity && $lastActivity > now()->subMinutes(5)->timestamp) {
                // Another active session detected - logout current session for security
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return response()->json([
                    'status' => 'concurrent_login',
                    'message' => __('messages.concurrent_session'),
                    'redirect' => route('login.view')
                ], 403);
            }
        }

        // Update session tracking
        session([
            'session_id' => $currentSessionId,
            'last_activity' => now()->timestamp
        ]);
        
        return response()->json([
            'status' => 'active',
            'message' => __('messages.account_active')
        ]);
    }
}
