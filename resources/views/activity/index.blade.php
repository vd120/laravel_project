@extends('layouts.app')

@section('title', __('activity.activity_logs'))

@section('content')
<div class="activity-container">
    <div class="activity-header">
        <h1><i class="fas fa-history"></i> {{ __('activity.activity_logs') }}</h1>
        <p class="activity-description">{{ __('activity.recent_activity') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="activity-stats">
        <div class="stat-card">
            <div class="stat-icon text-primary">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalLogins }}</div>
                <div class="stat-label">{{ __('activity.total_logins') }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon text-success">
                <i class="fas fa-laptop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $activeSessions }}</div>
                <div class="stat-label">{{ __('activity.active_sessions') }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon text-info">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $days }}</div>
                <div class="stat-label">{{ __('activity.days') }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="activity-filters">
        <form method="GET" action="{{ route('activity.index') }}" class="filter-form">
            <div class="filter-group">
                <label for="action">{{ __('activity.filter_by') }}</label>
                <select name="action" id="action" onchange="this.form.submit()">
                    @foreach($actions as $value => $label)
                        <option value="{{ $value }}" {{ $action === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="days">{{ __('activity.period') }}</label>
                <select name="days" id="days" onchange="this.form.submit()">
                    <option value="7" {{ $days === 7 ? 'selected' : '' }}>7 {{ __('activity.days') }}</option>
                    <option value="30" {{ $days === 30 ? 'selected' : '' }}>30 {{ __('activity.days') }}</option>
                    <option value="90" {{ $days === 90 ? 'selected' : '' }}>90 {{ __('activity.days') }}</option>
                    <option value="180" {{ $days === 180 ? 'selected' : '' }}>180 {{ __('activity.days') }}</option>
                    <option value="365" {{ $days === 365 ? 'selected' : '' }}>365 {{ __('activity.days') }}</option>
                </select>
            </div>
        </form>

        <form method="POST" action="{{ route('activity.clear') }}" class="clear-form" onsubmit="return confirm('{{ __('activity.clear_logs_confirm', ['days' => 90]) }}')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="days" value="90">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> {{ __('activity.clear_old_logs') }}
            </button>
        </form>
    </div>

    {{-- Activity List --}}
    <div class="activity-list">
        @forelse($activities as $activity)
            <div class="activity-item {{ $activity->action_color }}">
                <div class="activity-icon">
                    <i class="{{ $activity->action_icon }}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-header-row">
                        <span class="activity-action">{{ $activity->action_name }}</span>
                        <span class="activity-time">{{ $activity->logged_at->diffForHumans() }}</span>
                    </div>
                    <div class="activity-details">
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.device_type') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">{{ $activity->device_type ?? __('activity.unknown_device') }}</span>
                            <i class="{{ $activity->device_icon }}"></i>
                        </span>
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.browser') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">{{ $activity->browser }}</span>
                            <i class="fas fa-globe"></i>
                        </span>
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.operating_system') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">{{ $activity->os }}</span>
                            <i class="fas fa-desktop"></i>
                        </span>
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.ip_address') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">{{ $activity->ip_address }}</span>
                            <i class="fas fa-network-wired"></i>
                        </span>
                        @if($activity->isp)
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.isp') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">{{ $activity->isp }}</span>
                            <i class="fas fa-wifi"></i>
                        </span>
                        @endif
                        @if($activity->city || $activity->region || $activity->country)
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.location') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">
                                {{ $activity->city ?? '' }}{{ $activity->city && $activity->region ? ', ' : '' }}{{ $activity->region ?? '' }}{{ ($activity->city || $activity->region) && $activity->country ? ', ' : '' }}{{ $activity->country ?? '' }}
                            </span>
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        @endif
                        @if($activity->latitude && $activity->longitude)
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.coordinates') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">
                                <a href="https://www.google.com/maps?q={{ $activity->latitude }},{{ $activity->longitude }}" target="_blank" rel="noopener noreferrer" style="color: var(--primary);">
                                    {{ number_format($activity->latitude, 4) }}, {{ number_format($activity->longitude, 4) }}
                                </a>
                            </span>
                            <i class="fas fa-map"></i>
                        </span>
                        @endif
                        @if($activity->timezone)
                        <span class="activity-detail">
                            <span class="detail-label">{{ __('activity.timezone') }}</span>
                            <span class="detail-separator">:</span>
                            <span class="detail-value">
                                {{ $activity->timezone }}
                                @if($activity->logged_at)
                                    (<span style="color: var(--primary);">{{ $activity->logged_at->timezone(str_replace('_', '/', $activity->timezone))->format('h:i A') }}</span>)
                                @endif
                            </span>
                            <i class="fas fa-clock"></i>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="activity-badge">
                    @if($activity->action === 'login')
                        <span class="badge badge-success">{{ __('activity.login') }}</span>
                    @elseif($activity->action === 'logout')
                        <span class="badge badge-secondary">{{ __('activity.logout') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>{{ __('activity.no_activities') }}</h3>
                <p>{{ __('activity.no_activities_message') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($activities->hasPages())
        <div class="activity-pagination">
            {{ $activities->links() }}
        </div>
    @endif

    {{-- Security Tips --}}
    <div class="security-tips">
        <h3><i class="fas fa-shield-alt"></i> {{ __('activity.security_tips') }}</h3>
        <ul>
            <li><i class="fas fa-check"></i> {{ __('activity.security_tip_1') }}</li>
            <li><i class="fas fa-check"></i> {{ __('activity.security_tip_2') }}</li>
            <li><i class="fas fa-check"></i> {{ __('activity.security_tip_3') }}</li>
            <li><i class="fas fa-check"></i> {{ __('activity.security_tip_4') }}</li>
            <li><i class="fas fa-check"></i> {{ __('activity.security_tip_5') }}</li>
        </ul>
    </div>
</div>

<style>
.activity-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.activity-header {
    margin-bottom: 30px;
}

.activity-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.activity-description {
    color: var(--text-muted);
    font-size: 15px;
}

.activity-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: var(--bg-secondary);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text);
}

.stat-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 4px;
}

.activity-filters {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-form {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
}

.filter-group select {
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--text);
    font-size: 14px;
    min-width: 150px;
    cursor: pointer;
}

.activity-list {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px;
}

.activity-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    transition: background 0.2s;
    background: var(--bg);
    width: 100%;
}

.activity-item:last-child {
    border-bottom: 1px solid var(--border);
}

.activity-item:hover {
    background: var(--bg-secondary);
}

.activity-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    background: var(--bg-secondary);
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.activity-action {
    font-weight: 600;
    font-size: 15px;
    color: var(--text);
}

.activity-time {
    font-size: 13px;
    color: var(--text-muted);
    white-space: nowrap;
}

.activity-details {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.activity-detail {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-muted);
}

.detail-label {
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
}

.detail-separator {
    margin: 0 2px;
    opacity: 0.5;
}

.detail-value {
    color: var(--text-muted);
}

.detail-value a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.detail-value a:hover {
    text-decoration: underline;
}

.activity-detail i {
    font-size: 12px;
    flex-shrink: 0;
}

/* RTL Support for activity details */
[dir="rtl"] .activity-details {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .activity-detail {
    flex-direction: row-reverse;
}

[dir="rtl"] .detail-label {
    margin-left: 0;
    margin-right: 0;
}

/* For RTL, reverse the order so value comes first, then label */
[dir="rtl"] .activity-detail {
    direction: ltr;
}

[dir="rtl"] .activity-detail > *:nth-child(1) {
    order: 2; /* Value */
}

[dir="rtl"] .activity-detail > *:nth-child(2) {
    order: 1; /* Separator */
}

[dir="rtl"] .activity-detail > *:nth-child(3) {
    order: 0; /* Label */
}

[dir="rtl"] .activity-detail > *:nth-child(4) {
    order: 3; /* Icon */
}

.activity-badge {
    flex-shrink: 0;
}

.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.badge-secondary {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 64px;
    color: var(--text-muted);
    opacity: 0.5;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 20px;
    color: var(--text);
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--text-muted);
}

.security-tips {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
}

.security-tips h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.security-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-tips li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.6;
}

.security-tips li i {
    color: var(--primary);
    margin-top: 2px;
    flex-shrink: 0;
}

.activity-pagination {
    margin-top: 24px;
}

.text-primary { color: var(--primary); }
.text-success { color: #22c55e; }
.text-info { color: #06b6d4; }
.text-warning { color: #f59e0b; }
.text-muted { color: var(--text-muted); }

/* Mobile Responsive Styles */
@media (max-width: 640px) {
    .activity-container {
        padding: 12px;
    }
    
    .activity-header h1 {
        font-size: 20px;
    }
    
    .activity-stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-card {
        padding: 16px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .stat-value {
        font-size: 20px;
    }
    
    .activity-filters {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-form {
        width: 100%;
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-group select {
        width: 100%;
        min-width: 100%;
    }
    
    .clear-form {
        width: 100%;
    }
    
    .clear-form .btn {
        width: 100%;
        justify-content: center;
    }
    
    .activity-list {
        gap: 12px;
        padding: 12px;
    }
    
    .activity-item {
        padding: 12px;
        gap: 10px;
    }
    
    .activity-icon {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
    
    .activity-content {
        width: 100%;
        min-width: 100%;
        order: 3;
    }
    
    .activity-header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .activity-action {
        font-size: 14px;
    }
    
    .activity-time {
        font-size: 12px;
    }
    
    .activity-details {
        gap: 10px;
    }
    
    .activity-detail {
        width: 100%;
        font-size: 12px;
        justify-content: space-between;
    }
    
    .activity-detail i {
        font-size: 14px;
    }
    
    .detail-label {
        font-size: 11px;
    }
    
    .detail-value {
        font-size: 12px;
        text-align: right;
        flex: 1;
        margin-left: 8px;
    }
    
    .activity-badge {
        width: 100%;
        order: 2;
        margin-top: 8px;
    }
    
    .security-tips {
        padding: 16px;
    }
    
    .security-tips h3 {
        font-size: 16px;
    }
    
    .security-tips li {
        font-size: 13px;
    }
}

/* RTL Mobile Adjustments */
[dir="rtl"][dir="rtl"] .activity-detail {
    justify-content: space-between;
}

[dir="rtl"] .detail-value {
    text-align: left;
    margin-left: 0;
    margin-right: 8px;
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
}

.btn-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-danger:hover {
    background: rgba(239, 68, 68, 0.2);
}

@media (max-width: 640px) {
    .activity-container {
        padding: 16px;
    }

    .activity-stats {
        grid-template-columns: 1fr;
    }

    .activity-filters {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form {
        flex-direction: column;
    }

    .filter-group select {
        min-width: 100%;
    }

    .activity-item {
        flex-wrap: wrap;
    }

    .activity-details {
        gap: 12px;
    }

    .activity-badge {
        width: 100%;
        margin-top: 12px;
    }
}

/* RTL Support */
[dir="rtl"] .activity-header h1 {
    flex-direction: row-reverse;
}

[dir="rtl"] .activity-item {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .activity-details {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .activity-detail {
    flex-direction: row-reverse;
}

[dir="rtl"] .security-tips li {
    flex-direction: row-reverse;
}

[dir="rtl"] .detail-label {
    margin-left: 0;
    margin-right: 0;
}

[dir="rtl"] .activity-detail {
    direction: rtl;
}
</style>
@endsection
