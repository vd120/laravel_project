@extends('layouts.app')

@section('title', __('events.edit_event'))

@section('content')
<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas fa-edit"></i>
            {{ __('events.edit_event') }}
        </h1>
        <a href="{{ route('events.memory-book', auth()->user()) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
        </a>
    </div>

    <div class="form-card">
        <form action="{{ route('events.update', $event) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="event_type">{{ __('events.event_type') }}</label>
                <select name="event_type" id="event_type" class="form-control" required>
                    <option value="">{{ __('events.select_event_type') }}</option>
                    @foreach($eventTypes as $value => $label)
                        <option value="{{ $value }}" 
                                data-icon="{{ $eventIcons[$value] ?? '🎉' }}"
                                {{ old('event_type', $event->event_type) === $value ? 'selected' : '' }}>
                            {{ $eventIcons[$value] ?? '🎉' }} {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('event_type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="title">{{ __('events.title') }}</label>
                <input type="text" name="title" id="title" class="form-control"
                       value="{{ old('title', $event->title) }}" required maxlength="255"
                       placeholder="{{ __('events.title_placeholder') }}">
                @error('title')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">{{ __('events.description') }}</label>
                <textarea name="description" id="description" class="form-control"
                          rows="4" maxlength="1000"
                          placeholder="{{ __('events.description_placeholder') }}">{{ old('description', $event->description) }}</textarea>
                <small class="form-hint">{{ __('events.description_help') }}</small>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">{{ __('events.event_date') }}</label>
                    <input type="date" name="event_date" id="event_date" class="form-control"
                           value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required>
                    @error('event_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="year">{{ __('events.year') }}</label>
                    <input type="number" name="year" id="year" class="form-control"
                           value="{{ old('year', $event->year) }}" min="1900" max="{{ date('Y') }}"
                           placeholder="{{ date('Y') }}">
                    <small class="form-hint">{{ __('events.year_help') }}</small>
                    @error('year')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_private" value="1"
                           {{ old('is_private', $event->is_private) ? 'checked' : '' }}>
                    <span>{{ __('events.make_private') }}</span>
                </label>
                <small class="form-hint">{{ __('events.private_help') }}</small>
                @error('is_private')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('messages.save_changes') }}
                </button>
                <a href="{{ route('events.memory-book', auth()->user()) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ __('messages.cancel') }}
                </a>
            </div>
        </form>
    </div>
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

.page-header h1 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    color: var(--text);
    margin: 0;
}

.form-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 1rem;
    background: var(--input-bg);
    color: var(--text);
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.error-message {
    display: block;
    margin-top: 0.25rem;
    color: #dc3545;
    font-size: 0.85rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
}

.btn-secondary {
    background: var(--secondary-color);
    color: white;
}

.btn-secondary:hover {
    background: var(--secondary-hover);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection
