<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;



// Username availability check (public endpoint)
Route::get('/check-username/{username}', function ($username) {
    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        return response()->json(['available' => false, 'message' => 'Invalid username format']);
    }

    // Get current user ID from session if authenticated
    $currentUserId = auth()->id();

    // Check if username exists (exclude current user if editing profile)
    $query = \App\Models\User::where('name', $username);

    if ($currentUserId) {
        $query->where('id', '!=', $currentUserId);
    }

    $exists = $query->exists();

    return response()->json([
        'available' => !$exists,
        'username' => $username
    ]);
});

// User search (web authenticated endpoint)
Route::middleware('web')->group(function () {
    Route::get('/search-users', [UserController::class, 'search']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class)->parameters([
        'posts' => 'post:slug'
    ]);
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->where('post', '[a-zA-Z0-9]{24}');
    Route::apiResource('comments', CommentController::class)->except(['index', 'show']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::get('/users/{user}', [UserController::class, 'show'])->where('user', '[a-zA-Z0-9_-]+');
    Route::post('/users/{user}/follow', [UserController::class, 'follow'])->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/explore', [UserController::class, 'explore']);
    Route::post('/password/change', [App\Http\Controllers\Api\PasswordController::class, 'change']);

});

// Web-authenticated API routes for notifications (session-based auth)
Route::middleware('web')->group(function () {
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    Route::delete('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'deleteAll']);
    Route::get('/notifications/realtime-updates', [App\Http\Controllers\Api\NotificationController::class, 'getRealtimeUpdates']);
});