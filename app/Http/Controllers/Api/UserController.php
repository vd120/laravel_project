<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $posts = $user->posts()->with(['comments.replies.user', 'comments.likes'])->latest()->get();
        return response()->json([
            'user' => $user,
            'posts' => $posts,
            'followers_count' => $user->followers->count(),
            'following_count' => $user->follows->count(),
            'is_following' => auth()->user()->isFollowing($user),
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
            ->where('name', 'LIKE', '%' . $query . '%')
            ->with('profile')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->name, // Using name as username for now
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
