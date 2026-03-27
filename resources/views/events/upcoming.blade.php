@extends('layouts.app')

@section('title', __('events.upcoming_events'))

@section('content')
<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas fa-calendar-alt"></i>
            {{ __('events.upcoming_events') }}
        </h1>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
        </a>
    </div>

    @if($events->count() > 0)
        <div class="upcoming-events-list">
            @foreach($events as $event)
                <div class="upcoming-event-card">
                    <div class="event-countdown">
                        @php
                            $daysUntil = now()->diffInDays($event->event_date, false);
                        @endphp
                        @if($daysUntil === 0)
                            <span class="countdown-badge today">{{ __('events.today') }}</span>
                        @elseif($daysUntil === 1)
                            <span class="countdown-badge tomorrow">{{ __('events.tomorrow') }}</span>
                        @else
                            <span class="countdown-badge">{{ trans_choice('events.days_away', $daysUntil) }}</span>
                        @endif
                    </div>

                    <div class="upcoming-event-content">
                        <div class="upcoming-event-header">
                            <span class="event-icon">{{ $event->icon }}</span>
                            <div class="upcoming-event-info">
                                <h3>{{ $event->title }}</h3>
                                <p>{{ __("events.types.{$event->event_type}") }} • {{ $event->formatted_date }}</p>
                            </div>
                        </div>

                        <div class="upcoming-event-user">
                            <img src="{{ $event->user->avatar_url }}" alt="{{ $event->user->username }}" class="upcoming-event-avatar">
                            <div class="upcoming-event-user-info">
                                <a href="{{ route('users.show', $event->user) }}" class="upcoming-event-user-name">{{ $event->user->username }}</a>
                                @if($event->is_anniversary && $event->years_since)
                                    <span class="anniversary-label">{{ __('events.anniversary') }} • {{ $event->years_since }} {{ __('events.years') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="upcoming-event-actions">
                        <a href="{{ route('events.show', $event->slug) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> {{ __('events.view') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-calendar-check"></i>
            <h3>{{ __('events.no_upcoming_events') }}</h3>
            <p>{{ __('events.no_upcoming_events_description') }}</p>
            <a href="{{ route('events.index') }}" class="btn btn-primary">
                <i class="fas fa-star"></i> {{ __('events.life_events') }}
            </a>
        </div>
    @endif
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.75rem;
    color: var(--text);
    margin: 0;
}

.upcoming-events-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.upcoming-event-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    display: flex;
    gap: 1.5rem;
    align-items: center;
    transition: transform 0.2s;
}

.upcoming-event-card:hover {
    transform: translateX(4px);
}

.event-countdown {
    min-width: 80px;
}

.countdown-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
}

.countdown-badge.today {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.countdown-badge.tomorrow {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.upcoming-event-content {
    flex: 1;
}

.upcoming-event-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.event-icon {
    font-size: 2.5rem;
}

.upcoming-event-info h3 {
    color: var(--text);
    margin: 0 0 0.25rem 0;
    font-size: 1.25rem;
}

.upcoming-event-info p {
    color: var(--text-muted);
    margin: 0;
    font-size: 0.9rem;
}

.upcoming-event-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.upcoming-event-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.upcoming-event-user-info {
    display: flex;
    flex-direction: column;
}

.upcoming-event-user-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    font-size: 0.95rem;
}

.upcoming-event-user-name:hover {
    color: var(--accent);
}

.anniversary-label {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.upcoming-event-actions {
    min-width: 100px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.empty-state i {
    font-size: 4rem;
    color: var(--accent);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: var(--text);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .upcoming-event-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .upcoming-event-actions {
        width: 100%;
    }

    .upcoming-event-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection
