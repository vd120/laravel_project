@extends('layouts.app')

@section('title', $user->username . ' - ' . __('users.profile'))

@section('content')
<style>
.profile-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
.profile-header { position: relative; margin-bottom: 90px; }
.cover-image {
    width: 100%; height: 260px; background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: var(--radius-lg); position: relative; overflow: hidden;
}
.cover-image img { width: 100%; height: 100%; object-fit: cover; }
.cover-placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 48px; color: rgba(255,255,255,0.3);
}
.profile-avatar-wrapper {
    position: absolute; bottom: -65px; left: 40px;
    padding: 6px; background: var(--bg); border-radius: 50%;
}
.profile-avatar {
    width: 130px; height: 130px; border-radius: 50%; overflow: hidden;
    background: var(--surface); border: 4px solid var(--bg);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar .avatar-placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 48px; font-weight: 700; color: var(--primary);
}
.profile-info { padding: 0 40px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; }
.profile-details { flex: 1; min-width: 200px; }
.profile-header-info { margin-bottom: 12px; }
.profile-name { font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 2px; display: block; }
.profile-username { font-size: 15px; color: var(--text-muted); margin-bottom: 10px; display: block; }
.profile-username span { direction: ltr; }
.profile-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.profile-badges .private-badge { margin: 0; }
.profile-bio { font-size: 15px; color: var(--text); line-height: 1.6; margin-bottom: 16px; }
.profile-meta { display: flex; gap: 20px; flex-wrap: wrap; color: var(--text-muted); font-size: 14px; }
.profile-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.profile-stats {
    display: flex; gap: 40px; padding: 24px 40px; margin: 28px 0;
    border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
}
.stat-item { text-align: center; text-decoration: none; }
.stat-number { font-size: 26px; font-weight: 800; color: var(--text); }
.stat-label { font-size: 14px; color: var(--text-muted); }
.private-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
    background: rgba(244, 63, 94, 0.1); color: var(--accent); font-size: 12px;
    border-radius: var(--radius-full); font-weight: 600; white-space: nowrap;
}
.empty-state { text-align: center; padding: 60px 20px; }

/* Mobile Responsive */
@media (max-width: 640px) {
    .profile-container { padding: 0 8px; }
    .profile-avatar-wrapper { left: 50%; transform: translateX(-50%); bottom: -50px; }
    .profile-info { padding: 1px 0 0; text-align: center; justify-content: center; }
    .profile-meta { justify-content: center; }
    .profile-actions { width: 100%; justify-content: center; }
    .profile-stats { padding: 16px; justify-content: center; gap: 24px; }
    .cover-image { height: 180px; }
    .profile-name { font-size: 20px; text-align: left; }
    .profile-username { font-size: 14px; }
    .profile-bio { font-size: 14px; text-align: center; }
    .profile-actions .btn { width: 100%; max-width: 200px; justify-content: center; }
}
</style>

<div class="profile-container">
    <div class="profile-header">
        <div class="cover-image" @if($user->profile && $user->profile->cover_image) onclick="openImageModal('{{ asset('storage/' . $user->profile->cover_image) }}')" style="cursor: pointer;" @endif>
            @if($user->profile && $user->profile->cover_image)
                <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="Cover" loading="lazy">
            @else
                <div class="cover-placeholder"><i class="fas fa-image"></i></div>
            @endif
        </div>
        <div class="profile-avatar-wrapper">
            <div class="profile-avatar" @if($user->avatar_url) onclick="openImageModal('{{ $user->avatar_url }}')" style="cursor: pointer;" @endif>
                <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}" loading="lazy">
            </div>
        </div>
    </div>

    <div class="profile-info">
        <div class="profile-details">
            <div class="profile-header-info">
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-username"><span dir="ltr">@ {{ $user->username }}</span></div>
                <div class="profile-badges">
                    @if(auth()->check() && $isBlocking)
                        <span class="private-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-ban"></i> {{ __('users.blocked') }}</span>
                    @endif
                    @if($user->profile && $user->profile->is_private)
                        <span class="private-badge"><i class="fas fa-lock"></i> {{ __('users.private') }}</span>
                    @endif
                    @if($user->is_admin)
                        <span class="private-badge" style="background: rgba(139, 92, 246, 0.1); color: var(--primary);"><i class="fas fa-shield-alt"></i> {{ __('users.admin') }}</span>
                    @endif
                    @if($user->is_suspended)
                        <span class="private-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-ban"></i> {{ __('users.suspended') }}</span>
                    @endif
                    @if($user->hasVerifiedEmail())
                        <span class="private-badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fas fa-check-circle"></i> {{ __('users.email_verified') }}</span>
                    @else
                        <span class="private-badge" style="background: rgba(255, 165, 0, 0.1); color: orange;"><i class="fas fa-exclamation-circle"></i> {{ __('users.email_unverified') }}</span>
                    @endif
                </div>
            </div>
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
                <a href="{{ route('profile.edit', $user) }}" class="btn"><i class="fas fa-edit"></i> {{ __('users.edit_profile') }}</a>
                <a href="{{ route('activity.index') }}" class="btn"><i class="fas fa-history"></i> {{ __('activity.activity_logs') }}</a>
                <button class="btn" onclick="showQrCodeModal()"><i class="fas fa-qrcode"></i> {{ __('users.qr_code') }}</button>
            @elseif(auth()->check() && $isBlockedBy)
                <div style="color: var(--text-muted); font-size: 14px;">
                    <i class="fas fa-ban"></i> {{ __('users.blocked_you') }}
                </div>
            @elseif(auth()->check() && $isBlocking)
                <button class="btn" onclick="profileUnblockUser('{{ $user->username }}')" style="background: #dc3545; color: white;">
                    <i class="fas fa-unlock"></i> <span>{{ __('users.unblock') }}</span>
                </button>
            @elseif(auth()->check())
                <button class="btn btn-primary" onclick="profileToggleFollow(this, '{{ $user->username }}')" data-following="{{ $isFollowing ? 'true' : 'false' }}">
                    <i class="fas fa-user-{{ $isFollowing ? 'check' : 'plus' }}"></i> <span>{{ $isFollowing ? __('users.following') : __('users.follow') }}</span>
                </button>
                <a href="{{ route('chat.start', $user->id) }}" class="btn"><i class="fas fa-envelope"></i> {{ __('users.message') }}</a>
                <button class="btn" onclick="profileBlockUser('{{ $user->username }}')" style="background: #dc3545; color: white;">
                    <i class="fas fa-ban"></i> <span>{{ __('users.block') }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> {{ __('users.sign_in_to_follow') }}</a>
            @endif
        </div>
    </div>

    <div class="profile-stats">
        <a href="{{ route('users.show', $user) }}" class="stat-item">
            <div class="stat-number">{{ $postsCount }}</div>
            <div class="stat-label">{{ __('users.posts') }}</div>
        </a>
        <a href="{{ route('users.followers', $user) }}" class="stat-item">
            <div class="stat-number">{{ $followersCount }}</div>
            <div class="stat-label">{{ __('users.followers') }}</div>
        </a>
        <a href="{{ route('users.following', $user) }}" class="stat-item">
            <div class="stat-number">{{ $followingCount }}</div>
            <div class="stat-label">{{ __('users.following') }}</div>
        </a>
        @if(auth()->check() && auth()->id() === $user->id)
        <a href="{{ route('users.blocked', $user) }}" class="stat-item">
            <div class="stat-number">{{ $blockedCount }}</div>
            <div class="stat-label">{{ __('users.blocked') }}</div>
        </a>
        @endif
    </div>

    <div class="profile-content">
        @forelse($posts as $post)
            @include('partials.post', ['post' => $post])
        @empty
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>{{ __('users.no_posts_yet') }}</h3>
                <p style="color: var(--text-muted);">{{ __('users.no_posts_yet_desc') }}</p>
            </div>
        @endforelse
        {{ $posts->links() }}
    </div>
</div>

<!-- QR Code Modal -->
<div id="qr-code-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9998;align-items:center;justify-content:center;" onclick="closeQrCodeModal(event)">
    <div style="background:var(--surface);border-radius:var(--radius-lg);padding:32px;max-width:400px;width:90%;text-align:center;position:relative;" onclick="event.stopPropagation()">
        <button style="position:absolute;top:12px;right:12px;background:none;border:none;color:var(--text-muted);font-size:24px;cursor:pointer;padding:4px;" onclick="closeQrCodeModal()">×</button>
        <h3 style="font-size:20px;font-weight:700;color:var(--text);margin-bottom:8px;"><i class="fas fa-qrcode" style="color:var(--primary);"></i> {{ __('users.profile_qr_code') }}</h3>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:24px;">{{ __('users.scan_to_visit_profile') }}</p>
        
        <div id="qr-code-loading" style="display:flex;align-items:center;justify-content:center;padding:40px;">
            <i class="fas fa-spinner fa-spin" style="font-size:32px;color:var(--primary);"></i>
        </div>
        
        <div id="qr-code-content" style="display:none;">
            <div id="qr-code-display" style="background:white;padding:16px;border-radius:var(--radius-md);display:inline-block;margin-bottom:20px;"></div>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;word-break:break-all;" id="qr-profile-url"></p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button class="btn btn-primary" onclick="downloadQrCode()"><i class="fas fa-download"></i> {{ __('users.download_qr') }}</button>
                <button class="btn" onclick="closeQrCodeModal()"><i class="fas fa-times"></i> {{ __('users.close') }}</button>
            </div>
        </div>
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
const unblockConfirmText = {!! json_encode(__('users.unblock_user_confirm')) !!};
const blockConfirmText = {!! json_encode(__('users.block_user_confirm')) !!};
const errorUnblockingText = {!! json_encode(__('users.error_unblocking')) !!};
const errorBlockingText = {!! json_encode(__('users.error_blocking')) !!};
const followingText = {!! json_encode(__('users.following')) !!};
const followText = {!! json_encode(__('users.follow')) !!};

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

function profileToggleFollow(btn, userName) {
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
        // Force reload immediately
        window.location.href = window.location.href;
    })
    .catch(() => {
        btn.innerHTML = '<i class="fas fa-user-' + (isFollowing ? 'check' : 'plus') + '"></i> <span>' + (isFollowing ? followingText : followText) + '</span>';
        btn.disabled = false;
    });
}

