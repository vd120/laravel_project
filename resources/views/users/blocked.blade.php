@extends('layouts.app')

@section('content')
<div class="blocked-page">
    <div class="page-header">
        <h1>Blocked Users</h1>
        <a href="{{ route('users.show', $user) }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to profile</span>
        </a>
    </div>

    @if($blocked->count() > 0)
        <div class="users-grid">
            @foreach($blocked as $block)
                <div class="user-card">
                    @if($block->blocked->profile && $block->blocked->profile->avatar)
                        <img src="{{ asset('storage/' . $block->blocked->profile->avatar) }}" alt="Avatar" class="user-avatar">
                    @else
                        <div class="user-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif

                    <div class="user-info">
                        <h3><a href="{{ route('users.show', $block->blocked) }}">{{ $block->blocked->name }}</a></h3>
                        @if($block->blocked->profile && $block->blocked->profile->bio)
                            <p class="user-bio">{{ Str::limit($block->blocked->profile->bio, 100) }}</p>
                        @endif
                        <div class="user-stats">
                            <span>{{ $block->blocked->followers->count() }} followers</span>
                            <span>{{ $block->blocked->follows->count() }} following</span>
                        </div>
                        <div class="block-info">
                            <small class="text-muted">Blocked {{ $block->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    <button type="button" class="btn unblock-btn" data-user-id="{{ $block->blocked->id }}" data-username="{{ $block->blocked->name }}" onclick="toggleBlock(this)">Unblock</button>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-user-shield"></i>
            <h3>No blocked users</h3>
            <p>You haven't blocked any users yet.</p>
            <p>When you block someone, they won't be able to see your posts or interact with you.</p>
        </div>
    @endif
</div>

<style>
.blocked-page {
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
    margin-bottom: 16px;
}

.back-link a {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s ease;
}

.back-link a:hover {
    color: var(--twitter-dark);
}

.users-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    transition: box-shadow 0.2s ease;
}

.user-card:hover {
    box-shadow: var(--shadow);
}

.user-avatar,
.user-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--border-color);
    flex-shrink: 0;
}

.user-avatar {
    object-fit: cover;
}

.user-avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 18px;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
}

.user-info h3 a {
    color: var(--twitter-dark);
    text-decoration: none;
    transition: color 0.2s ease;
}

.user-info h3 a:hover {
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
    margin-bottom: 4px;
}

.block-info {
    margin-top: 8px;
}

.block-info small {
    color: #6c757d;
    font-size: 11px;
}

.unblock-btn {
    background: #ffc107;
    color: #212529;
    border: none;
    border-radius: 16px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.unblock-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.empty-state {
    text-align: center;
    padding: 48px 16px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 48px;
    opacity: 0.5;
    margin-bottom: 16px;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.empty-state p {
    margin: 0 0 8px 0;
    font-size: 14px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .blocked-page {
        padding: 12px;
    }

    .page-header h1 {
        font-size: 20px;
    }

    .user-card {
        padding: 12px;
        gap: 10px;
    }

    .user-avatar,
    .user-avatar-placeholder {
        width: 42px;
        height: 42px;
    }

    .user-avatar-placeholder {
        font-size: 16px;
    }

    .user-info h3 {
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

    .unblock-btn {
        padding: 6px 10px;
        font-size: 12px;
        min-height: 26px;
    }

    .empty-state {
        padding: 32px 12px;
    }

    .empty-state i {
        font-size: 36px;
    }

    .empty-state h3 {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .blocked-page {
        padding: 10px;
    }

    .page-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
    }

    .page-header h1 {
        font-size: 18px;
    }

    .user-card {
        padding: 10px;
        gap: 8px;
    }

    .user-avatar,
    .user-avatar-placeholder {
        width: 38px;
        height: 38px;
    }

    .user-avatar-placeholder {
        font-size: 15px;
    }

    .user-info h3 {
        font-size: 14px;
    }

    .user-bio {
        font-size: 12px;
        margin-bottom: 4px;
    }

    .user-stats {
        gap: 6px;
        font-size: 10px;
    }

    .block-info small {
        font-size: 10px;
    }

    .unblock-btn {
        padding: 5px 8px;
        font-size: 11px;
        min-height: 24px;
    }

    .empty-state {
        padding: 28px 10px;
    }

    .empty-state i {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .empty-state h3 {
        font-size: 15px;
    }

    .empty-state p {
        font-size: 13px;
    }
}

@media (max-width: 360px) {
    .blocked-page {
        padding: 8px;
    }

    .page-header h1 {
        font-size: 16px;
    }

    .user-card {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding: 12px;
    }

    .user-avatar,
    .user-avatar-placeholder {
        width: 44px;
        height: 44px;
        align-self: center;
        border-width: 2px;
    }

    .user-avatar-placeholder {
        font-size: 18px;
    }

    .user-info {
        text-align: center;
    }

    .user-info h3 {
        font-size: 16px;
        margin-bottom: 6px;
    }

    .user-bio {
        font-size: 13px;
        margin-bottom: 8px;
        text-align: center;
    }

    .user-stats {
        justify-content: center;
        gap: 10px;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .block-info {
        text-align: center;
        margin-top: 6px;
    }

    .block-info small {
        font-size: 11px;
    }

    .unblock-btn {
        width: 100%;
        padding: 8px 12px;
        font-size: 13px;
        min-height: 32px;
        margin-top: 8px;
    }

    .empty-state {
        padding: 32px 8px;
    }

    .empty-state i {
        font-size: 40px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 16px;
    }

    .empty-state p {
        font-size: 14px;
    }
}
</style>
@endsection
