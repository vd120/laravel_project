<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Explicit route binding for Story model to use slug
Route::bind('story', function ($value) {
    return \App\Models\Story::where('slug', $value)->firstOrFail();
});

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login.view');

    Route::post('login', [LoginController::class, 'store'])->name('login');

    Route::get('suspended', function () {
        return view('auth.suspended');
    })->name('auth.suspended');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register.view');

    Route::post('register', [RegisterController::class, 'store'])->name('register');

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');

    // Google OAuth Routes
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

Route::middleware(['auth', 'suspended'])->group(function () {
    Route::post('logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

// Email verification routes (6-digit code system)
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/email/verify', function () {
    // Allow access for both logged-in users and users with pending verification
    if (auth()->check()) {
        return view('auth.verify-email');
    }

    // Check if there's a pending verification user in session
    $pendingUserId = session('pending_verification_user_id');
    if ($pendingUserId) {
        $pendingUser = \App\Models\User::find($pendingUserId);
        if ($pendingUser && !$pendingUser->hasVerifiedEmail()) {
            // Temporarily log in the user for verification
            auth()->login($pendingUser);
            return view('auth.verify-email');
        }
    }

    return redirect('/')->with('error', 'Please register first.');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    // If no authenticated user, check for pending verification user
    if (!$user) {
        $pendingUserId = session('pending_verification_user_id');
        if ($pendingUserId) {
            $user = \App\Models\User::find($pendingUserId);
        }
    }

    // Check if user is already verified
    if ($user && $user->hasVerifiedEmail()) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Your account is already verified!'], 400);
        }
        return back()->with('error', 'Your account is already verified!');
    }

    if ($user && !$user->hasVerifiedEmail()) {
        // Generate and send new verification code
        $verificationCode = $user->generateVerificationCode();

        // Send simple verification code via email
        \Illuminate\Support\Facades\Mail::raw(
            "Welcome to " . config('app.name') . "!\n\n" .
            "Your verification code is: {$verificationCode}\n\n" .
            "Please enter this code to verify your account.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject(config('app.name') . ' - Verification Code');
            }
        );

        // Return JSON response for AJAX
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Verification code sent!']);
        }

        return back()->with('message', 'New verification code sent!');
    }

    // Return JSON response for AJAX
    if ($request->expectsJson()) {
        return response()->json(['error' => 'Unable to send verification email.'], 422);
    }

    return back()->with('error', 'Unable to send verification email.');
})->name('verification.send');

Route::post('/email/verify-code', function (Request $request) {
    $request->validate([
        'code' => 'required|string|size:6|regex:/^\d{6}$/',
    ]);

    $user = $request->user();

    // If no authenticated user, check for pending verification user
    if (!$user) {
        $pendingUserId = session('pending_verification_user_id');
        if ($pendingUserId) {
            $user = \App\Models\User::find($pendingUserId);
        }
    }

    if (!$user) {
        return redirect()->route('login')->withErrors(['code' => 'User not found.']);
    }

    if ($user->verifyCode($request->code)) {
        // Clear pending verification session
        session()->forget('pending_verification_user_id');

        // Ensure user is logged in
        if (!auth()->check()) {
            auth()->login($user);
        }

        // Regenerate session for security
        request()->session()->regenerate();

        return redirect('/')->with('message', 'Email verified successfully! Welcome to the platform.');
    }

    return back()->withErrors(['code' => 'Invalid or expired verification code.']);
})->name('verification.verify-code');

Route::get('/', function () {
    // If user is not authenticated, show the landing page
    if (!auth()->check()) {
        return view('home');
    }
    // If authenticated and verified, show the posts feed
    if (!auth()->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }
    // Show posts feed
    return app(\App\Http\Controllers\PostController::class)->index(request());
})->name('home');

// User account status check (for security monitoring)
Route::middleware(['auth', 'suspended'])->group(function () {
    Route::get('/user/check-account-status', [App\Http\Controllers\UserController::class, 'checkAccountStatus'])->name('user.check-account-status');
});

