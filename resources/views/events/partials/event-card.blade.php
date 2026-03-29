<div class="event-card" data-event-id="{{ $event->id }}">
    <div class="event-header">
        <div class="event-user">
            <img src="{{ $event->user->avatar_url }}" alt="{{ $event->user->username }}" class="event-user-avatar">
            <div class="event-user-info">
                <a href="{{ route('users.show', $event->user) }}" class="event-user-name">{{ $event->user->username }}</a>
                <span class="event-date">{{ $event->formatted_date }}</span>
            </div>
        </div>
        <div class="event-badge">
            <span class="event-icon">{{ $event->icon }}</span>
            <span class="event-type-label">{{ __("events.types.{$event->event_type}") }}</span>
        </div>
    </div>

    @if($event->is_anniversary && $event->years_since)
        <div class="anniversary-badge">
            <i class="fas fa-undo"></i>
            {{ __('events.anniversary') }} • {{ $event->years_since }} {{ __('events.years', ['count' => $event->years_since]) }}
        </div>
    @endif

    <div class="event-content">
        <h3 class="event-title">{{ $event->title }}</h3>
        @if($event->description)
            <p class="event-description">{{ $event->description }}</p>
        @endif
    </div>

    <div class="event-reactions">
        @if($event->reactions->count() > 0)
            <div class="reaction-summaries">
                @php
                    $groupedReactions = $event->reactions->groupBy('reaction_type');
                    $userReaction = $event->reactions->where('user_id', auth()->id())->first();
                @endphp
                @foreach($groupedReactions as $emoji => $reactions)
                    <span class="reaction-summary {{ $userReaction && $userReaction->reaction_type === $emoji ? 'user-reacted' : '' }}" title="{{ $reactions->pluck('user.name')->join(', ') }}">
                        {{ $emoji }} {{ $reactions->count() }}
                    </span>
                @endforeach
            </div>
        @endif
        
        @php
            $userReaction = $event->reactions->where('user_id', auth()->id())->first();
        @endphp
        @if($userReaction)
            <div class="your-reaction">
                <i class="fas fa-check-circle"></i> Your reaction: {{ $userReaction->reaction_type }}
            </div>
        @endif
    </div>

    <div class="event-actions">
        <button type="button" class="event-action-btn react-btn" onclick="toggleEventReactions('{{ $event->slug }}')">
            <i class="far fa-heart"></i> {{ __('events.react') }}
        </button>
        <a href="{{ route('events.show', $event->slug) }}" class="event-action-btn">
            <i class="far fa-comment"></i> {{ __('events.view') }}
        </a>
        @if(auth()->id() === $event->user_id)
            <a href="{{ route('events.edit', $event->slug) }}" class="event-action-btn">
                <i class="far fa-edit"></i> {{ __('messages.edit') }}
            </a>
            <button type="button" class="event-action-btn delete-btn" onclick="deleteEvent('{{ $event->slug }}')">
                <i class="far fa-trash"></i> {{ __('messages.delete') }}
            </button>
        @endif
    </div>

    <div class="event-reactions-panel" id="event-reactions-{{ $event->slug }}" style="display: none;">
        @php
            $allowedReactions = $event::REACTION_EMOJIS[$event->event_type] ?? ['🎉', '❤️', '👏'];
        @endphp
        @foreach($allowedReactions as $emoji)
            <button type="button" class="reaction-emoji" onclick="reactToEvent('{{ $event->slug }}', '{{ $emoji }}')">
                {{ $emoji }}
            </button>
        @endforeach
    </div>
</div>

<style>
.event-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.event-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.event-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.event-user-info {
    display: flex;
    flex-direction: column;
}

.event-user-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
}

.event-user-name:hover {
    color: var(--accent);
}

.event-date {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.event-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--accent-light);
    border-radius: 20px;
    font-weight: 600;
    color: var(--accent);
}

.event-icon {
    font-size: 1.25rem;
}

.anniversary-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.event-content {
    margin: 1rem 0;
}

.event-title {
    font-size: 1.25rem;
    color: var(--text);
    margin: 0 0 0.5rem 0;
}

.event-description {
    color: var(--text-muted);
    line-height: 1.6;
    margin: 0;
}

.event-reactions {
    margin: 1rem 0;
}

.reaction-summaries {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.reaction-summary {
    background: var(--bg);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.9rem;
}

.event-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.event-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: var(--text-muted);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.event-action-btn:hover {
    background: var(--accent-light);
    color: var(--accent);
}

.event-action-btn.delete-btn:hover {
    background: #fee2e2;
    color: #ef4444;
}

.event-reactions-panel {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    margin-top: 1rem;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}

.reaction-emoji {
    background: var(--bg);
    border: none;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.2s;
}

.reaction-emoji:hover {
    transform: scale(1.2);
    background: var(--accent-light);
}

.reaction-summary.user-reacted {
    background: var(--accent);
    color: white;
    font-weight: 600;
}

.your-reaction {
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
</style>

<script>
function toggleEventReactions(eventSlug) {
    const panel = document.getElementById(`event-reactions-${eventSlug}`);
    panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
}

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

function deleteEvent(eventSlug) {
    if (!confirm('{{ __('messages.are_you_sure') }}')) {
        return;
    }

    // Use absolute URL to avoid path resolution issues
    const deleteUrl = window.location.origin + '/life-events/' + eventSlug;
    
    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            return response.json().then(err => { throw err; });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || '{{ __('messages.error_occurred') }}');
    });
}
</script>
