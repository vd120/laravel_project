@extends('layouts.app')

@section('content')
<div class="stories-page">
    <div class="stories-header">
        <h1>Stories</h1>
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

.stories-header h1 {
    margin: 0;
    color: var(--twitter-dark);
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
