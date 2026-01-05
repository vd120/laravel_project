@extends('layouts.app')

@section('content')
<div class="followers-page">
    <div class="page-header">
        <h1>{{ $user->name }}'s Followers</h1>
        <a href="{{ route('users.show', $user) }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to profile</span>
        </a>
    </div>

    @if($followers->count() > 0)
        <div class="users-list">
            @foreach($followers as $follow)
                <div class="user-card">
                    <div class="user-avatar-section">
                        @if($follow->follower->profile && $follow->follower->profile->avatar)
                            <img src="{{ asset('storage/' . $follow->follower->profile->avatar) }}" alt="Avatar" class="user-avatar">
                        @else
                            <div class="user-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>

                    <div class="user-content">
                        <div class="user-header">
                            <h3 class="user-name">
                                <a href="{{ route('users.show', $follow->follower) }}">{{ $follow->follower->name }}</a>
                            </h3>
                        </div>

                        @if($follow->follower->profile && $follow->follower->profile->bio)
                            <p class="user-bio">{{ Str::limit($follow->follower->profile->bio, 120) }}</p>
                        @endif

                        <div class="user-stats">
                            <span class="stat-item">
                                <strong>{{ $follow->follower->followers->count() }}</strong> followers
                            </span>
                            <span class="stat-item">
                                <strong>{{ $follow->follower->follows->count() }}</strong> following
                            </span>
                        </div>
                    </div>

                    @if($follow->follower_id !== auth()->id())
                        <div class="user-actions">
                            <button type="button"
                                    class="follow-btn {{ auth()->user()->isFollowing($follow->follower) ? 'following' : '' }}"
                                    data-user-id="{{ $follow->follower->id }}"
                                    data-username="{{ $follow->follower->name }}"
                                    onclick="toggleFollow(this, {{ $follow->follower->id }})">
                                <span class="btn-text">{{ auth()->user()->isFollowing($follow->follower) ? 'Following' : 'Follow' }}</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>No followers yet</h3>
            <p>{{ $user->name }} doesn't have any followers.</p>
        </div>
    @endif
</div>

<style>
.followers-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 16px;
}

.page-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--twitter-dark);
}

.back-link i {
    font-size: 12px;
}

.users-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.user-card:hover {
    box-shadow: var(--shadow);
    transform: translateY(-1px);
    border-color: var(--twitter-blue);
}

.user-avatar-section {
    flex-shrink: 0;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.user-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--twitter-light);
    border: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 18px;
}

.user-content {
    flex: 1;
    min-width: 0; /* Allow text to wrap */
}

.user-header {
    margin-bottom: 4px;
}

.user-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.user-name a {
    color: var(--twitter-dark);
    text-decoration: none;
    transition: color 0.2s ease;
}

.user-name a:hover {
    color: var(--twitter-blue);
}

.user-bio {
    margin: 4px 0 8px 0;
    font-size: 14px;
    color: var(--twitter-gray);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.user-stats {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--twitter-gray);
}

.stat-item strong {
    color: var(--twitter-dark);
}

.user-actions {
    flex-shrink: 0;
}

.follow-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.follow-btn:not(.following) {
    background: var(--twitter-blue);
    color: white;
}

.follow-btn.following {
    background: #28a745;
    color: white;
}

.follow-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.empty-state {
    text-align: center;
    padding: 48px 16px;
    color: var(--twitter-gray);
}

.empty-icon {
    margin-bottom: 16px;
}

.empty-icon i {
    font-size: 48px;
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

/* Mobile Responsive */
@media (max-width: 480px) {
    .followers-page {
        padding: 12px;
    }

    .page-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
    }

    .page-header h1 {
        font-size: 20px;
    }

    .back-link {
        font-size: 13px;
    }

    .user-card {
        padding: 12px;
        gap: 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
    }

    .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .user-name {
        font-size: 15px;
    }

    .user-bio {
        font-size: 13px;
        margin-bottom: 6px;
    }

    .user-stats {
        gap: 8px;
        font-size: 11px;
    }

    .follow-btn {
        padding: 6px 12px;
        font-size: 13px;
        min-height: 28px;
    }

    .empty-state {
        padding: 32px 12px;
    }

    .empty-icon i {
        font-size: 36px;
    }

    .empty-state h3 {
        font-size: 16px;
    }
}

@media (max-width: 360px) {
    .user-card {
        flex-direction: column;
        align-items: stretch;
    }

    .user-avatar-section {
        align-self: center;
        margin-bottom: 8px;
    }

    .user-actions {
        align-self: stretch;
        margin-top: 12px;
    }

    .follow-btn {
        width: 100%;
    }
}
</style>
@endsection
