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

        broadcast(new CommentCreated($comment))->toOthers();

        // Check if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
                'message' => 'Comment posted successfully'
            ]);
        }

        return back();
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
                'success' => true,
                'message' => 'Comment deleted successfully'
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
