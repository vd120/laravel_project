<?php

namespace App\Http\Controllers;

use App\Events\PostLiked;
use App\Events\PostUnliked;
use App\Models\Post;
use App\Models\SavedPost;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 10); // Default 10 posts per page for web view

        if ($user) {
            // Authenticated user logic
            // Get paginated posts, but filter based on privacy settings and blocks
            $posts = Post::with(['user', 'media', 'likes', 'savedPosts', 'comments.replies.user', 'comments.likes'])
                ->where(function($query) use ($user) {
                    // Always show public posts from non-private accounts
                    $query->where('is_private', false)
                          ->whereHas('user.profile', function($profileQuery) {
                              $profileQuery->where('is_private', false);
                          })
                    // Or show private posts from non-private accounts that the user follows
                    ->orWhere(function($subQuery) use ($user) {
                        $subQuery->where('is_private', true)
                                 ->whereHas('user.profile', function($profileQuery) {
                                     $profileQuery->where('is_private', false);
                                 })
                                 ->whereHas('user.followers', function($followerQuery) use ($user) {
                                     $followerQuery->where('follower_id', $user->id);
                                 });
                    })
                    // Or show posts from private accounts that the user follows (both account and post level)
                    ->orWhere(function($subQuery) use ($user) {
                        $subQuery->whereHas('user.profile', function($profileQuery) {
                            $profileQuery->where('is_private', true);
                        })
                        ->whereHas('user.followers', function($followerQuery) use ($user) {
                            $followerQuery->where('follower_id', $user->id);
                        });
                    })
                    // Or show the user's own posts
                    ->orWhere('user_id', $user->id);
                })
                // Exclude posts from users that blocked the current user or that the current user blocked
                ->whereDoesntHave('user.blockedBy', function($blockedByQuery) use ($user) {
                    $blockedByQuery->where('blocker_id', $user->id);
                })
                ->whereDoesntHave('user.blockedUsers', function($blockedUsersQuery) use ($user) {
                    $blockedUsersQuery->where('blocked_id', $user->id);
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(); // Preserve query parameters

            // Get active stories from followed users and current user
            $followedUsersWithStories = \App\Models\User::whereHas('followers', function($query) use ($user) {
                $query->where('follower_id', $user->id);
            })->whereHas('activeStories')->with(['activeStories'])->get();

            // Include current user's stories
            $myStories = $user->activeStories;
        } else {
            // Guest user logic - show only public posts from non-private accounts
            $posts = Post::with(['user', 'media', 'likes', 'comments.replies.user', 'comments.likes'])
                ->where('is_private', false)
                ->whereHas('user.profile', function($profileQuery) {
                    $profileQuery->where('is_private', false);
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            // No stories for guests
            $followedUsersWithStories = collect();
            $myStories = collect();
        }

        return view('posts.index', compact('posts', 'followedUsersWithStories', 'myStories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:280',
            'media' => 'nullable|array|max:10', // Allow up to 10 files
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:51200', // 50MB max, added webm
        ]);

        // Ensure at least content or media is provided
        if (!$request->filled('content') && !$request->hasFile('media')) {
            return back()->withErrors(['content' => 'Please provide either text content or media.']);
        }

        $postData = $request->only('content');
        $postData['is_private'] = $request->boolean('is_private');

        // Remove old single media fields since we're using the new media table
        unset($postData['media_type'], $postData['media_path'], $postData['media_thumbnail']);

        $post = auth()->user()->posts()->create($postData);

        // Process mentions in the post content
        if ($post->content) {
            app(\App\Services\MentionService::class)->processMentions($post, $post->content, auth()->id());
        }

        // Handle multiple media uploads
        if ($request->hasFile('media')) {
            $files = $request->file('media');
            $sortOrder = 0;

            foreach ($files as $file) {
                $mimeType = $file->getMimeType();
                $originalName = $file->getClientOriginalName();

                if (str_contains($mimeType, 'image/')) {
                    // Handle image upload with compression
                    $manager = new \Intervention\Image\ImageManager(
                        new \Intervention\Image\Drivers\Gd\Driver()
                    );
                    $compressedImage = $manager->read($file);

                    // Compress image based on quality and size
                    $maxWidth = 1200;
                    $maxHeight = 1200;
                    $quality = 85; // Good balance of quality vs size

                    // Resize if too large
                    if ($compressedImage->width() > $maxWidth || $compressedImage->height() > $maxHeight) {
                        $compressedImage->scale(width: $maxWidth, height: $maxHeight);
                    }

                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = 'posts/images/' . $filename;

                    // Save compressed image
                    $compressedImage->toJpeg($quality)->save(storage_path('app/public/' . $path));

                    $post->media()->create([
                        'media_type' => 'image',
                        'media_path' => $path,
                        'sort_order' => $sortOrder++
                    ]);
                } elseif (str_contains($mimeType, 'video/')) {
                    // Enhanced video validation
                    $videoMimeTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/webm'];

                    if (!in_array($mimeType, $videoMimeTypes)) {
                        continue; // Skip invalid video types
                    }

                    // Check video size (50MB max)
                    if ($file->getSize() > 52428800) { // 50MB in bytes
                        continue; // Skip oversized videos
                    }

                    // Handle video upload
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = 'posts/videos/' . $filename;

                    // Move file to storage
                    $file->move(storage_path('app/public/posts/videos'), $filename);

                    $post->media()->create([
                        'media_type' => 'video',
                        'media_path' => $path,
                        'media_thumbnail' => null, // Could generate thumbnail here
                        'sort_order' => $sortOrder++
                    ]);
                }
            }
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'post' => $post->load('user', 'media'),
                'message' => 'Post created successfully'
            ]);
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        // Check if user can view this post (privacy and blocks)
        $user = auth()->user();

        // Check post-level privacy first
        if ($post->is_private) {
            $isFollowing = $user->isFollowing($post->user);
            $isOwner = $post->user_id === $user->id;

            if (!$isFollowing && !$isOwner) {
                abort(403, 'This post is private. Only followers can view it.');
            }
        }

        // If post is from private account and user doesn't follow them (account-level privacy)
        if ($post->user->profile && $post->user->profile->is_private) {
            $isFollowing = $user->isFollowing($post->user);
            $isOwner = $post->user_id === $user->id;

            if (!$isFollowing && !$isOwner) {
                abort(403, 'This post is from a private account. Follow the user to view their posts.');
            }
        }

        // Check if user is blocked by the post author or has blocked the post author
        if ($user->isBlocking($post->user) || $post->user->isBlocking($user)) {
            abort(403, 'You cannot view this post due to blocking restrictions.');
        }

        $post->load(['user', 'media', 'comments.replies.user', 'comments.likes']);
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $oldContent = $post->content;
        $post->update($request->only('content'));

        // Process mentions if content changed
        if ($oldContent !== $post->content && $post->content) {
            // Remove old mentions for this post
            \App\Models\Mention::where('mentionable_type', \App\Models\Post::class)
                ->where('mentionable_id', $post->id)
                ->delete();

            // Process new mentions
            app(\App\Services\MentionService::class)->processMentions($post, $post->content, auth()->id());
        }

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            abort(403);
        }
        $post->delete();

    // Check if it's an AJAX request
    if (request()->expectsJson()) {
        return response()->json([
            'success' => true
        ]);
    }

        return back();
    }

    public function like(Post $post)
    {
        $user = auth()->user();
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            // Refresh the post model to get updated relationships
            $post->refresh();
            broadcast(new PostUnliked(
                $post->id,
                $user->id,
                $user->name,
                $post->likes()->count()
            ))->toOthers();
        } else {
            $newLike = $post->likes()->create(['user_id' => $user->id]);
            // Refresh the post model to get updated relationships
            $post->refresh();

            // Create notification for post owner (if not liking own post)
            if ($post->user_id !== $user->id) {
                \App\Models\Notification::create([
                    'user_id' => $post->user_id,
                    'type' => 'like',
                    'data' => [
                        'liker_name' => $user->name,
                        'liker_id' => $user->id,
                        'post_content' => substr($post->content ?? 'Image post', 0, 50)
                    ],
                    'related_type' => \App\Models\Post::class,
                    'related_id' => $post->id
                ]);
            }

            broadcast(new PostLiked($newLike))->toOthers();
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'liked' => !$like,
                'likes_count' => $post->likes()->count()
            ]);
        }

        return back();
    }

    public function save(Post $post)
    {
        $saved = SavedPost::where('user_id', auth()->id())->where('post_id', $post->id)->first();
        if ($saved) {
            $saved->delete();
            // Refresh the post model to get updated relationships
            $post->refresh();
        } else {
            SavedPost::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ]);
            // Refresh the post model to get updated relationships
            $post->refresh();
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'saved' => !$saved
            ]);
        }

        return back();
    }


}
