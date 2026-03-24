@extends('layouts.app')

@section('title', __('admin.report_details') . ' - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.reports') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>{{ __('admin.report_details') }}</h1>
                <p>{{ __('admin.report_details_subtitle') }}</p>
            </div>
        </div>
        <div class="header-actions">
            <span class="status-badge {{ $report->status }}">
                @if($report->status === 'pending')
                    <i class="fas fa-clock"></i> {{ __('admin.pending') }}
                @elseif($report->status === 'accepted')
                    <i class="fas fa-check-circle"></i> {{ __('admin.accepted') }}
                @else
                    <i class="fas fa-times-circle"></i> {{ __('admin.rejected') }}
                @endif
            </span>
        </div>
    </div>

    {{-- Report Content --}}
    <div class="report-detail-section">
        {{-- Report Information --}}
        <div class="detail-card">
            <h2><i class="fas fa-flag"></i> {{ __('admin.report_information') }}</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>{{ __('admin.report_slug') }}:</label>
                    <span class="slug-text">{{ $report->slug }}</span>
                </div>
                <div class="detail-item">
                    <label>{{ __('admin.reason') }}:</label>
                    <span class="reason-badge">{{ \App\Models\PostReport::REASONS[$report->reason] ?? $report->reason }}</span>
                </div>
                <div class="detail-item">
                    <label>{{ __('admin.reported_by') }}:</label>
                    <span>{{ $report->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="detail-item">
                    <label>{{ __('admin.status') }}:</label>
                    <span class="status-badge {{ $report->status }}">
                        @if($report->status === 'pending')
                            {{ __('admin.pending') }}
                        @elseif($report->status === 'accepted')
                            {{ __('admin.accepted') }}
                        @else
                            {{ __('admin.rejected') }}
                        @endif
                    </span>
                </div>
            </div>

            @if($report->content)
            <div class="detail-section">
                <label>{{ __('admin.additional_details') }}:</label>
                <p class="content-text">{{ $report->content }}</p>
            </div>
            @endif

            @if($report->reviewed_by)
            <div class="detail-section">
                <label>{{ __('admin.review_information') }}:</label>
                <div class="review-info">
                    <span><strong>{{ __('admin.reviewed_by') }}:</strong> {{ $report->reviewer->username ?? 'Unknown' }}</span>
                    <span><strong>{{ __('admin.reviewed_at') }}:</strong> {{ $report->reviewed_at->format('M d, Y H:i') }}</span>
                    @if($report->admin_note)
                    <div class="admin-note">
                        <strong>{{ __('admin.admin_note') }}:</strong>
                        <p>{{ $report->admin_note }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Reporter Information --}}
        <div class="detail-card">
            <h2><i class="fas fa-user"></i> {{ __('admin.reporter_information') }}</h2>
            <div class="user-profile">
                <img src="{{ $report->reporter->avatar_url }}" alt="" class="profile-avatar">
                <div class="profile-info">
                    <h3>{{ $report->reporter->username }}</h3>
                    <p>{{ $report->reporter->name }}</p>
                    <p class="email">{{ $report->reporter->email }}</p>
                    <div class="profile-stats">
                        <span><strong>{{ $report->reporter->posts->count() }}</strong> {{ __('admin.posts') }}</span>
                        <span><strong>{{ $report->reporter->followers->count() }}</strong> {{ __('admin.followers') }}</span>
                        <span><strong>{{ $report->reporter->following->count() }}</strong> {{ __('admin.following') }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $report->reporter) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-user-circle"></i> {{ __('admin.view_profile') }}
            </a>
        </div>

        {{-- Reported Post --}}
        <div class="detail-card">
            <h2><i class="fas fa-newspaper"></i> {{ __('admin.reported_post') }}</h2>
            @if($report->post)
            <div class="post-detail">
                <div class="post-header">
                    <img src="{{ $report->post->user->avatar_url }}" alt="" class="avatar">
                    <div class="post-meta">
                        <span class="author">{{ $report->post->user->username }}</span>
                        <span class="date">{{ $report->post->created_at->diffForHumans() }}</span>
                        @if($report->post->is_private)
                        <span class="private-badge"><i class="fas fa-lock"></i> {{ __('admin.private') }}</span>
                        @endif
                    </div>
                </div>

                @if($report->post->content)
                <div class="post-content">
                    <p>{{ $report->post->content }}</p>
                </div>
                @endif

                @if($report->post->media->count() > 0)
                <div class="post-media">
                    @foreach($report->post->media as $media)
                        @if($media->media_type === 'image')
                            <img src="{{ asset('storage/' . $media->media_path) }}" alt="">
                        @elseif($media->media_type === 'video')
                            <video controls>
                                <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                            </video>
                        @endif
                    @endforeach
                </div>
                @endif

                <div class="post-stats">
                    <span><i class="fas fa-heart"></i> {{ $report->post->likes->count() }} {{ __('admin.likes') }}</span>
                    <span><i class="fas fa-comment"></i> {{ $report->post->comments->count() }} {{ __('admin.comments') }}</span>
                </div>

                <a href="{{ route('posts.show', $report->post->slug) }}" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="fas fa-external-link-alt"></i> {{ __('admin.view_post') }}
                </a>
            </div>
            @else
            <div class="status-message rejected">
                <i class="fas fa-trash-alt"></i>
                <h3>{{ __('admin.post_deleted') }}</h3>
                <p>{{ __('admin.post_no_longer_available') }}</p>
            </div>
            @endif
        </div>

        {{-- Action Section (only for pending reports) --}}
        @if($report->isPending() && $report->post)
        <div class="detail-card action-card">
            <h2><i class="fas fa-gavel"></i> {{ __('admin.take_action_on_report') }}</h2>

            <form method="POST" action="{{ route('admin.reports.accept', $report) }}" class="action-form" id="accept-form">
                @csrf
                <input type="hidden" name="action" value="delete" id="action-input">

                <div class="action-options">
                    <h3>{{ __('admin.select_action') }}:</h3>
                    <div class="option-group">
                        <label class="option-card">
                            <input type="radio" name="action" value="delete" checked>
                            <div class="option-content">
                                <i class="fas fa-trash"></i>
                                <span>{{ __('admin.delete_post') }}</span>
                                <small>{{ __('admin.delete_post_description') }}</small>
                            </div>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="action" value="hide">
                            <div class="option-content">
                                <i class="fas fa-eye-slash"></i>
                                <span>{{ __('admin.hide_post') }}</span>
                                <small>{{ __('admin.hide_post_description') }}</small>
                            </div>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="action" value="warning">
                            <div class="option-content">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>{{ __('admin.issue_warning') }}</span>
                                <small>{{ __('admin.issue_warning_description') }}</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_note_accept">{{ __('admin.admin_note') }}:</label>
                    <textarea name="admin_note" id="admin_note_accept" rows="3" placeholder="{{ __('admin.admin_note_placeholder') }}"></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-success" onclick="document.getElementById('action-input').value = document.querySelector('input[name=\'action\']:checked').value">
                        <i class="fas fa-check"></i> {{ __('admin.accept_report_and_take_action') }}
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('reject-form').classList.toggle('hidden')">
                        <i class="fas fa-times"></i> {{ __('admin.reject_report') }}
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.reports.reject', $report) }}" class="action-form hidden" id="reject-form">
                @csrf
                <div class="form-group">
                    <label for="admin_note_reject">{{ __('admin.admin_note') }} ({{ __('admin.optional') }}):</label>
                    <textarea name="admin_note" id="admin_note_reject" rows="3" placeholder="{{ __('admin.reject_note_placeholder') }}"></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('admin.reject_report') }}
                    </button>
                    <button type="button" class="btn btn-link" onclick="document.getElementById('reject-form').classList.add('hidden')">
                        {{ __('admin.cancel') }}
                    </button>
                </div>
            </form>
        </div>
        @elseif($report->isPending() && !$report->post)
        <div class="detail-card">
            <h2><i class="fas fa-info-circle"></i> {{ __('admin.report_status') }}</h2>
            <div class="status-message">
                <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                <h3>{{ __('admin.post_already_deleted') }}</h3>
                <p>{{ __('admin.post_already_deleted_message') }}</p>
                <div class="action-buttons" style="justify-content: center; margin-top: 1rem;">
                    <form method="POST" action="{{ route('admin.reports.reject', $report) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-check"></i> {{ __('admin.mark_as_reviewed') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
        {{-- Already processed --}}
        <div class="detail-card">
            <h2><i class="fas fa-info-circle"></i> {{ __('admin.report_status') }}</h2>
            <div class="status-message {{ $report->status }}">
                @if($report->status === 'accepted')
                    <i class="fas fa-check-circle"></i>
                    <h3>{{ __('admin.report_accepted_title') }}</h3>
                    <p>{{ __('admin.report_accepted_message') }}</p>
                @else
                    <i class="fas fa-times-circle"></i>
                    <h3>{{ __('admin.report_rejected_title') }}</h3>
                    <p>{{ __('admin.report_rejected_message') }}</p>
                @endif
                @if($report->admin_note)
                <div class="admin-note">
                    <strong>{{ __('admin.admin_note') }}:</strong>
                    <p>{{ $report->admin_note }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
<script>
// Add click handlers for option cards to improve selection visibility
document.addEventListener('DOMContentLoaded', function() {
    const optionCards = document.querySelectorAll('.option-card');
    
    optionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking directly on the radio
            if (e.target.tagName !== 'INPUT') {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Update visual feedback
                    optionCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                }
            }
        });
    });
    
    // Initialize first option as selected
    const firstRadio = document.querySelector('.option-card input[type="radio"]:checked');
    if (firstRadio) {
        firstRadio.closest('.option-card').classList.add('selected');
    }
});
</script>
@endsection
