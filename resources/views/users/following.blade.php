@extends('layouts.app')

@section('title', $user->username . ' - Following')

@section('content')
<style>
.users-list-container { max-width: 680px; margin: 0 auto; }
.page-header { margin-bottom: 24px; display: flex; flex-direction: column; gap: 8px; }
.page-header-top { display: flex; align-items: center; gap: 12px; }
.back-btn {
    display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px;
    background: var(--surface); border: 1px solid var(--border); border-radius: 50%; color: var(--text);
    text-decoration: none; transition: all var(--transition); flex-shrink: 0;
}
.back-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
.page-header h1 { font-size: 24px; font-weight: 800; color: var(--text); margin: 0; }
.page-header p { color: var(--text-muted); font-size: 14px; margin: 0; }

/* Button styles */
.btn-follow {
    padding: 6px 16px; border-radius: 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all var(--transition); border: none; min-width: 80px;
}
.btn-follow.primary { background: var(--primary); color: white; }
.btn-follow.primary:hover { background: #7c3aed; }
.btn-follow.secondary { background: transparent; border: 1px solid var(--border); color: var(--text); }
.btn-follow.secondary:hover { border-color: var(--primary); color: var(--primary); }

.users-grid { display: flex; flex-direction: column; gap: 12px; }
.user-card {
    display: flex; align-items: center; gap: 16px; padding: 16px 20px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
    transition: all var(--transition);
}
.user-card:hover { border-color: var(--primary); }
.user-avatar {
    width: 36px; height: 36px; border-radius: 50%; overflow: hidden;
    background: linear-gradient(135deg, var(--primary), var(--secondary)); flex-shrink: 0;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-avatar .placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: white;
}
.user-info { flex: 1; min-width: 0; }
.user-info a { text-decoration: none; }
.user-name { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-name:hover { color: var(--primary); }
.user-meta { font-size: 13px; color: var(--text-muted); }
.user-actions { display: flex; gap: 8px; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; opacity: 0.5; }
</style>

<div class="users-list-container">
    <div class="page-header">
        <div class="page-header-top">
            <a href="{{ route('users.show', $user) }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-user-friends"></i> Following</h1>
        </div>
        <p>{{ $user->username }} follows {{ $following->count() }} user{{ $following->count() !== 1 ? 's' : '' }}</p>
    </div>

    <div class="users-grid">
        @forelse($following as $follow)
        <div class="user-card">
            <a href="{{ route('users.show', $follow->followed) }}" class="user-avatar">
                <img src="{{ $follow->followed->avatar_url }}" alt="{{ $follow->followed->username }}">
            </a>
            <div class="user-info">
                <a href="{{ route('users.show', $follow->followed) }}">
                    <div class="user-name">{{ $follow->followed->username }}</div>
                </a>
                <div class="user-meta">@ {{ $follow->followed->username }}</div>
            </div>
            <div class="user-actions">
                @if(auth()->check() && auth()->id() === $user->id)
                    <button class="btn btn-ghost" onclick="followingPageUnfollow(this, '{{ $follow->followed->username }}')">
                        <i class="fas fa-user-minus"></i> Unfollow
                    </button>
                @elseif(auth()->check() && auth()->id() !== $follow->followed->id)
                    @php $isFollowing = in_array($follow->followed->id, $followingIds); @endphp
                    <button class="btn btn-sm {{ $isFollowing ? '' : 'btn-primary' }}" onclick="followingPageToggleFollow(this, '{{ $follow->followed->username }}')" data-following="{{ $isFollowing ? 'true' : 'false' }}">
                        {{ $isFollowing ? 'Following' : 'Follow' }}
                    </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-user-friends"></i>
            <h3>Not following anyone</h3>
            <p style="color: var(--text-muted);">{{ $user->username }} hasn't followed anyone yet.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function followingPageUnfollow(btn, username) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/users/${username}/follow`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
            'Accept': 'application/json' 
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Network response was not ok');
        return r.json();
    })
    .then(data => {
        // Reload the page to update the list
        window.location.reload();
    })
    .catch((error) => {
        console.error('Error:', error);
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function followingPageToggleFollow(btn, username) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/users/${username}/follow`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
            'Accept': 'application/json' 
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Network response was not ok');
        return r.json();
    })
    .then(data => {
        // Reload the page to update the list
        window.location.reload();
    })
    .catch((error) => {
        console.error('Error:', error);
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
</script>
@endsection
