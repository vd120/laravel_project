<?php

namespace App\Http\Controllers;

use App\Events\StoryDeleted;
use App\Events\StoryReacted;
use App\Events\StoryUnreacted;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class StoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get users that current user follows who have active stories
        $followedUsersWithStories = User::whereHas('followers', function($query) use ($user) {
            $query->where('follower_id', $user->id);
        })->whereHas('activeStories')->with(['activeStories'])->get();

        // Also include current user's stories
        $myStories = $user->activeStories;

        return view('stories.index', compact('followedUsersWithStories', 'myStories'));
    }

    public function create()
    {
        return view('stories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:51200', // 50MB max
            'content' => 'nullable|string|max:280',
        ]);

        $file = $request->file('media');
        $mimeType = $file->getMimeType();

        if (str_contains($mimeType, 'image/')) {
            // Handle image upload with compression
            $manager = new ImageManager(new Driver());
            $compressedImage = $manager->read($file);

            // Compress image
            $maxWidth = 1080;
            $maxHeight = 1920;
            $quality = 85;

            // Resize if too large
            if ($compressedImage->width() > $maxWidth || $compressedImage->height() > $maxHeight) {
                $compressedImage->scale(width: $maxWidth, height: $maxHeight);
            }

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'stories/images/' . $filename;

            // Ensure directory exists
            $directory = dirname(storage_path('app/public/' . $path));
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save compressed image
            $compressedImage->toJpeg($quality)->save(storage_path('app/public/' . $path));

            $mediaType = 'image';
        } elseif (str_contains($mimeType, 'video/')) {
            // Handle video upload
            $videoMimeTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/webm'];

            if (!in_array($mimeType, $videoMimeTypes)) {
                return back()->withErrors(['media' => 'Invalid video format.']);
            }

            if ($file->getSize() > 52428800) { // 50MB in bytes
                return back()->withErrors(['media' => 'Video file too large.']);
            }

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'stories/videos/' . $filename;

            // Ensure directory exists
            $directory = storage_path('app/public/stories/videos');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);
            $mediaType = 'video';
        } else {
            return back()->withErrors(['media' => 'Invalid file type.']);
        }

        // Create story with 24-hour expiration
        Story::create([
            'user_id' => auth()->id(),
            'media_type' => $mediaType,
            'media_path' => $path,
            'content' => $request->content,
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('stories.index');
    }

    public function show(User $user, Request $request)
    {
        // Check if current user can view this user's stories
        $currentUser = auth()->user();

        if ($user->id !== $currentUser->id && !$currentUser->isFollowing($user)) {
            abort(403, 'You can only view stories from users you follow.');
        }

        $stories = $user->activeStories()->get();

        // If a specific story is requested, reorder the collection so it comes first
        if ($request->has('story')) {
            $requestedStoryId = $request->get('story');
            $stories = $stories->sortBy(function ($story) use ($requestedStoryId) {
                return $story->id == $requestedStoryId ? 0 : 1;
            })->values();

            // Increment view count for the requested story (only if it's not the author's own story and user hasn't viewed it before)
            $requestedStory = $stories->first();
            if ($requestedStory && $requestedStory->user_id !== $currentUser->id) {
                // Check if this user has already viewed this story
                $existingView = \App\Models\StoryView::where('user_id', $currentUser->id)
                    ->where('story_id', $requestedStory->id)
                    ->exists();

                if (!$existingView) {
                    // Record the view and increment the count
                    \App\Models\StoryView::create([
                        'user_id' => $currentUser->id,
                        'story_id' => $requestedStory->id
                    ]);
                    $requestedStory->increment('views');
                }
            }
        }

        return view('stories.show', compact('user', 'stories'));
    }

    public function viewers(User $user, Story $story)
    {
        // Check if current user is the story author
        if ($story->user_id !== auth()->id()) {
            abort(403, 'You can only view viewers of your own stories.');
        }

        // Get all users who viewed this story with their view timestamps and reactions
        $viewers = \App\Models\StoryView::where('story_id', $story->id)
            ->with(['user.profile', 'user.storyReactions' => function($query) use ($story) {
                $query->where('story_id', $story->id);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the data to include reaction info
        $viewerData = $viewers->map(function($viewer) {
            return [
                'user' => $viewer->user,
                'viewed_at' => $viewer->created_at,
                'reaction' => $viewer->user->storyReactions->first()?->reaction_type
            ];
        });

        return view('stories.viewers', compact('user', 'story', 'viewerData'));
    }

    public function react(Request $request, Story $story)
    {
        $request->validate([
            'reaction_type' => 'required|string|max:10'
        ]);

        $user = auth()->user();

        // Check if user can view this story
        if ($story->user_id !== $user->id && !$user->isFollowing($story->user)) {
            abort(403, 'You can only react to stories from users you follow.');
        }

        // Check if user already reacted to this story
        $existingReaction = \App\Models\StoryReaction::where('user_id', $user->id)
            ->where('story_id', $story->id)
            ->first();

        if ($existingReaction) {
            // Update existing reaction
            $existingReaction->update(['reaction_type' => $request->reaction_type]);
            broadcast(new StoryReacted($existingReaction))->toOthers();
        } else {
            // Create new reaction
            $newReaction = \App\Models\StoryReaction::create([
                'user_id' => $user->id,
                'story_id' => $story->id,
                'reaction_type' => $request->reaction_type
            ]);
            broadcast(new StoryReacted($newReaction))->toOthers();
        }

        return response()->json(['success' => true]);
    }

    public function unreact(Story $story)
    {
        $user = auth()->user();

        // Check if user can view this story
        if ($story->user_id !== $user->id && !$user->isFollowing($story->user)) {
            abort(403, 'You can only unreact to stories from users you follow.');
        }

        // Remove user's reaction from this story
        \App\Models\StoryReaction::where('user_id', $user->id)
            ->where('story_id', $story->id)
            ->delete();

        broadcast(new StoryUnreacted(
            $story->id,
            $user->id,
            $user->name,
            $story->reactions()->count()
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function destroy(Story $story)
    {
        if ($story->user_id !== auth()->id()) {
            abort(403);
        }

        $storyId = $story->id;
        $userId = $story->user_id;
        $userName = $story->user->name;

        // Delete the media file
        Storage::disk('public')->delete($story->media_path);

        $story->delete();

        // Broadcast the deletion event
        broadcast(new StoryDeleted($storyId, $userId, $userName))->toOthers();

        // Log for debugging
        \Log::info('Story deleted', [
            'story_id' => $storyId,
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        // Check if it's an AJAX request
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Story deleted successfully'
            ]);
        }

        return back();
    }
}
