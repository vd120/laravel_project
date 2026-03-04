@extends('layouts.app')

@section('title', 'Stories')

@section('content')
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
@endif

<div class="stories-page">
    <div class="page-header">
        <h1>Stories</h1>
        <a href="{{ route('stories.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Create Story
        </a>
    </div>

    @if($myStories->count() > 0)
    <div class="story-section">
        <h3>Your Stories</h3>
        <div class="stories-grid">
            @php
            // Group stories by user and get the latest one
            $myStoriesGrouped = $myStories->groupBy('user_id');
            @endphp
            @foreach($myStoriesGrouped as $userId => $userStories)
            @php
            $latestStory = $userStories->sortByDesc('created_at')->first();
            @endphp
            <div class="story-card" onclick="viewStory('{{ $latestStory->user->username }}', '{{ $latestStory->slug }}')">
                <div class="story-preview">
                    @if($latestStory->media_type === 'image')
                        <img src="{{ asset('storage/' . $latestStory->media_path) }}" alt="Story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $latestStory->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-overlay">
                    <div class="story-avatar">
                        <img src="{{ $latestStory->user->avatar_url }}" alt="Avatar">
                    </div>
                    <div class="story-meta">
                        <span class="story-user">{{ $latestStory->user->username }}</span>
                        <span class="story-time">{{ $latestStory->created_at->diffForHumans() }}</span>
                    </div>
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
            @php
            $latestStory = $user->activeStories->sortByDesc('created_at')->first();
            @endphp
            @if($latestStory)
            <div class="story-card" onclick="viewStory('{{ $user->username }}', '{{ $latestStory->slug }}')">
                <div class="story-preview">
                    @if($latestStory->media_type === 'image')
                        <img src="{{ asset('storage/' . $latestStory->media_path) }}" alt="Story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $latestStory->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-overlay">
                    <div class="story-avatar">
                        <img src="{{ $user->avatar_url }}" alt="Avatar">
                    </div>
                    <div class="story-meta">
                        <span class="story-user">{{ $user->username }}</span>
                        <span class="story-time">{{ $latestStory->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($myStories->count() === 0 && $followedUsersWithStories->count() === 0)
    <div class="empty-state">
        <i class="fas fa-camera"></i>
        <h3>No Stories Yet</h3>
        <p>Be the first to share a story with your friends!</p>
        <a href="{{ route('stories.create') }}" class="btn-primary">Create Your First Story</a>
    </div>
    @endif
</div>

<style>
.stories-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.story-section {
    margin-bottom: 40px;
}

.story-section h3 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.story-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 9/16;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
}

.story-card:hover {
    transform: scale(1.02);
}

.story-preview {
    width: 100%;
    height: 100%;
}

.story-preview img,
.story-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(transparent 50%, rgba(0,0,0,0.8));
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.story-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 3px solid var(--twitter-blue);
    overflow: hidden;
}

.story-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--twitter-blue), #8B5CF6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 20px;
}

.story-meta {
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

.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 24px;
    cursor: pointer;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #1991DB;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .stories-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .stories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function viewStory(username, storySlug) {
    window.location.href = '/stories/' + username + '/' + storySlug;
}

// Check for story deleted toast
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('story_deleted') === 'true') {
        localStorage.removeItem('story_deleted');
        if (typeof showToast === 'function') {
            showToast('Story deleted successfully', 'success');
        }
    }
});
</script>
@endsection
