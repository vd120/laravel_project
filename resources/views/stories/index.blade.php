@extends('layouts.app')

@section('content')
<div class="stories-page">
    <div class="stories-header">
        <h1 class="stories-title">Stories</h1>
        <a href="{{ route('stories.create') }}" class="btn">Create Story</a>
    </div>

    @if($myStories->count() > 0)
    <div class="story-section">
        <h3>Your Stories</h3>
        <div class="stories-grid">
            @foreach($myStories as $story)
            <div class="story-item" data-story-id="{{ $story->id }}" onclick="viewStory('{{ $story->user->name }}', {{ $story->id }})">
                <div class="story-avatar">
                    @if($story->user->profile && $story->user->profile->avatar)
                        <img src="{{ asset('storage/' . $story->user->profile->avatar) }}" alt="{{ $story->user->name }}">
                    @else
                        <div class="avatar-placeholder">{{ substr($story->user->name, 0, 1) }}</div>
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
                    <span class="story-user">{{ $story->user->name }}</span>
                    <span class="story-time">{{ $story->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($followedUsersWithStories->count() > 0)
    <div class="story-section">
        <h3>Friends' Stories</h3>
        <div class="stories-grid">
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
                    <span class="story-time">{{ $story->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>
    @endif

    @if($myStories->count() === 0 && $followedUsersWithStories->count() === 0)
    <div class="empty-state">
        <i class="fas fa-camera"></i>
        <h3>No Stories Yet</h3>
        <p>Be the first to share a story with your friends!</p>
        <a href="{{ route('stories.create') }}" class="btn">Create Your First Story</a>
    </div>
    @endif
</div>

<style>
.stories-page {
    max-width: 1200px;
    margin: 0 auto;
}

.stories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

/* Powerful Neon Effect for Stories Title */
.stories-title {
    margin: 0;
    color: var(--twitter-dark);
    font-weight: 300;
    font-size: 48px;
    letter-spacing: 4px;
    text-transform: uppercase;
    position: relative;
    z-index: 10;
    /* Powerful neon glow effects */
    text-shadow:
        0 0 5px var(--twitter-blue),
        0 0 10px var(--twitter-blue),
        0 0 15px var(--twitter-blue),
        0 0 20px var(--twitter-blue),
        0 0 35px var(--twitter-blue),
        0 0 40px var(--twitter-blue),
        0 0 50px var(--twitter-blue),
        0 0 75px var(--twitter-blue);
    animation: neonFlicker 2s ease-in-out infinite alternate, neonGlow 4s ease-in-out infinite;
}

.stories-title::before {
    content: 'STORIES';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    color: var(--twitter-blue);
    z-index: -1;
    opacity: 0.8;
    animation: neonPulse 3s ease-in-out infinite;
}

.stories-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--twitter-blue), var(--neon-lime-bright), var(--twitter-blue), transparent);
    border-radius: 2px;
    box-shadow:
        0 0 10px var(--twitter-blue),
        0 0 20px var(--neon-lime-bright),
        0 0 30px var(--twitter-blue);
    animation: underlineGlow 2.5s ease-in-out infinite alternate;
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

/* Responsive adjustments for neon title */
@media (max-width: 768px) {
    .stories-title {
        font-size: 32px;
        letter-spacing: 2px;
    }

    .stories-title::after {
        width: 100px;
        height: 2px;
    }
}

@media (max-width: 480px) {
    .stories-title {
        font-size: 24px;
        letter-spacing: 1px;
    }

    .stories-title::after {
        width: 80px;
        height: 2px;
    }
}

.story-section {
    margin-bottom: 40px;
}

.story-section h3 {
    margin-bottom: 20px;
    color: var(--twitter-dark);
    font-size: 18px;
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.story-item {
    cursor: pointer;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: transform 0.2s ease;
    position: relative;
}

.story-item:hover {
    transform: scale(1.05);
}

.story-avatar {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 3px solid white;
    overflow: hidden;
    z-index: 2;
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
    font-size: 18px;
}

.story-preview {
    width: 100%;
    height: 200px;
    position: relative;
}

.story-preview img,
.story-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 40px 10px 10px 10px;
    color: white;
}

.story-user {
    display: block;
    font-weight: 600;
    font-size: 14px;
}

.story-time {
    display: block;
    font-size: 12px;
    opacity: 0.8;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    display: block;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: var(--twitter-dark);
}

</style>

<script>
// Initialize real-time manager for story updates
document.addEventListener('DOMContentLoaded', function() {
    if (window.realTimeManager) {
        window.realTimeManager.init();
    }
});

function viewStory(username, storyId) {
    // Username is now passed directly, construct the URL
    window.location.href = '{{ url("/stories") }}/' + username + '?story=' + storyId;
}
</script>
@endsection
