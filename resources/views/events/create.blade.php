@extends('layouts.app')

@section('title', __('events.add_event'))

@section('content')
<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas fa-plus-circle"></i>
            {{ __('events.add_event') }}
        </h1>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
        </a>
    </div>

    <div class="form-card">
        <form action="{{ route('events.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="event_type">{{ __('events.event_type') }}</label>
                <select name="event_type" id="event_type" class="form-control" required>
                    <option value="">{{ __('events.select_event_type') }}</option>
                    @foreach($eventTypes as $value => $label)
                        <option value="{{ $value }}" data-icon="{{ $eventIcons[$value] ?? '🎉' }}">
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
                       value="{{ old('title') }}" required maxlength="255"
                       placeholder="{{ __('events.title_placeholder') }}">
                @error('title')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">{{ __('events.description') }}</label>
                <textarea name="description" id="description" class="form-control" 
                          rows="4" maxlength="1000"
                          placeholder="{{ __('events.description_placeholder') }}">{{ old('description') }}</textarea>
                <small class="form-hint">{{ __('events.description_help') }}</small>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">{{ __('events.event_date') }}</label>
                    <input type="date" name="event_date" id="event_date" class="form-control" 
                           value="{{ old('event_date', date('Y-m-d')) }}" required>
                    @error('event_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="year">{{ __('events.year') }}</label>
                    <input type="number" name="year" id="year" class="form-control" 
                           value="{{ old('year', date('Y')) }}" min="1900" max="{{ date('Y') }}"
                           placeholder="{{ date('Y') }}">
                    <small class="form-hint">{{ __('events.year_help') }}</small>
                    @error('year')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_anniversary" id="is_anniversary" value="1" {{ old('is_anniversary') ? 'checked' : '' }}>
                    <span>{{ __('events.is_anniversary') }}</span>
                </label>
                <small class="form-hint">{{ __('events.anniversary_help') }}</small>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_private" id="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}>
                    <span>{{ __('events.is_private') }}</span>
                </label>
                <small class="form-hint">{{ __('events.private_help') }}</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('messages.save') }}
                </button>
                <a href="{{ route('events.index') }}" class="btn btn-secondary">
                    {{ __('messages.cancel') }}
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
}

.page-header h1 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.75rem;
    color: var(--text);
    margin: 0;
}

.form-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
    max-width: 700px;
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
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 1rem;
    background: var(--bg);
    color: var(--text);
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--text-muted);
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
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.error-message {
    display: block;
    margin-top: 0.25rem;
    color: #ef4444;
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventTypeSelect = document.getElementById('event_type');
    const isAnniversaryCheckbox = document.getElementById('is_anniversary');
    const yearInput = document.getElementById('year');

    eventTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const icon = selectedOption.getAttribute('data-icon');
        
        // Auto-fill title based on event type
        if (this.value && !document.getElementById('title').value) {
            const typeLabel = selectedOption.textContent.replace(icon, '').trim();
            document.getElementById('title').placeholder = `{{ __('events.my') }} ${typeLabel}`;
        }
    });

    isAnniversaryCheckbox.addEventListener('change', function() {
        if (this.checked) {
            yearInput.required = true;
            yearInput.value = new Date().getFullYear() - 1;
        } else {
            yearInput.required = false;
            yearInput.value = '';
        }
    });
});
</script>
@endsection
