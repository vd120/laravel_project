@extends('layouts.app')

@section('title', __('messages.story_viewers') . ' - ' . $user->username)

@section('content')
<div class="story-viewers-page">
    <div class="page-header">
        <a href="{{ route('stories.show', [$user, $story]) }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            {{ __('messages.back_to_story') }}
        </a>
        <h1>{{ __('messages.story_viewers') }}</h1>
    </div>

    <div class="story-preview-section">
        @if($story->media_type === 'image')
            <img src="{{ asset('storage/' . $story->media_path) }}" alt="{{ __('messages.story') }}" class="story-preview">
        @elseif($story->media_type === 'video')
            <video class="story-preview" muted>
                <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
            </video>
        @endif
        <div class="story-info">
            <span class="story-date">{{ __('messages.posted') }} {{ $story->created_at->diffForHumans() }}</span>
            <span class="view-count"><i class="fas fa-eye"></i> {{ $viewerData->count() }} {{ __('messages.views') }}</span>
        </div>
    </div>

    <div class="viewers-list">
        <h2>{{ __('messages.viewers') }}</h2>
        @if($viewerData->count() > 0)
            <div class="viewers-grid">
                @foreach($viewerData as $viewer)
                <div class="viewer-item">
                    <div class="viewer-avatar">
                        <img src="{{ $viewer['user']->avatar_url }}" alt="Avatar">
                    </div>
                    <div class="viewer-info">
                        <a href="{{ route('users.show', $viewer['user']) }}" class="viewer-name">{{ $viewer['user']->username }}</a>
                        @if($viewer['reaction'])
                            <span class="viewer-reaction">{{ __('messages.reaction') }}: {{ $viewer['reaction'] }}</span>
                        @endif
                        @if($viewer['user']->profile && $viewer['user']->profile->bio)
                            <span class="viewer-bio">{{ Str::limit($viewer['user']->profile->bio, 40) }}</span>
                        @endif
                    </div>
                    <div class="viewer-meta">
                        <span class="viewed-time">{{ $viewer['viewed_at']->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-eye-slash"></i>
                <h3>{{ __('messages.no_viewers_yet') }}</h3>
                <p>{{ __('messages.no_viewers_desc') }}</p>
            </div>
        @endif
    </div>
</div>

<style>
.story-viewers-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.back-link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 20px;
    transition: background-color 0.2s ease;
}

.back-link:hover {
    background: var(--twitter-light);
}

.story-preview-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.story-preview {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    display: block;
}

.story-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: var(--twitter-light);
    border-top: 1px solid var(--border-color);
}

.story-date {
    font-size: 14px;
    color: var(--twitter-gray);
}

.view-count {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    color: var(--twitter-blue);
}

.view-count i {
    font-size: 16px;
}

.viewers-list {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.viewers-list h2 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.viewers-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.viewer-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    background: var(--twitter-light);
    transition: background-color 0.2s ease;
}

.viewer-item:hover {
    background: var(--hover-bg);
}

.viewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid var(--border-color);
}

.viewer-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 20px;
}

.viewer-info {
    flex: 1;
    min-width: 0;
}

.viewer-name {
    display: block;
    font-weight: 600;
    color: var(--twitter-dark);
    text-decoration: none;
    font-size: 15px;
    margin-bottom: 2px;
}

.viewer-name:hover {
    color: var(--twitter-blue);
}

.viewer-bio {
    display: block;
    font-size: 12px;
    color: var(--twitter-gray);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.viewer-reaction {
    display: block;
    font-size: 13px;
    color: var(--twitter-blue);
    margin-top: 2px;
    font-weight: 500;
}

.viewer-meta {
    flex-shrink: 0;
}

.viewed-time {
    font-size: 12px;
    color: var(--twitter-gray);
}

.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

@media (max-width: 768px) {
    .story-viewers-page {
        padding: 16px;
    }

    .page-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
    }

    .story-preview {
        max-height: 300px;
    }

    .story-info {
        padding: 12px 16px;
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }

    .viewers-list {
        padding: 16px;
    }

    .viewer-avatar {
        width: 40px;
        height: 40px;
    }

    .viewer-name {
        font-size: 14px;
    }

    .viewer-bio {
        font-size: 12px;
    }
}
</style>
@endsection
