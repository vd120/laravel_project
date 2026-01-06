<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('login', [LoginController::class, 'store'])->name('login');

    Route::get('suspended', function () {
        return view('auth.suspended');
    })->name('auth.suspended');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('register', [RegisterController::class, 'store'])->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('home');
    Route::resource('posts', PostController::class);
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/save', [PostController::class, 'save'])->name('posts.save');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/like', [CommentController::class, 'like'])->name('comments.like');
    Route::get('/stories', [App\Http\Controllers\StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [App\Http\Controllers\StoryController::class, 'create'])->name('stories.create');
    Route::post('/stories', [App\Http\Controllers\StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories/{user}', [App\Http\Controllers\StoryController::class, 'show'])->name('stories.show')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/stories/{user}/{story}/viewers', [App\Http\Controllers\StoryController::class, 'viewers'])->name('stories.viewers')->where('user', '[a-zA-Z0-9_-]+');
    Route::post('/stories/{story}/react', [App\Http\Controllers\StoryController::class, 'react'])->name('stories.react');
    Route::delete('/stories/{story}/react', [App\Http\Controllers\StoryController::class, 'unreact'])->name('stories.unreact');
    Route::delete('/stories/{story}', [App\Http\Controllers\StoryController::class, 'destroy'])->name('stories.destroy');
    Route::get('/profile', function () { return redirect()->route('users.show', auth()->user()); })->name('profile');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/users/{user}/followers', [UserController::class, 'followers'])->name('users.followers')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/users/{user}/following', [UserController::class, 'following'])->name('users.following')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/users/{user}/blocked', [UserController::class, 'blocked'])->name('users.blocked')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/saved-posts', [UserController::class, 'savedPosts'])->name('users.saved-posts');
    Route::post('/users/{user}/follow', [UserController::class, 'follow'])->name('users.follow')->where('user', '[a-zA-Z0-9_-]+');
    Route::post('/users/{user}/block', [UserController::class, 'block'])->name('users.block')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/users/{user}/block', function () {
        abort(404);
    })->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/explore', [UserController::class, 'explore'])->name('explore');
    Route::get('/search', [UserController::class, 'searchPage'])->name('search');
    Route::get('/users/{user}/edit', [UserController::class, 'editProfile'])->name('profile.edit')->where('user', '[a-zA-Z0-9_-]+');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/delete-avatar', [UserController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::delete('/profile/delete-cover', [UserController::class, 'deleteCoverImage'])->name('profile.delete-cover');
    Route::delete('/profile/delete-account', [UserController::class, 'deleteAccount'])->name('profile.delete-account');
    Route::get('/password/change', function () { return view('auth.password-change'); })->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'change'])->name('password.change');

    // AI Assistant routes
    Route::get('/ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

    // Chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/api/user/new-messages', [App\Http\Controllers\ChatController::class, 'getNewMessages'])->name('api.user.new-messages');
    Route::get('/api/user/{user}/username', [App\Http\Controllers\UserController::class, 'getUsername'])->name('api.user.username');
    Route::get('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::delete('/chat/message/{message}', [App\Http\Controllers\ChatController::class, 'destroy'])->name('chat.destroy');
    Route::delete('/chat/{conversation}/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/chat/start/{userId}', [App\Http\Controllers\ChatController::class, 'startConversation'])->name('chat.start');
    Route::get('/chat/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/{conversation}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');



    // Admin routes (protected by admin middleware)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/posts', [App\Http\Controllers\AdminController::class, 'posts'])->name('posts');
        Route::delete('/posts/{post}', [App\Http\Controllers\AdminController::class, 'deletePost'])->name('posts.delete');
        Route::get('/comments', [App\Http\Controllers\AdminController::class, 'comments'])->name('comments');
        Route::delete('/comments/{comment}', [App\Http\Controllers\AdminController::class, 'deleteComment'])->name('comments.delete');
        Route::get('/stories', [App\Http\Controllers\AdminController::class, 'stories'])->name('stories');
        Route::delete('/stories/{story}', [App\Http\Controllers\AdminController::class, 'deleteStory'])->name('stories.delete');
        Route::get('/system-info', [App\Http\Controllers\AdminController::class, 'systemInfo'])->name('system-info');
        Route::post('/create-admin', [App\Http\Controllers\AdminController::class, 'createAdminAccount'])->name('create-admin');
    });


});
