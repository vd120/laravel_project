@extends('layouts.app')

@section('title', __('events.life_events'))

@section('content')
<div class="container">
    <div class="page-header">
        <div class="header-content">
            <h1>
                <i class="fas fa-star"></i>
                {{ __('events.life_events') }}
            </h1>
            <p class="page-subtitle">{{ __('events.celebrate_moments') }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('events.add_event') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Links - Modern Design --}}
    <div class="quick-links-container">
        <a href="{{ route('events.memory-book', auth()->user()) }}" class="quick-link-card">
            <div class="quick-link-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-book"></i>
            </div>
            <span class="quick-link-label">{{ __('events.memory_book') }}</span>
        </a>
        <a href="{{ route('events.upcoming') }}" class="quick-link-card">
            <div class="quick-link-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="quick-link-label">{{ __('events.upcoming_events') }}</span>
        </a>
        <a href="{{ route('events.create') }}" class="quick-link-card">
            <div class="quick-link-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-plus-circle"></i>
            </div>
            <span class="quick-link-label">{{ __('events.add_event') }}</span>
        </a>
    </div>

    @if($events->count() > 0)
        <div class="events-feed">
            @foreach($events as $event)
                @include('events.partials.event-card', ['event' => $event])
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-star"></i>
            <h3>{{ __('events.no_events_yet') }}</h3>
            <p>{{ __('events.no_events_description') }}</p>
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('events.add_event') }}
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
    padding: 1.5rem;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.page-header h1 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin: 0;
}

.header-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    gap: 0.5rem;
}

/* Quick Links - Modern Design */
.quick-links-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.quick-link-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem 1.5rem;
    background: var(--card-bg);
    border-radius: 16px;
    box-shadow: var(--shadow);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
}

.quick-link-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.3);
    border-color: var(--accent);
}

.quick-link-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s;
}

.quick-link-card:hover .quick-link-icon {
    transform: scale(1.1) rotate(5deg);
}

.quick-link-label {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text);
    text-align: center;
}

.events-feed {
    display: grid;
    gap: 1.5rem;
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
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem;
    }

    .header-actions {
        width: 100%;
    }

    .header-actions .btn {
        width: 100%;
        justify-content: center;
        padding: 0.6rem 0.8rem;
        font-size: 0.8rem;
    }
    
    .page-header h1 {
        font-size: 1.3rem;
    }

    .quick-links-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .quick-link-card {
        padding: 1.5rem 1rem;
    }
    
    .quick-link-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .quick-link-label {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .quick-links-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .quick-link-card {
        padding: 1.25rem 0.75rem;
    }
    
    .quick-link-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
}
</style>
@endsection