function profileUnblockUser(userName) {
    if (!confirm(unblockConfirmText.replace(':username', userName))) return;

    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        // Force reload immediately
        window.location.href = window.location.href;
    })
    .catch(() => {
        alert(errorUnblockingText);
    });
}

function profileBlockUser(userName) {
    if (!confirm(blockConfirmText.replace(':username', userName))) return;

    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        // Force reload immediately
        window.location.href = window.location.href;
    })
    .catch(() => {
        alert(errorBlockingText);
    });
}

// Show success message toast if exists
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    showToast({!! json_encode(session('success')) !!}, 'success');
});
@endif

// QR Code Modal Functions
const loadingQRCodeText = {!! json_encode(__('users.loading_qr_code')) !!};
const errorLoadingQRText = {!! json_encode(__('users.error_loading_qr_code')) !!};
const downloadQRText = {!! json_encode(__('users.download_qr')) !!};

function showQrCodeModal() {
    const modal = document.getElementById('qr-code-modal');
    const loading = document.getElementById('qr-code-loading');
    const content = document.getElementById('qr-code-content');
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Show loading state
    loading.style.display = 'flex';
    content.style.display = 'none';
    
    // Fetch QR code
    fetch(`{{ route('users.qr-code', $user) }}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('qr-code-display').innerHTML = data.qr_code;
            document.getElementById('qr-profile-url').textContent = data.profile_url;
            
            loading.style.display = 'none';
            content.style.display = 'block';
        } else {
            throw new Error('Failed to load QR code');
        }
    })
    .catch(() => {
        loading.innerHTML = '<i class="fas fa-exclamation-circle" style="font-size:32px;color:#ef4444;"></i><p style="color:var(--text-muted);margin-top:12px;">' + errorLoadingQRText + '</p>';
    });
}

function downloadQrCode() {
    const downloadUrl = `{{ route('users.qr-code.download', $user) }}`;
    
    // Create temporary link and trigger download
    fetch(downloadUrl)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'profile-qr-{{ $user->username }}.svg';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        })
        .catch(() => {
            // Fallback: open in new tab
            window.open(downloadUrl, '_blank');
        });
}

function closeQrCodeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('qr-code-modal');
    const loading = document.getElementById('qr-code-loading');
    
    modal.style.display = 'none';
    document.body.style.overflow = '';
    
    // Reset loading state for next open
    loading.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:32px;color:var(--primary);"></i>';
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const qrModal = document.getElementById('qr-code-modal');
        const imgModal = document.getElementById('image-modal');
        
        if (qrModal.style.display === 'flex') {
            closeQrCodeModal();
        }
        if (imgModal.style.display === 'flex') {
            closeImageModal();
        }
    }
});
</script>
@endsection
