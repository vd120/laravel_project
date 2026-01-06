<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Events\CommentDeleted;
use App\Events\CommentLiked;
use App\Events\CommentUnliked;
use App\Models\Comment;
use App\Models\CommentLike;
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
                    'commenter_name' => auth()->user()->name,
                    'commenter_id' => auth()->id(),
                    'comment_content' => substr($comment->content, 0, 50) . (strlen($comment->content) > 50 ? '...' : ''),
                    'post_content' => substr($comment->post->content ?? 'Image post', 0, 30)
                ],
                'related_type' => \App\Models\Comment::class,
                'related_id' => $comment->id
            ]);
        }

        broadcast(new CommentCreated($comment))->toOthers();

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            $commentData = $comment->load('user');
            $commentData->content = app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content);

            return response()->json([
                'success' => true,
                'comment' => $commentData,
                'message' => 'Comment posted successfully'
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

        broadcast(new CommentDeleted(
            $commentId,
            $postId,
            $comment->post->comments()->count()
        ))->toOthers();

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
        $user = auth()->user();
        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            // Refresh the comment model to get updated relationships
            $comment->refresh();
            broadcast(new CommentUnliked(
                $comment->id,
                $user->id,
                $user->name,
                $comment->likes()->count()
            ))->toOthers();
        } else {
            $newLike = CommentLike::create(['user_id' => $user->id, 'comment_id' => $comment->id]);
            // Refresh the comment model to get updated relationships
            $comment->refresh();
            broadcast(new CommentLiked($newLike))->toOthers();
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
    }
}
