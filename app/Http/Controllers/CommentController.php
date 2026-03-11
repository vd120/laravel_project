<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:280',
            'post_id' => 'required|exists:posts,id',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $currentUser = auth()->user();
        $post = Post::findOrFail($request->post_id);
        
        // CRITICAL FIX: Check if commenter is blocked by post owner
        if ($post->user->isBlocking($currentUser)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.post_owner_has_blocked_you')
                ], 403);
            }
            return back()->with('error', __('messages.post_owner_has_blocked_you'));
        }
        
        // Check if commenter has blocked post owner
        if ($currentUser->isBlocking($post->user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.you_have_blocked_this_user')
                ], 403);
            }
            return back()->with('error', __('messages.you_have_blocked_this_user'));
        }
        
        // If replying to a comment, check block status with parent comment author
        if ($request->parent_id) {
            $parentComment = Comment::findOrFail($request->parent_id);
            
            if ($parentComment->user->isBlocking($currentUser)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.comment_author_has_blocked_you')
                    ], 403);
                }
                return back()->with('error', __('messages.comment_author_has_blocked_you'));
            }
            
            if ($currentUser->isBlocking($parentComment->user)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.you_have_blocked_this_user')
                    ], 403);
                }
                return back()->with('error', __('messages.you_have_blocked_this_user'));
            }
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Process mentions in the comment content
        app(\App\Services\MentionService::class)->processMentions($comment, $comment->content, auth()->id());

        // Create notification for post owner (if not commenting on own post)
        if ($comment->post->user_id !== auth()->id()) {
            \App\Models\Notification::create([
                'user_id' => $comment->post->user_id,
                'type' => 'comment',
                'data' => [
                    'commenter_name' => auth()->user()->username ?? auth()->user()->name ?? 'Someone',
                    'commenter_username' => auth()->user()->username ?? 'Unknown',
                    'commenter_id' => auth()->id(),
                    'comment_content' => substr($comment->content, 0, 50) . (strlen($comment->content) > 50 ? '...' : ''),
                    'post_content' => substr($comment->post->content ?? 'Image post', 0, 30)
                ],
                'related_type' => \App\Models\Comment::class,
                'related_id' => $comment->id
            ]);
        }

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            $commentData = $comment->load(['user.profile']);
            // Ensure accessor-based attributes like avatar_url are included in JSON
            if ($commentData->user) {
                $commentData->user->append('avatar_url');
            }
            $commentData->content = app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content);

            return response()->json([
                'success' => true,
                'comment' => $commentData,
                'message' => __('messages.comment_posted')
            ]);
        }

        return back();
    }

    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $oldContent = $comment->content;
        $comment->update($request->only('content'));

        // Process mentions if content changed
        if ($oldContent !== $comment->content && $comment->content) {
            // Remove old mentions for this comment
            \App\Models\Mention::where('mentionable_type', \App\Models\Comment::class)
                ->where('mentionable_id', $comment->id)
                ->delete();

            // Process new mentions
            app(\App\Services\MentionService::class)->processMentions($comment, $comment->content, auth()->id());
        }

        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $postId = $comment->post_id;
        $commentId = $comment->id;
        $comment->delete();

        // Check if it's an AJAX request
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true
            ]);
        }

        return back();
    }

    public function like(Comment $comment)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.please_login')
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        try {
            $like = $comment->likes()->where('user_id', $user->id)->first();

            if ($like) {
                $like->delete();
                $comment->refresh();
            } else {
                $newLike = CommentLike::create(['user_id' => $user->id, 'comment_id' => $comment->id]);
                $comment->refresh();
            }

            // Check if it's an AJAX request
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'liked' => !$like,
                    'likes_count' => $comment->likes()->count()
                ]);
            }

            return back();
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ], 500);
            }
            return back()->with('error', __('messages.error_occurred'));
        }
    }
}
