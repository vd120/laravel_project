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

        // Process mentions in the comment content
        app(\App\Services\MentionService::class)->processMentions($comment, $comment->content, auth()->id());

        $commentData = $comment->load(['user', 'user.profile']);
        $commentData->content = app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content);

        return response()->json($commentData, 201);
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
