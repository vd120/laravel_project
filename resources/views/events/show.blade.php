@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('events.index') }}">{{ __('events.life_events') }}</a>
            <span>/</span>
            <span>{{ $event->title }}</span>
        </div>
        <div class="header-actions">
            @if(auth()->id() === $event->user_id)
                <a href="{{ route('events.edit', $event->slug) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                </a>
            @endif
            <a href="{{ route('events.memory-book', $event->user) }}" class="btn btn-primary">
                <i class="fas fa-book"></i> {{ __('events.memory_book') }}
            </a>
        </div>
    </div>

    <div class="event-detail-card">
        <div class="event-detail-header">
            <div class="event-detail-user">
                <img src="{{ $event->user->avatar_url }}" alt="{{ $event->user->username }}" class="event-detail-avatar">
                <div class="event-detail-user-info">
                    <a href="{{ route('users.show', $event->user) }}" class="event-detail-user-name">{{ $event->user->username }}</a>
                    <span class="event-detail-date">{{ $event->formatted_date }}</span>
                </div>
            </div>
            <div class="event-detail-badge">
                <span class="event-detail-icon">{{ $event->icon }}</span>
                <span class="event-detail-type">{{ __("events.types.{$event->event_type}") }}</span>
            </div>
        </div>

        @if($event->is_anniversary && $event->years_since)
            <div class="anniversary-detail-badge">
                <i class="fas fa-undo"></i>
                {{ __('events.anniversary') }} • {{ $event->years_since }} {{ __('events.years', ['count' => $event->years_since]) }}
            </div>
        @endif

        <div class="event-detail-content">
            <h1 class="event-detail-title">{{ $event->title }}</h1>
            @if($event->description)
                <div class="event-detail-description">
                    {!! nl2br(e($event->description)) !!}
                </div>
            @endif
        </div>

        @if(auth()->id() !== $event->user_id)
            <div class="event-detail-reactions-section">
                <h3>{{ __('events.reactions') }}</h3>
                
                @if($groupedReactions->count() > 0)
                    <div class="reactions-list">
                        @foreach($groupedReactions as $reaction)
                            <div class="reaction-item">
                                <span class="reaction-emoji-display">{{ $reaction->reaction_type }}</span>
                                <span class="reaction-count">{{ $reaction->count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="react-section">
                    <h4>{{ __('events.add_reaction') }}</h4>
                    <div class="reaction-options">
                        @foreach($allowedReactions as $emoji)
                            <button type="button" class="reaction-option" onclick="reactToEvent('{{ $event->slug }}', '{{ $emoji }}')">
                                {{ $emoji }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="event-detail-meta">
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>{{ $event->event_date->translatedFormat(__('messages.date_format')) }}</span>
            </div>
            @if($event->year && $event->is_anniversary)
                <div class="meta-item">
                    <i class="fas fa-history"></i>
                    <span>{{ __('events.since') }} {{ $event->year }}</span>
                </div>
            @endif
            @if($event->is_private)
                <div class="meta-item">
                    <i class="fas fa-lock"></i>
                    <span>{{ __('messages.private') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--text-muted);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: var(--accent);
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.event-detail-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
    max-width: 800px;
    margin: 0 auto;
}

.event-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.event-detail-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.event-detail-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.event-detail-user-info {
    display: flex;
    flex-direction: column;
}

.event-detail-user-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    font-size: 1.1rem;
}

.event-detail-user-name:hover {
    color: var(--accent);
}

.event-detail-date {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.event-detail-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--accent-light);
    border-radius: 24px;
    font-weight: 600;
    color: var(--accent);
}

.event-detail-icon {
    font-size: 1.5rem;
}

.anniversary-detail-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 24px;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.event-detail-content {
    margin: 1.5rem 0;
}

.event-detail-title {
    font-size: 1.75rem;
    color: var(--text);
    margin: 0 0 1rem 0;
}

.event-detail-description {
    color: var(--text-muted);
    line-height: 1.8;
    font-size: 1.05rem;
}

.event-detail-reactions-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}

.event-detail-reactions-section h3 {
    color: var(--text);
    margin-bottom: 1rem;
}

.reactions-list {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.reaction-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bg);
    padding: 0.5rem 1rem;
    border-radius: 12px;
}

.reaction-emoji-display {
    font-size: 1.25rem;
}

.reaction-count {
    font-weight: 600;
    color: var(--text);
}

.react-section h4 {
    color: var(--text);
    margin-bottom: 0.75rem;
}

.reaction-options {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.reaction-option {
    background: var(--bg);
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.reaction-option:hover {
    transform: scale(1.15);
    background: var(--accent-light);
}

.event-detail-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    font-size: 0.9rem;
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .event-detail-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>

<script>
function reactToEvent(eventSlug, emoji) {
    fetch(`/api/events/${eventSlug}/react`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ emoji: emoji })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '{{ __('messages.error_occurred') }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __('messages.error_occurred') }}');
    });
}
</script>
@endsection
