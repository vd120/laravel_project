<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if profile is private and current user is not following
        $isPrivate = $user->profile && $user->profile->is_private;
        $isFollowing = $currentUser ? $currentUser->isFollowing($user) : false;
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        
        // If profile is private and user is not following or owner, return limited data
        if ($isPrivate && !$isFollowing && !$isOwnProfile) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'profile' => $user->profile ? [
                        'is_private' => true,
                        'bio' => null,
                        'avatar' => null
                    ] : null
                ],
                'posts' => [],
                'message' => __('messages.profile_is_private'),
                'is_private' => true
            ]);
        }
        
        // Get posts with privacy checks
        $postsQuery = $user->posts()->with(['comments.replies.user', 'comments.likes']);
        
        // Only show public posts to non-followers
        if (!$isFollowing && !$isOwnProfile) {
            $postsQuery->where('is_private', false);
        }
        
        $posts = $postsQuery->latest()->get();
        
        return response()->json([
            'user' => $user,
            'posts' => $posts,
            'followers_count' => $user->followers->count(),
            'following_count' => $user->follows->count(),
            'is_following' => $currentUser ? $currentUser->isFollowing($user) : false,
        ]);
    }

    public function follow(User $user)
    {
        if (auth()->user()->isFollowing($user)) {
            auth()->user()->follows()->where('followed_id', $user->id)->delete();
            return response()->json(['following' => false]);
        } else {
            auth()->user()->follows()->create(['followed_id' => $user->id]);
            return response()->json(['following' => true]);
        }
    }

    public function explore()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return response()->json($users);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 20);

        if (empty($query)) {
            return response()->json(['users' => []]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function($q) use ($query) {
                $q->where('username', 'LIKE', '%' . $query . '%')
                  ->orWhere('name', 'LIKE', '%' . $query . '%');
            })
            ->with('profile')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'profile' => $user->profile ? [
                        'avatar' => $user->profile->avatar,
                        'bio' => $user->profile->bio,
                        'is_private' => $user->profile->is_private
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
