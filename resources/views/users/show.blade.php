@extends('layouts.app')

@section('title', $user->name . ' - Profile')

@section('content')
<style>
.profile-container { max-width: 900px; margin: 0 auto; }
.profile-header { position: relative; margin-bottom: 80px; }
.cover-image { 
    width: 100%; height: 240px; background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: var(--radius-lg); position: relative; overflow: hidden;
}
.cover-image img { width: 100%; height: 100%; object-fit: cover; }
.cover-placeholder { 
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 48px; color: rgba(255,255,255,0.3);
}
.profile-avatar-wrapper { 
    position: absolute; bottom: -60px; left: 40px; 
    padding: 5px; background: var(--bg); border-radius: 50%;
}
.profile-avatar { 
    width: 120px; height: 120px; border-radius: 50%; overflow: hidden;
    background: var(--surface); border: 4px solid var(--bg);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar .avatar-placeholder { 
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 48px; font-weight: 700; color: var(--primary);
}
.profile-info { padding: 0 40px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; }
.profile-details { flex: 1; min-width: 200px; }
.profile-name { font-size: 28px; font-weight: 800; color: var(--text); margin-bottom: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.profile-username { font-size: 16px; color: var(--text-muted); margin-bottom: 12px; }
.profile-bio { font-size: 15px; color: var(--text); line-height: 1.6; margin-bottom: 16px; }
.profile-meta { display: flex; gap: 20px; flex-wrap: wrap; color: var(--text-muted); font-size: 14px; }
.profile-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.profile-stats { 
    display: flex; gap: 32px; padding: 20px 40px; margin: 24px 0;
    border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
}
.stat-item { text-align: center; text-decoration: none; }
.stat-number { font-size: 24px; font-weight: 800; color: var(--text); }
.stat-label { font-size: 14px; color: var(--text-muted); }
.private-badge { 
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
    background: rgba(244, 63, 94, 0.1); color: var(--accent); font-size: 12px;
    border-radius: var(--radius-full); font-weight: 600;
}
.empty-state { text-align: center; padding: 60px 20px; }
@media (max-width: 640px) {
    .profile-avatar-wrapper { left: 50%; transform: translateX(-50%); }
    .profile-info { padding: 20px 0; text-align: center; justify-content: center; }
    .profile-meta { justify-content: center; }
    .profile-actions { width: 100%; justify-content: center; }
    .profile-stats { padding: 20px; justify-content: center; gap: 24px; }
    .cover-image { height: 180px; }
}
</style>

<div class="profile-container">
    <div class="profile-header">
        <div class="cover-image" @if($user->profile && $user->profile->cover_image) onclick="openImageModal('{{ asset('storage/' . $user->profile->cover_image) }}')" style="cursor: pointer;" @endif>
            @if($user->profile && $user->profile->cover_image)
                <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="Cover">
            @else
                <div class="cover-placeholder"><i class="fas fa-image"></i></div>
            @endif
        </div>
        <div class="profile-avatar-wrapper">
            <div class="profile-avatar" @if($user->profile && $user->profile->avatar) onclick="openImageModal('{{ asset('storage/' . $user->profile->avatar) }}')" style="cursor: pointer;" @endif>
                @if($user->profile && $user->profile->avatar)
                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="{{ $user->name }}">
                @else
                    <div class="avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-info">
        <div class="profile-details">
            <div class="profile-name">
                {{ $user->name }}
                @if(auth()->check() && auth()->user()->isBlocking($user))
                    <span class="private-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-ban"></i> Blocked</span>
                @endif
                @if($user->profile && $user->profile->is_private)
                    <span class="private-badge"><i class="fas fa-lock"></i> Private</span>
                @endif
                @if($user->is_admin)
                    <span class="private-badge" style="background: rgba(139, 92, 246, 0.1); color: var(--primary);"><i class="fas fa-shield-alt"></i> Admin</span>
                @endif
                @if($user->is_suspended)
                    <span class="private-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-ban"></i> Suspended</span>
                @endif
                @if($user->hasVerifiedEmail())
                    <span class="private-badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fas fa-check-circle"></i> Email Verified</span>
                @else
                    <span class="private-badge" style="background: rgba(255, 165, 0, 0.1); color: orange;"><i class="fas fa-exclamation-circle"></i> Email Unverified</span>
                @endif
            </div>
            <div class="profile-username">@ {{ $user->name }}</div>
            @if($user->profile && $user->profile->bio)
                <div class="profile-bio">{{ $user->profile->bio }}</div>
            @endif
            <div class="profile-meta">
                @if($user->profile && $user->profile->location)
                    <span><i class="fas fa-map-marker-alt"></i> {{ $user->profile->location }}</span>
                @endif
                <span><i class="fas fa-calendar"></i> Joined {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>

        <div class="profile-actions">
            @if(auth()->check() && auth()->id() === $user->id)
                <a href="{{ route('profile.edit', $user) }}" class="btn"><i class="fas fa-edit"></i> Edit Profile</a>
            @elseif(auth()->check() && $user->isBlocking(auth()->user()))
                <div style="color: var(--text-muted); font-size: 14px;">
                    <i class="fas fa-ban"></i> This user has blocked you
                </div>
            @elseif(auth()->check() && auth()->user()->isBlocking($user))
                <button class="btn" onclick="unblockUser('{{ $user->name }}')" style="background: #dc3545; color: white;">
                    <i class="fas fa-unlock"></i> <span>Unblock</span>
                </button>
            @elseif(auth()->check())
                @php
                    $isFollowing = auth()->user()->isFollowing($user);
                @endphp
                <button class="btn btn-primary" onclick="toggleFollow(this, '{{ $user->name }}')" data-following="{{ $isFollowing ? 'true' : 'false' }}">
                    <i class="fas fa-user-{{ $isFollowing ? 'check' : 'plus' }}"></i> <span>{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                </button>
                <a href="{{ route('chat.start', $user->id) }}" class="btn"><i class="fas fa-envelope"></i> Message</a>
                <button class="btn" onclick="blockUser('{{ $user->name }}')" style="background: #dc3545; color: white;">
                    <i class="fas fa-ban"></i> <span>Block</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Sign in to Follow</a>
            @endif
        </div>
    </div>

    <div class="profile-stats">
        <a href="{{ route('users.show', $user) }}" class="stat-item">
            <div class="stat-number">{{ $user->posts()->count() }}</div>
            <div class="stat-label">Posts</div>
        </a>
        <a href="{{ route('users.followers', $user) }}" class="stat-item">
            <div class="stat-number">{{ $user->followers()->count() }}</div>
            <div class="stat-label">Followers</div>
        </a>
        <a href="{{ route('users.following', $user) }}" class="stat-item">
            <div class="stat-number">{{ $user->follows()->count() }}</div>
            <div class="stat-label">Following</div>
        </a>
        @if(auth()->check() && auth()->id() === $user->id)
        <a href="{{ route('users.blocked', $user) }}" class="stat-item">
            <div class="stat-number">{{ $user->blockedUsers()->count() }}</div>
            <div class="stat-label">Blocked</div>
        </a>
        @endif
    </div>

    <div class="profile-content">
        @php
            $posts = $user->posts()->with(['media', 'comments', 'likes'])->latest()->paginate(10);
        @endphp
        @forelse($posts as $post)
            @include('partials.post', ['post' => $post])
        @empty
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>No posts yet</h3>
                <p style="color: var(--text-muted);">This user hasn't posted anything yet.</p>
            </div>
        @endforelse
        {{ $posts->links() }}
    </div>
</div>

<!-- Image Modal for Avatar/Cover -->
<div id="image-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;" onclick="closeImageModal(event)">
    <div style="position:relative;max-width:90vw;max-height:90vh;" onclick="event.stopPropagation()">
        <button style="position:absolute;top:-40px;right:0;background:none;border:none;color:white;font-size:28px;cursor:pointer;padding:8px;" onclick="closeImageModal()">×</button>
        <img id="image-modal-img" src="" alt="Image" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;">
    </div>
</div>

<script>
function openImageModal(src) {
    document.getElementById('image-modal-img').src = src;
    document.getElementById('image-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeImageModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('image-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function toggleFollow(btn, userName) {
    const isFollowing = btn.getAttribute('data-following') === 'true';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/users/${encodeURIComponent(userName)}/follow`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.following) {
            btn.innerHTML = '<i class="fas fa-user-check"></i> <span>Following</span>';
            btn.setAttribute('data-following', 'true');
        } else {
            btn.innerHTML = '<i class="fas fa-user-plus"></i> <span>Follow</span>';
            btn.setAttribute('data-following', 'false');
        }
    })
    .catch(() => alert('Error updating follow status'))
    .finally(() => btn.disabled = false);
}

function unblockUser(userName) {
    if (!confirm(`Unblock ${userName}?`)) return;
    
    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error unblocking user');
        }
    })
    .catch(() => alert('Error unblocking user'));
}

function blockUser(userName) {
    if (!confirm(`Block ${userName}?`)) return;
    
    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error blocking user');
        }
    })
    .catch(() => alert('Error blocking user'));
}
</script>
@endsection
