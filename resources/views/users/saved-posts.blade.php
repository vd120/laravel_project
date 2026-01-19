@extends('layouts.app')

@section('content')
@if (session('verified'))
    <div style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(40, 167, 69, 0.95);
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3);
        z-index: 10000;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        animation: slideInRight 0.5s ease-out;
        max-width: 400px;
    ">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle" style="font-size: 24px; color: white;"></i>
            <div>
                <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px;">Email Verified Successfully!</div>
                <div style="font-size: 14px; opacity: 0.9;">Welcome to Laravel Social! You can now enjoy all features.</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 18px;
                padding: 4px;
                margin-left: 8px;
                opacity: 0.7;
                transition: opacity 0.2s ease;
            " onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0.7';">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
    <script>
        // Auto-hide success message after 5 seconds
        setTimeout(() => {
            const message = document.querySelector('[style*="position: fixed"]');
            if (message) {
                message.style.animation = 'slideOutRight 0.5s ease-out';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);

        // Add slideOutRight animation
        const slideOutStyle = document.createElement('style');
        slideOutStyle.textContent = `
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(slideOutStyle);
    </script>
@endif

<div style="padding: 20px 0;">

@if($followedUsersWithStories->count() > 0 || $myStories->count() > 0)
<div class="stories-section">
    <div class="stories-header">
        <h3>Stories</h3>
        <a href="{{ route('stories.index') }}" class="view-all-stories">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="stories-container">
        @if($myStories->count() > 0)
            @foreach($myStories as $story)
            <div class="story-item" data-story-id="{{ $story->id }}" onclick="viewStory('{{ auth()->check() ? auth()->user()->name : 'User' }}', {{ $story->id }})">
                <div class="story-avatar">
                    @if(auth()->check() && auth()->user()->profile && auth()->user()->profile->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="avatar-placeholder">{{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}</div>
                    @endif
                </div>
                <div class="story-preview">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="Your story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-info">
                    <span class="story-user">Your story</span>
                </div>
            </div>
            @endforeach
        @else
        <div class="story-item create-story" onclick="window.location.href='{{ route('stories.create') }}'">
            <div class="story-avatar">
                @if(auth()->check() && auth()->user()->profile && auth()->user()->profile->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="avatar-placeholder">{{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}</div>
                @endif
                <div class="add-story-icon">
                    <i class="fas fa-plus"></i>
                </div>
            </div>
            <div class="story-info">
                <span class="story-user">Create story</span>
            </div>
        </div>
        @endif

        @foreach($followedUsersWithStories as $user)
            @foreach($user->activeStories as $story)
            <div class="story-item" data-story-id="{{ $story->id }}" onclick="viewStory('{{ $user->name }}', {{ $story->id }})">
                <div class="story-avatar">
                    @if($user->profile && $user->profile->avatar)
                        <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="{{ "@" . $user->name }}">
                    @else
                        <div class="avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="story-preview">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-info">
                    <span class="story-user">{{ "@" . $user->name }}</span>
                </div>
            </div>
            @endforeach
        @endforeach
    </div>
</div>
@endif

<h2 class="all-posts-title">SAVED POSTS</h2>

@if($savedPosts->count() > 0)
<div id="posts-container">
    @foreach($savedPosts as $savedPost)
        @include('partials.post', ['post' => $savedPost->post])
    @endforeach
</div>

<div class="mt-6">
    {{ $savedPosts->links() }}
</div>
@else
    <div style="text-align: center; padding: 40px 20px; background: var(--card-bg); border-radius: 12px; margin: 20px 0;">
        <i class="fas fa-bookmark" style="font-size: 48px; color: var(--twitter-gray); margin-bottom: 20px; display: block;"></i>
        @if(auth()->check())
            <h3 style="color: var(--twitter-dark); margin-bottom: 10px;">No Saved Posts Yet</h3>
            <p style="color: var(--twitter-gray); margin-bottom: 20px; font-size: 16px;">You haven't saved any posts yet. Click the bookmark icon on posts you want to save for later!</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('home') }}" class="btn" style="background: var(--twitter-blue); text-decoration: none; font-weight: 600;">Browse Posts</a>
                <a href="{{ route('explore') }}" class="btn" style="background: transparent; color: var(--twitter-blue); border: 2px solid var(--twitter-blue); text-decoration: none; font-weight: 600;">Discover People</a>
            </div>
        @else
            <h3 style="color: var(--twitter-dark); margin-bottom: 10px;">No Saved Posts Yet</h3>
            <p style="color: var(--twitter-gray); margin-bottom: 20px; font-size: 16px;">Be the first to share something amazing!</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('register') }}" class="btn" style="background: var(--twitter-blue); text-decoration: none; font-weight: 600;">Sign Up Free</a>
                <a href="{{ route('login') }}" class="btn" style="background: transparent; color: var(--twitter-blue); border: 2px solid var(--twitter-blue); text-decoration: none; font-weight: 600;">Login</a>
            </div>
        @endif
    </div>
@endif

@if(!auth()->check())
<div style="text-align: center; padding: 30px 20px; background: var(--card-bg); border-radius: 12px; margin: 20px 0; border: 1px solid var(--border-color);">
    <i class="fas fa-users" style="font-size: 36px; color: var(--twitter-blue); margin-bottom: 15px; display: block;"></i>
    <h3 style="color: var(--twitter-dark); margin-bottom: 8px; font-size: 18px;">Join Our Community</h3>
    <p style="color: var(--twitter-gray); margin-bottom: 16px; font-size: 14px; line-height: 1.4;">Create your account to share posts, connect with friends, and access all features.</p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('register') }}" class="btn" style="background: var(--twitter-blue); text-decoration: none; font-weight: 600;">Sign Up Free</a>
        <a href="{{ route('login') }}" class="btn" style="background: transparent; color: var(--twitter-blue); border: 2px solid var(--twitter-blue); text-decoration: none; font-weight: 600;">Login</a>
    </div>
    <p style="margin-top: 16px; font-size: 12px; color: var(--twitter-gray); opacity: 0.8;">
        <i class="fas fa-lock" style="margin-right: 4px;"></i>
        Private posts are only visible to their owners
    </p>
</div>
@endif

<div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
    <i class="fas fa-spinner fa-spin"></i> Loading more posts...
</div>

<div id="end-of-content" style="display: none; text-align: center; padding: 20px; color: #666;">
    <i class="fas fa-check-circle"></i> You've seen all saved posts!
</div>

<style>
/* Clean Title for Saved Posts */
.all-posts-title {
    text-align: center;
    margin-bottom: 40px;
    color: var(--twitter-dark);
    font-weight: 600;
    font-size: 32px;
    letter-spacing: 2px;
    text-transform: uppercase;
    position: relative;
    z-index: 10;
}

.all-posts-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--twitter-blue);
    border-radius: 2px;
}

@keyframes neonFlicker {
    0%, 100% {
        opacity: 1;
        text-shadow:
            0 0 5px var(--twitter-blue),
            0 0 10px var(--twitter-blue),
            0 0 15px var(--twitter-blue),
            0 0 20px var(--twitter-blue),
            0 0 35px var(--twitter-blue),
            0 0 40px var(--twitter-blue),
            0 0 50px var(--twitter-blue),
            0 0 75px var(--twitter-blue);
    }
    2%, 4%, 6%, 8%, 10%, 12%, 14%, 16%, 18% {
        opacity: 0.3;
        text-shadow:
            0 0 1px var(--twitter-blue),
            0 0 2px var(--twitter-blue);
    }
    3%, 7%, 11%, 15%, 19% {
        opacity: 0.6;
        text-shadow:
            0 0 2px var(--twitter-blue),
            0 0 4px var(--twitter-blue),
            0 0 6px var(--twitter-blue);
    }
    5%, 9%, 13%, 17% {
        opacity: 0.8;
        text-shadow:
            0 0 3px var(--twitter-blue),
            0 0 6px var(--twitter-blue),
            0 0 9px var(--twitter-blue),
            0 0 12px var(--twitter-blue);
    }
    20%, 40%, 60%, 80% {
        opacity: 0.9;
        text-shadow:
            0 0 4px var(--twitter-blue),
            0 0 8px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 16px var(--twitter-blue),
            0 0 20px var(--twitter-blue);
    }
    25%, 35%, 45%, 55%, 65%, 75%, 85%, 95% {
        opacity: 0.95;
        text-shadow:
            0 0 3px var(--twitter-blue),
            0 0 6px var(--twitter-blue),
            0 0 9px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 18px var(--twitter-blue),
            0 0 24px var(--twitter-blue);
    }
    30%, 50%, 70%, 90% {
        opacity: 1;
        text-shadow:
            0 0 4px var(--twitter-blue),
            0 0 8px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 16px var(--twitter-blue),
            0 0 24px var(--twitter-blue),
            0 0 32px var(--twitter-blue),
            0 0 40px var(--twitter-blue);
    }
}

@keyframes neonGlow {
    0% {
        filter: brightness(1) contrast(1.2);
    }
    50% {
        filter: brightness(1.1) contrast(1.3);
    }
    100% {
        filter: brightness(1) contrast(1.2);
    }
}

@keyframes neonPulse {
    0%, 100% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.02);
    }
}

@keyframes underlineGlow {
    0% {
        opacity: 1;
        box-shadow:
            0 0 10px var(--twitter-blue),
            0 0 20px var(--neon-lime-bright);
    }
    100% {
        opacity: 0.8;
        box-shadow:
            0 0 15px var(--twitter-blue),
            0 0 30px var(--neon-lime-bright),
            0 0 45px var(--twitter-blue);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes modalFadeIn {
    0% {
        opacity: 0;
        backdrop-filter: blur(0px);
        -webkit-backdrop-filter: blur(0px);
    }
    100% {
        opacity: 1;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
}

@keyframes modalSlideUp {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}



/* Responsive adjustments for neon title */
@media (min-width: 1025px) {
    /* Large desktop */
    .all-posts-title {
        font-size: 52px;
        letter-spacing: 5px;
        margin-bottom: 45px;
    }

    .all-posts-title::after {
        width: 160px;
        height: 3px;
    }
}

@media (max-width: 1024px) and (min-width: 769px) {
    /* Tablets and small laptops */
    .all-posts-title {
        font-size: 38px;
        letter-spacing: 3px;
        margin-bottom: 35px;
    }

    .all-posts-title::after {
        width: 130px;
        height: 2px;
    }

    /* Improve container spacing for tablets */
    div[style*="padding: 20px 0;"] {
        padding: 25px 15px;
    }

    /* Better button layout for tablets */
    .guest-message {
        padding: 35px 25px;
        margin-bottom: 25px;
    }

    .guest-message .btn {
        padding: 14px 28px;
        font-size: 15px;
    }

    /* Adjust post form for tablets */
    .post-form-container {
        padding: 20px;
        border-radius: 20px;
    }

    .post-form-container textarea {
        font-size: 15px;
        padding: 14px 18px;
    }
}

@media (max-width: 768px) {
    .all-posts-title {
        font-size: 32px;
        letter-spacing: 2px;
        margin-bottom: 30px;
    }

    .all-posts-title::after {
        width: 100px;
        height: 2px;
    }
}

@media (max-width: 480px) {
    .all-posts-title {
        font-size: 24px;
        letter-spacing: 1px;
        margin-bottom: 20px;
    }

    .all-posts-title::after {
        width: 80px;
        height: 2px;
    }
}

.stories-section {
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 20px;
}

.stories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.stories-header h3 {
    margin: 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.view-all-stories {
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.view-all-stories:hover {
    color: var(--twitter-dark);
}

.stories-container {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 10px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.stories-container::-webkit-scrollbar {
    display: none;
}

.story-item {
    flex-shrink: 0;
    width: 70px;
    cursor: pointer;
    position: relative;
}

.story-item:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.story-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid var(--twitter-blue);
    overflow: hidden;
    margin-bottom: 8px;
}

.story-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 24px;
}

.story-preview {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.3);
}

.story-preview img,
.story-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.create-story .story-avatar {
    border-color: var(--border-color);
    position: relative;
}

.add-story-icon {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 24px;
    height: 24px;
    background: var(--twitter-blue);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid white;
}

.story-info {
    text-align: center;
}

.story-user {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--twitter-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70px;
}

@media (max-width: 480px) {
    .stories-container {
        gap: 10px;
    }

    .story-item {
        width: 60px;
    }

    .story-avatar {
        width: 60px;
        height: 60px;
    }

    .story-preview {
        width: 50px;
        height: 50px;
        top: 5px;
        left: 5px;
    }
}

/* Privacy toggle button */
.privacy-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    position: relative;
    overflow: hidden;
}

.privacy-toggle-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.privacy-toggle-btn:hover::before {
    left: 100%;
}

.privacy-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
}

.privacy-toggle-btn.private {
    background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
    box-shadow: 0 2px 8px rgba(156, 39, 176, 0.3);
}

.privacy-toggle-btn.private:hover {
    box-shadow: 0 4px 15px rgba(156, 39, 176, 0.4);
}

.privacy-toggle-btn i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

.privacy-toggle-btn.private i {
    transform: rotate(180deg);
}

/* Post Styling for Dark Theme */
.post {
    margin-bottom: 20px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 16px;
    border: 2px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.post:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.post .user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.post .user a {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 600;
}

.post .user a:hover {
    text-decoration: underline;
}

.post .user small {
    color: var(--twitter-gray);
    font-size: 12px;
}

.privacy-badge.private {
    background: var(--error-color);
    color: white;
    font-size: 10px;
    padding: 1px 4px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
    margin-left: 4px;
}

.post .content {
    margin: 16px 0;
    line-height: 1.6;
    font-size: 18px;
    color: var(--twitter-dark);
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    max-height: 500px;
    overflow-y: auto;
    padding-right: 8px;
}

.content::-webkit-scrollbar {
    width: 4px;
}

.content::-webkit-scrollbar-track {
    background: transparent;
}

.content::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 2px;
}

.content::-webkit-scrollbar-thumb:hover {
    background: var(--twitter-gray);
}

.read-more-btn {
    background: none;
    border: none;
    color: var(--twitter-blue);
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    padding: 4px 0;
    margin-top: 8px;
    text-decoration: underline;
    transition: color 0.2s ease;
}

.read-more-btn:hover {
    color: var(--twitter-dark);
}

.content-container {
    position: relative;
}

.content.truncated {
    max-height: 120px;
    overflow: hidden;
    position: relative;
}

.content.truncated::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(transparent, var(--card-bg));
    pointer-events: none;
}

/* Completely Plain Text Reaction Buttons - No Visual Styling */
.post .btn,
.post .btn:hover,
.post .btn:focus,
.post .btn:active,
.post .btn:visited,
.post .btn:focus-visible,
.post .btn:focus-within,
.post .btn:target,
.post .btn:link {
    all: unset !important;
    display: inline !important;
    color: var(--twitter-gray) !important;
    cursor: pointer !important;
    transition: color 0.2s ease !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background: none !important;
    background-color: transparent !important;
    background-image: none !important;
    border: none !important;
    border-radius: 0 !important;
    border-color: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
    outline: none !important;
    outline-color: transparent !important;
    box-shadow: none !important;
    text-shadow: none !important;
    text-decoration: none !important;
    font: inherit !important;
    text-align: left !important;
    min-width: 0 !important;
    min-height: 0 !important;
    line-height: inherit !important;
    vertical-align: baseline !important;
    position: static !important;
    z-index: auto !important;
}

/* Add spacing and click color for reaction buttons - Higher specificity */
.post .reaction-buttons .btn,
.post .reaction-buttons .btn:hover,
.post .reaction-buttons .btn:focus,
.post .reaction-buttons .btn:active {
    margin-right: 30px !important;
}

.post .reaction-buttons .btn:last-child,
.post .reaction-buttons .btn:last-child:hover,
.post .reaction-buttons .btn:last-child:focus,
.post .reaction-buttons .btn:last-child:active {
    margin-right: 0 !important;
}

.post .reaction-buttons .btn:active {
    background-color: rgba(29, 161, 242, 0.1) !important;
}

/* Allow specific color changes for liked/saved states while preventing unwanted colors */

/* Like button colors with higher specificity */
.post .reaction-buttons .like-btn {
    background: transparent !important;
    color: var(--twitter-gray) !important;
}

.post .reaction-buttons .like-btn:hover {
    color: var(--twitter-blue) !important;
}

.post .reaction-buttons .like-btn.liked {
    color: var(--error-color) !important;
}

.post .reaction-buttons .like-btn.liked:hover {
    color: var(--error-color) !important;
}

.save-btn {
    background: transparent;
    color: var(--twitter-gray);
}

.save-btn:hover {
    color: var(--twitter-blue);
}

.save-btn.saved {
    color: var(--twitter-blue);
}

.save-btn.saved:hover {
    color: var(--twitter-blue);
}

/* Likers Modal Styles */
.likers-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.likers-modal-content {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: modalSlideUp 0.3s ease-out;
}

.likers-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.likers-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.likers-modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.likers-modal-close:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.likers-modal-body {
    padding: 0;
    max-height: 400px;
    overflow-y: auto;
}

.liker-item {
    display: flex;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.liker-item:hover {
    background: var(--hover-bg);
}

.liker-item:last-child {
    border-bottom: none;
}

.liker-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 16px;
    flex-shrink: 0;
}

.liker-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.liker-info {
    flex: 1;
    min-width: 0;
}

.liker-name {
    font-weight: 600;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 16px;
    display: block;
    margin-bottom: 2px;
    transition: color 0.2s ease;
}

.liker-name:hover {
    color: var(--twitter-dark);
}

.liker-bio {
    font-size: 14px;
    color: var(--twitter-gray);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.liker-actions {
    margin-left: 16px;
}

.liker-actions .btn {
    font-size: 12px !important;
    padding: 6px 12px !important;
    min-height: 32px !important;
}

.likers-loading,
.no-likers,
.likers-error {
    text-align: center;
    padding: 40px 24px;
    color: var(--twitter-gray);
}

.likers-loading i,
.no-likers i,
.likers-error i {
    font-size: 32px;
    margin-bottom: 12px;
    display: block;
}

.likers-loading span,
.no-likers span,
.likers-error span {
    font-size: 16px;
}

.no-likers i {
    color: var(--error-color);
}

.likers-error i {
    color: #dc3545;
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .likers-modal-content {
        width: 95%;
        max-width: none;
        margin: 20px;
        max-height: 90vh;
    }

    .likers-modal-header {
        padding: 16px 20px;
    }

    .likers-modal-header h3 {
        font-size: 16px;
    }

    .liker-item {
        padding: 12px 20px;
    }

    .liker-avatar {
        width: 40px;
        height: 40px;
        margin-right: 12px;
    }

    .liker-name {
        font-size: 15px;
    }

    .liker-bio {
        font-size: 13px;
    }
}
</style>

<script>
function viewStory(userId, storyId) {
    window.location.href = '{{ url("/stories") }}/' + userId + '?story=' + storyId;
}

function toggleLike(postId, button) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    // Find like count in the container (sibling element)
    const likeContainer = button.parentElement;
    const likeCount = likeContainer ? likeContainer.querySelector('.like-count') : null;
    const currentCount = likeCount ? parseInt(likeCount.textContent) || 0 : 0;

    // Add loading state only
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Use web route instead of API route
    fetch(`/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: `_token=${csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update count with server response
            const newCount = data.likes_count || 0;
            if (likeCount) {
                likeCount.textContent = newCount;

                // Show/hide likers button based on count
                const likersBtn = likeContainer.querySelector('.likers-btn');
                if (likersBtn) {
                    likersBtn.style.display = newCount > 0 ? 'inline-flex' : 'none';
                }
            }
        } else {
            // Show error but don't change appearance
            console.error('Like failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        // Don't change appearance on error
    })
    .finally(() => {
        // Restore original button content and enable it
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function toggleSave(postId, button) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const saveText = button.querySelector('.save-text');

    // Immediately update UI for better UX (no color changes)
    const isSaved = button.classList.contains('saved');
    if (isSaved) {
        // Unsave: remove saved class, change text only
        button.classList.remove('saved');
        saveText.textContent = 'Save';
    } else {
        // Save: add saved class, change text only
        button.classList.add('saved');
        saveText.textContent = 'Saved';
    }

    // Add loading state
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/posts/${postId}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update with server response (no color changes)
            if (data.saved) {
                button.classList.add('saved');
                saveText.textContent = 'Saved';
            } else {
                button.classList.remove('saved');
                saveText.textContent = 'Save';
            }
        } else {
            // Revert UI changes on error
            console.error('Save failed:', data.message);
            if (isSaved) {
                button.classList.add('saved');
                saveText.textContent = 'Saved';
            } else {
                button.classList.remove('saved');
                saveText.textContent = 'Save';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling save:', error);
        // Revert UI changes on error
        if (isSaved) {
            button.classList.add('saved');
            saveText.textContent = 'Saved';
        } else {
            button.classList.remove('saved');
            saveText.textContent = 'Save';
        }
    })
    .finally(() => {
        // Restore original button content and enable it
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Copy post link functionality
function copyPostLink(postSlug) {
    const url = window.location.origin + '/posts/' + postSlug;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            // Show success feedback
            showNotification('Link copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(url);
        });
    } else {
        fallbackCopyTextToClipboard(url);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showNotification('Link copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy link. Please copy manually: ' + text, 'error');
    }

    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Show with animation
    setTimeout(() => notification.classList.add('show'), 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function showLoginModal(action, message) {
    // Remove any existing modals
    const existingModal = document.getElementById('login-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.id = 'login-modal';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        padding: 20px;
        animation: fadeIn 0.3s ease-out;
    `;

    // Create modal content
    modalOverlay.innerHTML = `
        <div style="
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 0;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.4s ease-out 0.1s both;
            transform: translateY(20px);
            opacity: 0;
        ">
            <!-- Header with gradient -->
            <div style="
                background: linear-gradient(135deg, var(--twitter-blue) 0%, #1A91DA 100%);
                padding: 24px 20px;
                text-align: center;
                border-radius: 20px 20px 0 0;
                position: relative;
            ">
                <div style="
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(45deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 50%, rgba(255,255,255,0.08) 100%);
                    opacity: 0.6;
                "></div>
                <i class="fas ${action === 'like' ? 'fa-heart' : action === 'save' ? 'fa-bookmark' : 'fa-star'}" style="
                    font-size: 48px;
                    color: white;
                    margin-bottom: 12px;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    animation: iconBounce 0.6s ease-out;
                "></i>
                <h2 style="
                    color: white;
                    margin: 0 0 8px 0;
                    font-size: 24px;
                    font-weight: 700;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                ">Join the Community</h2>
                <p style="
                    color: rgba(255,255,255,0.9);
                    margin: 0;
                    font-size: 16px;
                    font-weight: 500;
                ">${message}</p>
            </div>

            <!-- Content -->
            <div style="padding: 24px;">
                <!-- Action buttons -->
                <div style="
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 12px;
                    margin-bottom: 20px;
                ">
                    <a href="{{ route('login') }}" style="
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        padding: 14px 20px;
                        background: var(--twitter-blue);
                        color: white;
                        text-decoration: none;
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 14px;
                        transition: all 0.2s ease;
                        box-shadow: 0 4px 12px rgba(29, 161, 242, 0.3);
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(29, 161, 242, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(29, 161, 242, 0.3)';">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>

                    <a href="{{ route('register') }}" style="
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        padding: 14px 20px;
                        background: transparent;
                        color: var(--twitter-blue);
                        text-decoration: none;
                        border: 2px solid var(--twitter-blue);
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 14px;
                        transition: all 0.2s ease;
                    " onmouseover="this.style.background='var(--twitter-blue)'; this.style.color='white'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='transparent'; this.style.color='var(--twitter-blue)'; this.style.transform='translateY(0)';">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                </div>

                <!-- Close button -->
                <button onclick="closeLoginModal()" style="
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    width: 32px;
                    height: 32px;
                    border: none;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.2);
                    color: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.appendChild(modalOverlay);

    // Close on overlay click
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeLoginModal();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLoginModal();
        }
    });
}

function closeLoginModal() {
    const modal = document.getElementById('login-modal');
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease-out';
        setTimeout(() => {
            if (modal.parentNode) {
                modal.remove();
            }
        }, 300);
    }
}

// Video play functionality
function playVideo(overlay) {
    const container = overlay.parentElement;
    const video = container.querySelector('video');

    if (video) {
        // Remove the overlay
        overlay.style.display = 'none';
        container.classList.add('playing');

        // Play the video
        video.play().catch(function(error) {
            console.log('Video play failed:', error);
        });

        // Add event listener to show overlay when video ends
        video.addEventListener('ended', function() {
            overlay.style.display = 'flex';
            container.classList.remove('playing');
        });

        // Add event listener to show overlay when video is paused
        video.addEventListener('pause', function() {
            if (!video.seeking) {
                overlay.style.display = 'flex';
                container.classList.remove('playing');
            }
        });

        // Add event listener to hide overlay when video plays
        video.addEventListener('play', function() {
            overlay.style.display = 'none';
            container.classList.add('playing');
        });
    }
}

// Follow functionality
function toggleFollow(button, userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const username = button.getAttribute('data-username') || 'user';

    // Immediately update UI for better UX
    const isFollowing = button.classList.contains('following');
    if (isFollowing) {
        // Unfollow: change text and style
        button.textContent = 'Follow';
        button.classList.remove('following');
        button.style.background = 'var(--twitter-blue)';
    } else {
        // Follow: change text and style
        button.textContent = 'Following';
        button.classList.add('following');
        button.style.background = 'var(--success-color)';
    }

    // Add loading state
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/users/${userId}/follow`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.following) {
            button.textContent = 'Following';
            button.classList.add('following');
            button.style.background = 'var(--success-color)';
        } else {
            button.textContent = 'Follow';
            button.classList.remove('following');
            button.style.background = 'var(--twitter-blue)';
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
        // Revert UI changes on error
        if (isFollowing) {
            button.textContent = 'Following';
            button.classList.add('following');
            button.style.background = 'var(--success-color)';
        } else {
            button.textContent = 'Follow';
            button.classList.remove('following');
            button.style.background = 'var(--twitter-blue)';
        }
    })
    .finally(() => {
        // Restore original button content and enable it
        button.disabled = false;
    });
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to video overlays
    const overlays = document.querySelectorAll('.video-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            playVideo(this);
        });
    });

    // Handle video loading states
    const videos = document.querySelectorAll('.video-container video');
    videos.forEach(video => {
        video.addEventListener('loadstart', function() {
            const container = this.parentElement;
            let loading = container.querySelector('.media-loading');
            if (!loading) {
                loading = document.createElement('div');
                loading.className = 'media-loading';
                loading.innerHTML = '<i class="fas fa-spinner"></i>';
                container.appendChild(loading);
            }
        });

        video.addEventListener('canplay', function() {
            const container = this.parentElement;
            const loading = container.querySelector('.media-loading');
            if (loading) {
                loading.remove();
            }
        });
    });

    // Add notification styles
    const notificationStyle = document.createElement('style');
    notificationStyle.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 10000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            color: var(--twitter-dark);
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-success {
            border-color: var(--success-color);
        }

        .notification-success i {
            color: var(--success-color);
        }

        .notification-error {
            border-color: var(--error-color);
        }

        .notification-error i {
            color: var(--error-color);
        }

        .notification-info {
            border-color: var(--twitter-blue);
        }

        .notification-info i {
            color: var(--twitter-blue);
        }
    `;
    document.head.appendChild(notificationStyle);
});

// Add fade-in animation
const animationStyle = document.createElement('style');
animationStyle.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes iconBounce {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }

    @keyframes modalFadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(animationStyle);
</script>

<style>
/* Page Container */
.saved-posts-page {
    max-width: 900px;
    margin: 0 auto;
    min-height: calc(100vh - 60px);
    background: var(--card-bg);
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #1A91DA 100%);
    padding: 32px 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 50%, rgba(255,255,255,0.08) 100%);
    opacity: 0.6;
}

.page-title-section {
    position: relative;
    z-index: 1;
    text-align: center;
}

.page-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.page-title i {
    font-size: 24px;
    opacity: 0.9;
}

.page-subtitle {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
    font-weight: 400;
}

/* Page Content */
.page-content {
    padding: 24px;
}

/* Posts Section */
.posts-section {
    max-width: 800px;
    margin: 0 auto;
}

.posts-count {
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
}

.count-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 16px;
    background: var(--hover-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    color: var(--twitter-gray);
}

.posts-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 32px;
}

/* Pagination */
.pagination-section {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: center;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    text-align: center;
    padding: 40px 24px;
}

.empty-state-icon {
    margin-bottom: 24px;
}

.empty-state-icon i {
    font-size: 64px;
    color: var(--twitter-gray);
    opacity: 0.3;
}

.empty-state-content {
    max-width: 400px;
}

.empty-state-title {
    margin: 0 0 12px 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.empty-state-description {
    margin: 0 0 24px 0;
    font-size: 16px;
    color: var(--twitter-gray);
    line-height: 1.5;
}

.empty-state-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.primary-action,
.secondary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 2px solid;
}

.primary-action {
    background: var(--twitter-blue);
    color: white;
    border-color: var(--twitter-blue);
}

.primary-action:hover {
    background: var(--twitter-blue);
    filter: brightness(1.1);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(29, 161, 242, 0.3);
}

.secondary-action {
    background: transparent;
    color: var(--twitter-blue);
    border-color: var(--twitter-blue);
}

.secondary-action:hover {
    background: var(--twitter-blue);
    color: white;
    transform: translateY(-2px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .saved-posts-page {
        background: transparent;
    }

    .page-header {
        padding: 24px 16px;
        margin: -20px -16px 20px -16px;
    }

    .page-title {
        font-size: 24px;
        gap: 8px;
    }

    .page-title i {
        font-size: 20px;
    }

    .page-subtitle {
        font-size: 14px;
    }

    .page-content {
        padding: 16px;
    }

    .posts-section {
        max-width: none;
    }

    .posts-container {
        gap: 16px;
    }

    .empty-state {
        min-height: 300px;
        padding: 20px 16px;
    }

    .empty-state-icon i {
        font-size: 48px;
    }

    .empty-state-title {
        font-size: 20px;
    }

    .empty-state-description {
        font-size: 14px;
    }

    .empty-state-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .primary-action,
    .secondary-action {
        justify-content: center;
        padding: 14px 20px;
    }
}

@media (max-width: 480px) {
    .page-header {
        padding: 20px 12px;
        margin: -20px -12px 20px -12px;
    }

    .page-title {
        font-size: 20px;
        flex-direction: column;
        gap: 6px;
    }

    .page-title i {
        font-size: 18px;
    }

    .page-subtitle {
        font-size: 13px;
    }

    .page-content {
        padding: 12px;
    }

    .posts-container {
        gap: 12px;
    }

    .empty-state {
        padding: 16px 12px;
    }

    .empty-state-icon i {
        font-size: 40px;
    }

    .empty-state-title {
        font-size: 18px;
    }

    .empty-state-description {
        font-size: 13px;
    }

    .empty-state-actions {
        gap: 8px;
    }

    .primary-action,
    .secondary-action {
        padding: 12px 16px;
        font-size: 13px;
    }
}

/* Dark theme adjustments */
@media (prefers-color-scheme: dark) {
    .empty-state-icon i {
        color: var(--twitter-gray);
        opacity: 0.2;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .page-header {
        background: var(--twitter-dark);
    }

    .primary-action,
    .secondary-action {
        border-width: 3px;
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .primary-action,
    .secondary-action {
        transition: none;
    }

    .primary-action:hover,
    .secondary-action:hover {
        transform: none;
    }
}
</style>
@endsection