@extends('layouts.app')

@section('title', 'Admin Dashboard - Nexus')

@section('content')
<div class="admin-dashboard">
    {{-- Header --}}
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Dashboard</h1>
            </div>
            <p class="admin-subtitle">Monitor and manage your platform</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-section">
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon-wrap users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_users']) }}</span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon-wrap posts">
                    <i class="fas fa-pen-square"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_posts']) }}</span>
                    <span class="stat-label">Total Posts</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon-wrap comments">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_comments']) }}</span>
                    <span class="stat-label">Comments</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon-wrap stories">
                    <i class="fas fa-circle-notch"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_stories']) }}</span>
                    <span class="stat-label">Stories</span>
                </div>
            </div>
        </div>

        <div class="stats-row secondary">
            <div class="stat-box small">
                <div class="stat-icon-wrap follows">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_follows']) }}</span>
                    <span class="stat-label">Follows</span>
                </div>
            </div>

            <div class="stat-box small">
                <div class="stat-icon-wrap blocks">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['total_blocks']) }}</span>
                    <span class="stat-label">Blocks</span>
                </div>
            </div>

            <div class="stat-box small">
                <div class="stat-icon-wrap admin">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['admin_users']) }}</span>
                    <span class="stat-label">Admins</span>
                </div>
            </div>

            <div class="stat-box small">
                <div class="stat-icon-wrap private">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($stats['private_profiles']) }}</span>
                    <span class="stat-label">Private</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="section">
        <h2 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h2>
        <div class="actions-row">
            <a href="{{ route('admin.users') }}" class="action-btn">
                <div class="action-icon"><i class="fas fa-users-cog"></i></div>
                <span>Users</span>
            </a>
            <a href="{{ route('admin.posts') }}" class="action-btn">
                <div class="action-icon"><i class="fas fa-images"></i></div>
                <span>Posts</span>
            </a>
            <a href="{{ route('admin.comments') }}" class="action-btn">
                <div class="action-icon"><i class="fas fa-comments"></i></div>
                <span>Comments</span>
            </a>
            <a href="{{ route('admin.stories') }}" class="action-btn">
                <div class="action-icon"><i class="fas fa-camera"></i></div>
                <span>Stories</span>
            </a>
            <a href="#" onclick="showCreateAdminModal()" class="action-btn highlight">
                <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                <span>New Admin</span>
            </a>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="section">
        <h2 class="section-title">
            <i class="fas fa-clock"></i>
            Recent Activity
        </h2>
        <div class="activity-grid">
            <div class="activity-card">
                <div class="activity-header">
                    <h3>New Users</h3>
                    <span class="badge">{{ $stats['recent_users']->count() }}</span>
                </div>
                <div class="activity-list">
                    @forelse($stats['recent_users']->take(5) as $user)
                    <div class="activity-item">
                        <div class="activity-avatar">
                            @if($user->profile && $user->profile->avatar)
                                <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="">
                            @else
                                <div class="avatar-initials">{{ substr($user->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="activity-details">
                            <span class="activity-name">{{ $user->name }}</span>
                            <span class="activity-time">{{ $user->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('admin.users.show', $user) }}" class="activity-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    @empty
                    <div class="empty-activity">No users yet</div>
                    @endforelse
                </div>
            </div>

            <div class="activity-card">
                <div class="activity-header">
                    <h3>Latest Posts</h3>
                    <span class="badge">{{ $stats['recent_posts']->count() }}</span>
                </div>
                <div class="activity-list">
                    @forelse($stats['recent_posts']->take(5) as $post)
                    <div class="activity-item">
                        <div class="activity-avatar">
                            @if($post->user->profile && $post->user->profile->avatar)
                                <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="">
                            @else
                                <div class="avatar-initials">{{ substr($post->user->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="activity-details">
                            <span class="activity-name">{{ $post->user->name }}</span>
                            <span class="activity-time">{{ Str::limit($post->content ?? 'Media post', 30) }}</span>
                        </div>
                        <a href="{{ route('admin.posts') }}" class="activity-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    @empty
                    <div class="empty-activity">No posts yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Admin Modal --}}
<div id="create-admin-modal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-top">
            <h3><i class="fas fa-user-shield"></i> Create Admin</h3>
            <button class="modal-close-btn" onclick="hideCreateAdminModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.create-admin') }}">
            @csrf
            <div class="form-row">
                <label>Username</label>
                <input type="text" name="username" required minlength="3" maxlength="50" autocomplete="username" placeholder="Enter username">
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Enter email address" autocomplete="email">
            </div>
            <div class="form-row">
                <label>Password</label>
                <input type="password" name="password" required minlength="8" autocomplete="current-password" placeholder="Min 8 characters">
            </div>
            <div class="form-buttons">
                <button type="button" class="btn-cancel" onclick="hideCreateAdminModal()">Cancel</button>
                <button type="submit" class="btn-submit">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 16px 40px;
}

.admin-header {
    background: linear-gradient(135deg, #1d9bf0 0%, #8b5cf6 100%);
    margin: -16px -16px 30px;
    padding: 40px 20px;
    text-align: center;
    border-radius: 0 0 24px 24px;
}

.admin-header-content {
    max-width: 600px;
    margin: 0 auto;
}

.admin-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;
}

.admin-title i {
    font-size: 28px;
    color: white;
}

.admin-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: white;
}

.admin-subtitle {
    margin: 0;
    color: rgba(255,255,255,0.85);
    font-size: 15px;
}

.stats-section {
    margin-bottom: 32px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 12px;
}

.stats-row.secondary {
    grid-template-columns: repeat(4, 1fr);
}

.stat-box {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-box.small {
    padding: 16px;
    gap: 12px;
}

.stat-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-icon-wrap.users { background: rgba(29, 161, 242, 0.15); color: #1d9bf0; }
.stat-icon-wrap.posts { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.stat-icon-wrap.comments { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.stat-icon-wrap.stories { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.stat-icon-wrap.follows { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
.stat-icon-wrap.blocks { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.stat-icon-wrap.admin { background: rgba(244, 63, 94, 0.15); color: #f43f5e; }
.stat-icon-wrap.private { background: rgba(107, 114, 128, 0.15); color: #6b7280; }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}

.section {
    margin-bottom: 32px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 16px;
}

.section-title i {
    color: var(--primary);
}

.actions-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-btn {
    flex: 1;
    min-width: 120px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px 16px;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
}

.action-btn:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.action-btn.highlight {
    background: linear-gradient(135deg, rgba(29, 161, 242, 0.1), rgba(139, 92, 246, 0.1));
    border-color: var(--primary);
}

.action-icon {
    width: 44px;
    height: 44px;
    margin: 0 auto 10px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.action-btn.highlight .action-icon {
    background: linear-gradient(135deg, #1d9bf0, #8b5cf6);
}

.action-btn span {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.activity-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.activity-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.activity-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg);
}

.activity-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}

.badge {
    background: var(--primary);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
}

.activity-list {
    padding: 8px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    transition: background 0.2s ease;
}

.activity-item:hover {
    background: var(--hover-bg);
}

.activity-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
}

.activity-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.activity-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.activity-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.activity-time {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-link {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    border-radius: 50%;
    transition: all 0.2s ease;
}

.activity-link:hover {
    background: var(--primary);
    color: white;
}

.empty-activity {
    padding: 24px;
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-box {
    background: var(--card-bg);
    border-radius: 16px;
    width: 100%;
    max-width: 400px;
    overflow: hidden;
}

.modal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
}

.modal-top h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-top h3 i {
    color: var(--primary);
}

.modal-close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: var(--text-muted);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close-btn:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.modal-box form {
    padding: 24px;
}

.form-row {
    margin-bottom: 20px;
}

.form-row label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-row input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 14px;
    background: var(--input-bg);
    color: var(--text);
    transition: all 0.2s ease;
}

.form-row input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.form-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-cancel, .btn-submit {
    flex: 1;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
}

.btn-cancel:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.btn-submit {
    background: var(--primary);
    border: none;
    color: white;
}

.btn-submit:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .admin-header {
        margin: -16px -16px 24px;
        padding: 30px 16px;
        border-radius: 0 0 20px 20px;
    }

    .admin-title h1 {
        font-size: 22px;
    }

    .stats-row, .stats-row.secondary {
        grid-template-columns: repeat(2, 1fr);
    }

    .stat-box {
        padding: 16px;
    }

    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .stat-value {
        font-size: 18px;
    }

    .actions-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }

    .action-btn {
        min-width: auto;
        padding: 16px 12px;
    }

    .action-icon {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }

    .action-btn span {
        font-size: 11px;
    }

    .activity-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .stats-row, .stats-row.secondary {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .actions-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function showCreateAdminModal() {
    document.getElementById('create-admin-modal').style.display = 'flex';
}

function hideCreateAdminModal() {
    document.getElementById('create-admin-modal').style.display = 'none';
}

document.getElementById('create-admin-modal').addEventListener('click', function(e) {
    if (e.target === this) hideCreateAdminModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') hideCreateAdminModal();
});
</script>
@endsection
