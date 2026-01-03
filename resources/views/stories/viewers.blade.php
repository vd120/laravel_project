@extends('layouts.app')

@section('title', 'Story Viewers - @' . $user->name)

@section('content')
<div class="story-viewers-page">
    <div class="container">
        <div class="viewers-header">
            <button onclick="history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h1>Story Viewers</h1>
            <span class="viewers-count">{{ $viewerData->count() }} viewers</span>
        </div>

        <div class="story-preview">
            @if($story->media_type === 'image')
                <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="preview-media">
            @else
                <video controls class="preview-media">
                    <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                </video>
            @endif
        </div>

        
        <div class="viewers-section">
            <h3>Views & Reactions ({{ $viewerData->count() }})</h3>
            <div class="viewers-list">
                @if($viewerData->count() > 0)
                    @foreach($viewerData as $viewer)
                    <div class="viewer-item">
                        <div class="viewer-avatar">
                            @if($viewer['user']->profile && $viewer['user']->profile->avatar)
                                <img src="{{ asset('storage/' . $viewer['user']->profile->avatar) }}" alt="{{ $viewer['user']->name }}">
                            @else
                                <div class="avatar-placeholder">{{ substr($viewer['user']->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="viewer-info">
                            <div class="viewer-name">
                                {{ $viewer['user']->name }}
                                @if($viewer['reaction'])
                                    <span class="viewer-reaction">{{ $viewer['reaction'] }}</span>
                                @endif
                            </div>
                            <div class="viewer-time">{{ $viewer['viewed_at']->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('users.show', $viewer['user']) }}" class="view-profile-btn">
                            <i class="fas fa-user"></i>
                        </a>
                    </div>
                    @endforeach
                @else
                    <div class="empty-viewers">
                        <i class="fas fa-eye-slash"></i>
                        <p>No one has viewed this story yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.story-viewers-page {
    min-height: 100vh;
    background: var(--twitter-white);
}

.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.viewers-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
}

.back-btn {
    background: none;
    border: none;
    color: var(--twitter-dark);
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.back-btn:hover {
    background: var(--border-color);
}

.viewers-header h1 {
    margin: 0;
    font-size: 20px;
    color: var(--twitter-dark);
}

.viewers-count {
    font-size: 14px;
    color: var(--twitter-gray);
}

.story-preview {
    margin-bottom: 20px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.preview-media {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    display: block;
}

.viewers-list {
    background: var(--twitter-white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.viewer-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.viewer-item:hover {
    background: var(--hover-bg);
}

.viewer-item:last-child {
    border-bottom: none;
}

.viewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 15px;
    flex-shrink: 0;
}

.viewer-avatar img {
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
    font-size: 20px;
}

.viewer-info {
    flex: 1;
}

.viewer-name {
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.viewer-reaction {
    font-size: 16px;
    opacity: 0.8;
}

.viewer-time {
    font-size: 12px;
    color: var(--twitter-gray);
}

.view-profile-btn {
    color: var(--twitter-blue);
    text-decoration: none;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.view-profile-btn:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.empty-viewers {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
}

.empty-viewers i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

.viewers-section,
.reactions-section {
    margin-bottom: 20px;
}

.viewers-section h3,
.reactions-section h3 {
    margin: 0 0 15px 0;
    color: var(--twitter-dark);
    font-size: 16px;
    font-weight: 600;
}

.reactions-list {
    background: var(--twitter-white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.reaction-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.reaction-item:hover {
    background: var(--hover-bg);
}

.reaction-item:last-child {
    border-bottom: none;
}

.reaction-emoji {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;
    flex-shrink: 0;
}

.reaction-info {
    flex: 1;
}

.reaction-user {
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 2px;
}

.reaction-time {
    font-size: 12px;
    color: var(--twitter-gray);
}

.empty-reactions {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
}

.empty-reactions i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

@media (max-width: 480px) {
    .container {
        padding: 15px;
    }

    .viewers-header {
        padding-bottom: 10px;
    }

    .viewer-item {
        padding: 12px;
    }

    .viewer-avatar {
        width: 45px;
        height: 45px;
        margin-right: 12px;
    }
}
</style>
@endsection
