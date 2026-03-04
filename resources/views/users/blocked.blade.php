@extends('layouts.app')

@section('title', 'Blocked Users')

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

.users-grid { display: flex; flex-direction: column; gap: 12px; }
.user-card { 
    display: flex; align-items: center; gap: 16px; padding: 16px 20px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
    transition: all var(--transition);
}
.user-card:hover { border-color: var(--accent); }
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
.user-name { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.user-name:hover { color: var(--primary); }
.user-meta { font-size: 13px; color: var(--text-muted); }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; opacity: 0.5; }

.blocked-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
    background: rgba(244, 63, 94, 0.1); color: var(--accent); font-size: 12px;
    border-radius: var(--radius-full); font-weight: 600;
}
</style>

<div class="users-list-container">
    <div class="page-header">
        <div class="page-header-top">
            <a href="{{ route('home') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-ban"></i> Blocked Users</h1>
        </div>
        <p>You have blocked {{ $blocked->count() }} user{{ $blocked->count() !== 1 ? 's' : '' }}</p>
    </div>

    <div class="users-grid">
        @forelse($blocked as $block)
        <div class="user-card">
            <a href="{{ route('users.show', $block->blocked) }}" class="user-avatar">
                <img src="{{ $block->blocked->avatar_url }}" alt="{{ $block->blocked->username }}">
            </a>
            <div class="user-info">
                <a href="{{ route('users.show', $block->blocked) }}">
                    <div class="user-name">{{ $block->blocked->username }}</div>
                </a>
                <div class="user-meta">
                    <span class="blocked-badge"><i class="fas fa-ban"></i> Blocked</span>
                    <span>Blocked on {{ $block->created_at->format('M d, Y') }}</span>
                </div>
            </div>
            <button class="btn btn-ghost" onclick="blockedPageUnblock(this, '{{ $block->blocked->username }}')">
                <i class="fas fa-unlock"></i> Unblock
            </button>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-shield-alt"></i>
            <h3>No blocked users</h3>
            <p style="color: var(--text-muted);">You haven't blocked anyone yet.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function blockedPageUnblock(btn, username) {
    if (!confirm('Unblock this user?')) return;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/users/${username}/block`, {
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
