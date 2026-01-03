<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
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

        return response()->json($comment->load('user'), 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $comment->update($request->only('content'));
        return response()->json($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }

    public function like(Comment $comment)
    {
        $like = $comment->likes()->where('user_id', auth()->id())->first();
        if ($like) {
            $like->delete();
            return response()->json(['liked' => false, 'likes_count' => $comment->likes()->count()]);
        } else {
            CommentLike::create(['user_id' => auth()->id(), 'comment_id' => $comment->id]);
            return response()->json(['liked' => true, 'likes_count' => $comment->likes()->count()]);
        }
    }
}
