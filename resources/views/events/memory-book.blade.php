@extends('layouts.app')

@section('title', __('events.memory_book'))

@section('content')
<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas fa-book"></i>
            {{ __('events.memory_book') }}
        </h1>
        <div class="header-actions">
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('events.add_event') }}
            </a>
            <a href="{{ route('events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="memory-book">
        @foreach($eventTypes as $type => $label)
            @if(isset($memoryBook[$type]) && $memoryBook[$type]->count() > 0)
                <section class="memory-section">
                    <div class="memory-section-header">
                        <h2>
                            <span class="memory-icon">{{ $eventIcons[$type] ?? '🎉' }}</span>
                            {{ $label }}
                        </h2>
                        <span class="memory-count">{{ $memoryBook[$type]->count() }}</span>
                    </div>

                    <div class="memory-grid">
                        @foreach($memoryBook[$type] as $event)
                            <div class="memory-card" data-event-slug="{{ $event->slug }}">
                                <div class="memory-badge">
                                    <span class="memory-icon-large">{{ $event->icon }}</span>
                                    @if($event->is_anniversary && $event->years_since)
                                        <span class="anniversary-tag">
                                            {{ $event->years_since }} {{ __('events.years') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="memory-content">
                                    <h3 class="memory-title">{{ $event->title }}</h3>
                                    @if($event->description)
                                        <p class="memory-description">{{ Str::limit($event->description, 100) }}</p>
                                    @endif
                                    <span class="memory-date">{{ $event->event_date->format(__('messages.date_format')) }}</span>
                                </div>

                                <div class="memory-actions">
                                    <a href="{{ route('events.show', $event->slug) }}" class="memory-action-btn">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->id() === $event->user_id)
                                        <a href="{{ route('events.edit', $event->slug) }}" class="memory-action-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="memory-action-btn delete-btn" onclick="deleteEvent('{{ $event->slug }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if($memoryBook->isEmpty())
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>{{ __('events.no_memory_book_yet') }}</h3>
                <p>{{ __('events.no_memory_book_description') }}</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('events.add_event') }}
                </a>
            </div>
        @endif
    </div>
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

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.memory-book {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}

.memory-section {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.memory-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--accent-light);
}

.memory-section-header h2 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    color: var(--text);
    margin: 0;
}

.memory-icon {
    font-size: 1.75rem;
}

.memory-count {
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
}

.memory-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.memory-card {
    background: var(--bg);
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.2s;
    border: 1px solid var(--border);
}

.memory-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
    border-color: var(--accent);
}

.memory-badge {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.memory-icon-large {
    font-size: 2.5rem;
}

.anniversary-tag {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.memory-content {
    margin-bottom: 1rem;
}

.memory-title {
    font-size: 1.1rem;
    color: var(--text);
    margin: 0 0 0.5rem 0;
    font-weight: 600;
}

.memory-description {
    color: var(--text-muted);
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0 0 0.5rem 0;
}

.memory-date {
    display: block;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.memory-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.memory-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--card-bg);
    color: var(--text-muted);
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.memory-action-btn:hover {
    background: var(--accent-light);
    color: var(--accent);
}

.memory-action-btn.delete-btn:hover {
    background: #fee2e2;
    color: #ef4444;
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

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .memory-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function deleteEvent(eventSlug) {
    if (!confirm('{{ __('messages.are_you_sure') }}')) {
        return;
    }

    // Find the event card and remove it
    const eventCard = document.querySelector(`.memory-card[data-event-slug="${eventSlug}"]`);

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
            // Reload the page to update the UI
            window.location.reload();
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
@endsection
