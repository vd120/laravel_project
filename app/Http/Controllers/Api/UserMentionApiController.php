<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMentionApiController extends Controller
{
    /**
     * Get followed users for mention autocomplete
     * - When search is empty (just @ typed): return all followed users (max 10)
     * - When search has letters (e.g., @a): return only matching followed users
     */
    public function following(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            $search = $request->get('search', '');
            $limit = (int) $request->get('limit', 10);

            // Get the authenticated user's following list
            $user = Auth::user();

            // If search is empty, return followed users (max 10)
            if (empty($search)) {
                $followingUsers = $user->following()
                    ->orderBy('name', 'asc')
                    ->limit($limit)
                    ->get();
            } else {
                // If search has letters, return only matching followed users
                $followingUsers = $user->following()
                    ->where(function($query) use ($search) {
                        $query->where('name', 'LIKE', $search . '%')
                              ->orWhere('username', 'LIKE', $search . '%');
                    })
                    ->orderBy('name', 'asc')
                    ->limit($limit)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $followingUsers->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'avatar_url' => $u->avatar_url,
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('User mention suggestions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
