<?php

namespace App\Http\Controllers;

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

            // Get trim values if provided (max 60 seconds)
            $trimStart = $request->input('trim_start', 0);
            $trimEnd = $request->input('trim_end', 60);
            
            // Ensure trim values are valid
            $trimStart = max(0, floatval($trimStart));
            $trimEnd = min(60, max($trimStart + 1, floatval($trimEnd)));
            
            // Determine if trimming is needed
            $needsTrimming = ($trimEnd - $trimStart) < 60 || $trimStart > 0 || $trimEnd < 60;
            
            $filename = time() . '_' . uniqid() . '.mp4';
            $path = 'stories/videos/' . $filename;

            // Ensure directory exists
            $directory = storage_path('app/public/stories/videos');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($needsTrimming && $trimEnd - $trimStart > 0) {
                // Use ffmpeg to trim the video
                $tempPath = $file->storeAs('temp', 'temp_' . time() . '.' . $file->getClientOriginalExtension());
                $tempFullPath = storage_path('app/' . $tempPath);
                $outputPath = storage_path('app/public/' . $path);
                
                // Build ffmpeg command to trim video
                $duration = $trimEnd - $trimStart;
                $command = sprintf(
                    'ffmpeg -i "%s" -ss %.3f -t %.3f -c:v libx264 -c:a aac -strict experimental -movflags +faststart "%s" 2>&1',
                    $tempFullPath,
                    $trimStart,
                    $duration,
                    $outputPath
                );
                
                exec($command, $output, $returnVar);
                
                // Clean up temp file
                if (file_exists($tempFullPath)) {
                    unlink($tempFullPath);
                }
                
                if ($returnVar !== 0 || !file_exists($outputPath)) {
                    // If ffmpeg fails, fall back to original file
                    $file->move($directory, $filename);
                }
            } else {
                // No trimming needed, just move the file
                $file->move($directory, $filename);
            }
            
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

        return redirect()->route('stories.index')->with('success', __('messages.story_posted'));
    }

    public function show(User $user, Story $story, Request $request)
    {
        // Check if current user can view this user's stories
        $currentUser = auth()->user();

        if ($user->id !== $currentUser->id && !$currentUser->isFollowing($user)) {
            abort(403, 'You can only view stories from users you follow.');
        }

        // Get all active stories from this user with their views
        $stories = $user->activeStories()->with('storyViews')->get();

        // If a specific story is requested via route parameter, prioritize it but show all stories
        if ($story && $story->user_id === $user->id) {
            // Mark story as viewed
            if ($story->user_id !== $currentUser->id) {
                // Check if this user has already viewed this story
                $existingView = \App\Models\StoryView::where('user_id', $currentUser->id)
                    ->where('story_id', $story->id)
                    ->exists();

                if (!$existingView) {
                    // Record the view and increment the count
                    \App\Models\StoryView::create([
                        'user_id' => $currentUser->id,
                        'story_id' => $story->id
                    ]);
                    $story->increment('views');
                }
            }

            // Sort stories to show the requested one first
            $stories = $stories->sortBy(function ($s) use ($story) {
                return $s->id == $story->id ? 0 : 1;
            })->values();
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
            ->with(['user.profile'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the data to include reaction info
        $viewerData = $viewers->map(function($viewer) use ($story) {
            // Get reaction for this specific story
            $reaction = \App\Models\StoryReaction::where('user_id', $viewer->user_id)
                ->where('story_id', $story->id)
                ->first();
            
            return [
                'user' => $viewer->user,
                'viewed_at' => $viewer->created_at,
                'reaction' => $reaction ? $reaction->reaction_type : null
            ];
        });

        return view('stories.viewers', compact('user', 'story', 'viewerData'));
    }

    public function react(string $user, Request $request, Story $story)
    {
        $authUser = auth()->user();

        // Check if user can view this story
        if ($story->user_id !== $authUser->id && !$authUser->isFollowing($story->user)) {
            abort(403, 'You can only react to stories from users you follow.');
        }

        // Check if user already reacted to this story
        $existingReaction = \App\Models\StoryReaction::where('user_id', $authUser->id)
            ->where('story_id', $story->id)
            ->first();

        if ($existingReaction) {
            // Update existing reaction
            $existingReaction->update(['reaction_type' => $request->reaction_type]);
        } else {
            // Create new reaction
            $newReaction = \App\Models\StoryReaction::create([
                'user_id' => $authUser->id,
                'story_id' => $story->id,
                'reaction_type' => $request->reaction_type
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function removeReaction(string $user, Story $story)
    {
        $authUser = auth()->user();

        // Delete user's reaction from this story
        \App\Models\StoryReaction::where('user_id', $authUser->id)
            ->where('story_id', $story->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getReactions(string $user, Story $story)
    {
        // Get all reactions for this story
        $reactions = \App\Models\StoryReaction::where('story_id', $story->id)
            ->with(['user.profile'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($reaction) {
                return [
                    'user_id' => $reaction->user_id,
                    'user_username' => $reaction->user->username,
                    'user_avatar' => $reaction->user->avatar_url ?? null,
                    'reaction_type' => $reaction->reaction_type
                ];
            });

        return response()->json([
            'success' => true,
            'reactions' => $reactions
        ]);
    }

    public function checkReaction(string $user, Story $story)
    {
        $authUser = auth()->user();

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'has_reaction' => false
            ], 401);
        }

        // Check if user has already reacted to this story
        $hasReaction = \App\Models\StoryReaction::where('user_id', $authUser->id)
            ->where('story_id', $story->id)
            ->exists();

        return response()->json([
            'success' => true,
            'has_reaction' => $hasReaction
        ]);
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

        return response()->json(['success' => true]);
    }

    public function destroy(string $user, Story $story)
    {
        if ($story->user_id !== auth()->id()) {
            abort(403);
        }

        $storyId = $story->id;
        $userId = $story->user_id;
        $userName = $story->user->username;

        // Delete the media file
        Storage::disk('public')->delete($story->media_path);

        $story->delete();

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
                'message' => __('messages.story_deleted')
            ]);
        }

        return back();
    }
}
