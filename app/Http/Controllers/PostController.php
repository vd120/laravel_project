<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SavedPost;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        if ($user) {
            $posts = Post::with(['user', 'media', 'likes', 'savedPosts', 'comments.replies.user', 'comments.likes'])
                ->where(function($query) use ($user) {
                    $query->where(function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->orWhere(function($q) use ($user) {
                            $q->whereHas('user.followers', function($followerQuery) use ($user) {
                                $followerQuery->where('follower_id', $user->id);
                            });
                        })
                        ->orWhere(function($q) use ($user) {
                            $q->where('is_private', false);
                        });
                })
                ->whereDoesntHave('user.blockedBy', function($blockedByQuery) use ($user) {
                    $blockedByQuery->where('blocker_id', $user->id);
                })
                ->whereDoesntHave('user.blockedUsers', function($blockedUsersQuery) use ($user) {
                    $blockedUsersQuery->where('blocked_id', $user->id);
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            $followedUsersWithStories = \App\Models\User::whereHas('followers', function($query) use ($user) {
                $query->where('follower_id', $user->id);
            })->whereHas('activeStories')->with(['activeStories'])->get();

            $myStories = $user->activeStories;
        } else {
            $posts = Post::with(['user', 'media', 'likes', 'comments.replies.user', 'comments.likes'])
                ->where('is_private', false)
                ->whereHas('user.profile', function($profileQuery) {
                    $profileQuery->where('is_private', false);
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

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
    public function store(Request $request, FileUploadService $fileService)
    {
        // Check if POST data exceeded PHP limits
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
            return back()
                ->withInput()
                ->withErrors(['media' => __('messages.post_too_large')]);
        }
        
        $request->validate([
            'content' => 'nullable|string|max:280',
            'media' => 'nullable|array|max:30', // Allow up to 30 files
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:51200', // 50MB max, added webm
        ]);

        // Ensure at least content or media is provided
        if (!$request->filled('content') && !$request->hasFile('media')) {
            return back()->withErrors(['content' => 'Please provide either text content or media.']);
        }

        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'
        ];

        // SECURITY FIX: Validate all files before processing
        $validatedFiles = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $validation = $fileService->validateFile($file, $allowedMimeTypes);
                
                if (!$validation['valid']) {
                    return back()->withErrors([
                        'media.' . $index => implode(', ', $validation['errors'])
                    ])->withInput();
                }
                
                $validatedFiles[] = $file;
            }
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

        // Handle multiple media uploads with validated files
        if ($validatedFiles) {
            $files = $validatedFiles;
            $sortOrder = 0;

            foreach ($files as $file) {
                $mimeType = $file->getMimeType();
                $originalName = $file->getClientOriginalName();

                if (str_contains($mimeType, 'image/')) {
                    // Handle image upload with FAST compression
                    try {
                        $manager = new \Intervention\Image\ImageManager(
                            new \Intervention\Image\Drivers\Gd\Driver()
                        );
                        $compressedImage = $manager->read($file);

                        // FAST compression: smaller max size + lower quality
                        $maxWidth = 800;  // Reduced from 1200 for speed
                        $maxHeight = 800; // Reduced from 1200 for speed
                        $quality = 75;    // Reduced from 85 for speed

                        // Resize if too large
                        if ($compressedImage->width() > $maxWidth || $compressedImage->height() > $maxHeight) {
                            $compressedImage->scale(width: $maxWidth, height: $maxHeight);
                        }

                        // Generate unique filename
                        $filename = time() . '_' . uniqid() . '.jpg'; // Always use .jpg for speed
                        $path = 'posts/images/' . $filename;

                        // Save compressed image (fastest settings)
                        $compressedImage->toJpeg($quality)->save(storage_path('app/public/' . $path));
                    } catch (\Exception $e) {
                        // If compression fails, save the original file
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = 'posts/images/' . $filename;
                        $file->move(storage_path('app/public/posts/images'), $filename);
                    }

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
            $post->load('user', 'media');
            if ($post->user) {
                $post->user->append('avatar_url');
            }

            return response()->json([
                'success' => true,
                'post' => $post,
                'message' => __('messages.post_created')
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

        // Ensure avatar_url is present for the post author in JSON
        $post->load('user');
        if ($post->user) {
            $post->user->append('avatar_url');
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
        } else {
            $post->likes()->create(['user_id' => $user->id]);

            // Create notification for post owner (if not liking own post)
            if ($post->user_id !== $user->id) {
                \App\Models\Notification::create([
                    'user_id' => $post->user_id,
                    'type' => 'like',
                    'data' => [
                        'liker_name' => $user->username ?? $user->name ?? 'Someone',
                        'liker_username' => $user->username ?? 'Unknown',
                        'liker_id' => $user->id,
                        'post_content' => substr($post->content ?? 'Image post', 0, 50)
                    ],
                    'related_type' => \App\Models\Post::class,
                    'related_id' => $post->id
                ]);
            }
        }

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            $recentLikers = $post->likes()->with('user:id,name')->latest()->limit(10)->get()->map(function($like) {
                return [
                    'id' => $like->user->id,
                    'username' => $like->user->username
                ];
            });

            return response()->json([
                'success' => true,
                'liked' => !$like,
                'likes_count' => $post->likes()->count(),
                'likers' => $recentLikers
            ]);
        }

        return back();
    }

    public function save(Post $post)
    {
        $saved = SavedPost::where('user_id', auth()->id())->where('post_id', $post->id)->first();
        if ($saved) {
            $saved->delete();
        } else {
            SavedPost::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ]);
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

    public function getLikers(Post $post)
    {
        $user = auth()->user();

        // Check if user can view the post (privacy and blocks)
        if ($post->is_private && !$user->isFollowing($post->user) && $post->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => __('messages.cannot_view_private_post')]);
        }

        if ($user->isBlocking($post->user) || $post->user->isBlocking($user)) {
            return response()->json(['success' => false, 'message' => __('messages.blocking_restriction')]);
        }

        // Get users who liked this post
        $likers = $post->likes()
            ->with('user:id,name,username')
            ->with('user.profile:id,user_id,avatar,bio')
            ->get()
            ->map(function ($like) use ($user) {
                $liker = $like->user;
                $profile = $liker->profile;

                return [
                    'id' => $liker->id,
                    'username' => $liker->username,
                    'avatar' => $liker->avatar_url,
                    'bio' => $profile ? $profile->bio : null,
                    'can_follow' => $liker->id !== $user->id && !$user->isFollowing($liker) && !$liker->isBlocking($user),
                    'is_following' => $user->isFollowing($liker)
                ];
            });

        return response()->json([
            'success' => true,
            'likers' => $likers
        ]);
    }


}