// Test route for debugging
Route::get('/user/test-route', function() {
    return response()->json(['status' => 'ok', 'message' => 'Route works']);
});

Route::middleware(['auth', 'suspended', 'verified'])->group(function () {
    Route::resource('posts', PostController::class, [
        'parameters' => ['posts' => 'post:slug'],
        'only' => ['index', 'show', 'store', 'update', 'destroy', 'create', 'edit']
    ]);
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like')->where('post', '[a-zA-Z0-9]{24}');
    Route::post('/posts/{post}/save', [PostController::class, 'save'])->name('posts.save')->where('post', '[a-zA-Z0-9]{24}');
    Route::get('/posts/{post}/likers', [PostController::class, 'getLikers'])->name('posts.likers')->where('post', '[a-zA-Z0-9]{24}');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/like', [CommentController::class, 'like'])->name('comments.like');
    Route::get('/stories', [App\Http\Controllers\StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [App\Http\Controllers\StoryController::class, 'create'])->name('stories.create');
    Route::post('/stories', [App\Http\Controllers\StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories/{user}/{story}', [App\Http\Controllers\StoryController::class, 'show'])->name('stories.show')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/stories/{user}/{story}/viewers', [App\Http\Controllers\StoryController::class, 'viewers'])->name('stories.viewers')->where('user', '[a-zA-Z0-9_-]+');
    Route::post('/stories/{user}/{story}/react', [App\Http\Controllers\StoryController::class, 'react'])->name('stories.react');
    Route::delete('/stories/{user}/{story}/react', [App\Http\Controllers\StoryController::class, 'removeReaction'])->name('stories.remove-reaction');
    Route::get('/stories/{user}/{story}/reactions', [App\Http\Controllers\StoryController::class, 'getReactions'])->name('stories.reactions');
    Route::get('/stories/{user}/{story}/check-reaction', [App\Http\Controllers\StoryController::class, 'checkReaction'])->name('stories.check-reaction');
    Route::delete('/stories/{user}/{story}', [App\Http\Controllers\StoryController::class, 'destroy'])->name('stories.destroy');

    Route::get('/profile', function () { return redirect()->route('users.show', auth()->user()); })->name('profile');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/followers', [UserController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [UserController::class, 'following'])->name('users.following');
    Route::get('/users/{user}/blocked', [UserController::class, 'blocked'])->name('users.blocked');
    Route::get('/saved-posts', [UserController::class, 'savedPosts'])->name('users.saved-posts');
    Route::post('/users/{user}/follow', [UserController::class, 'follow'])->name('users.follow');
    Route::post('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::get('/users/{user}/block', function () {
        abort(404);
    });
    Route::get('/explore', [UserController::class, 'explore'])->name('explore');
    Route::get('/search', [UserController::class, 'searchPage'])->name('search');
    Route::get('/users/{user}/edit', [UserController::class, 'editProfile'])->name('profile.edit')->where('user', '[a-zA-Z0-9_\- ]+');
    Route::post('/profile/{user}/update', [UserController::class, 'updateProfile'])->name('profile.update')->where('user', '[a-zA-Z0-9_\- ]+');
    Route::delete('/profile/delete-avatar', [UserController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::delete('/profile/delete-cover', [UserController::class, 'deleteCoverImage'])->name('profile.delete-cover');
    Route::delete('/profile/delete-account', [UserController::class, 'deleteAccount'])->name('profile.delete-account');
    Route::get('/password/change', function () { return view('auth.password-change'); })->name('password.change.view');
    Route::post('/password/change', [PasswordController::class, 'change'])->name('password.change');

    // AI Assistant routes
    Route::get('/ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

    // Chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');

    Route::get('/chat/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/chat/conversations/updated', [App\Http\Controllers\ChatController::class, 'getUpdatedConversations'])->name('chat.conversations.updated');
    Route::get('/api/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/user/{user}/username', [App\Http\Controllers\UserController::class, 'getUsername'])->name('api.user.username');
    Route::post('/user/online-status', [App\Http\Controllers\UserController::class, 'updateOnlineStatus'])->name('user.online-status');
    Route::post('/user/online-status/offline', [App\Http\Controllers\UserController::class, 'setOfflineStatus'])->name('user.offline-status');
    Route::get('/user/{user}/online-status', [App\Http\Controllers\UserController::class, 'getOnlineStatus'])->name('user.get-online-status');
    Route::post('/user/online-status/batch', [App\Http\Controllers\UserController::class, 'getMultipleOnlineStatus'])->name('user.batch-online-status');
    Route::get('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::delete('/chat/message/{message}', [App\Http\Controllers\ChatController::class, 'destroy'])->name('chat.destroy');
    Route::delete('/chat/{conversation}/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/chat/start/{userId}', [App\Http\Controllers\ChatController::class, 'startConversation'])->name('chat.start');
    Route::get('/chat/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/{conversation}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::post('/chat/{conversation}/status', [App\Http\Controllers\ChatController::class, 'getMessageStatuses'])->name('chat.status');
    Route::post('/chat/message/delivered', [App\Http\Controllers\ChatController::class, 'confirmDelivery'])->name('chat.message-delivered');
    Route::post('/chat/{conversation}/typing', [App\Http\Controllers\ChatController::class, 'sendTypingIndicator'])->name('chat.typing');
    Route::get('/chat/{conversation}/typing', [App\Http\Controllers\ChatController::class, 'getTypingStatus'])->name('chat.typing-status');

    // Group chat routes with slug-based URLs
    Route::get('/groups', [App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [App\Http\Controllers\GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [App\Http\Controllers\GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{slug}', [App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{slug}/edit', [App\Http\Controllers\GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{slug}', [App\Http\Controllers\GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{slug}', [App\Http\Controllers\GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{slug}/members', [App\Http\Controllers\GroupController::class, 'addMembers'])->name('groups.add-members');
    Route::delete('/groups/{slug}/members/{userId}', [App\Http\Controllers\GroupController::class, 'removeMember'])->name('groups.remove-member');
    Route::post('/groups/{slug}/members/{userId}/admin', [App\Http\Controllers\GroupController::class, 'makeAdmin'])->name('groups.make-admin');
    Route::delete('/groups/{slug}/members/{userId}/admin', [App\Http\Controllers\GroupController::class, 'removeAdmin'])->name('groups.remove-admin');
    Route::post('/groups/{slug}/regenerate-invite', [App\Http\Controllers\GroupController::class, 'regenerateInvite'])->name('groups.regenerate-invite');
    Route::post('/groups/{slug}/quick-invite', [App\Http\Controllers\GroupController::class, 'quickInvite'])->name('groups.quick-invite');
    Route::post('/groups/accept-invite/{inviteLink}', [App\Http\Controllers\GroupController::class, 'acceptInvite'])->name('groups.accept-invite');
    
    // Join group via invite link
    Route::get('/join/{inviteLink}', [App\Http\Controllers\GroupController::class, 'joinViaInvite'])->name('groups.join');



    // Admin routes (protected by admin middleware)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/posts', [App\Http\Controllers\AdminController::class, 'posts'])->name('posts');
        Route::delete('/posts/{post}', [App\Http\Controllers\AdminController::class, 'deletePost'])->name('posts.delete')->where('post', '[a-zA-Z0-9]{24}');
        Route::get('/comments', [App\Http\Controllers\AdminController::class, 'comments'])->name('comments');
        Route::delete('/comments/{comment}', [App\Http\Controllers\AdminController::class, 'deleteComment'])->name('comments.delete');
        Route::get('/stories', [App\Http\Controllers\AdminController::class, 'stories'])->name('stories');
        Route::delete('/stories/{story}', [App\Http\Controllers\AdminController::class, 'deleteStory'])->name('stories.delete');
        Route::post('/create-admin', [App\Http\Controllers\AdminController::class, 'createAdminAccount'])->name('create-admin');
    });


});