@extends('layouts.app')

@section('title', 'Admin Dashboard - Laravel Social')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p class="page-subtitle">Complete system overview and management</p>
    </div>

    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_posts']) }}</div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_comments']) }}</div>
                <div class="stat-label">Total Comments</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-camera"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_stories']) }}</div>
                <div class="stat-label">Total Stories</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_follows']) }}</div>
                <div class="stat-label">Total Follows</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_blocks']) }}</div>
                <div class="stat-label">Total Blocks</div>
            </div>
        </div>

        <div class="stat-card admin-highlight">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['admin_users']) }}</div>
                <div class="stat-label">Admin Users</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['private_profiles']) }}</div>
                <div class="stat-label">Private Profiles</div>
            </div>
        </div>
    </div>

    
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="{{ route('admin.users') }}" class="action-card">
                <i class="fas fa-users-cog"></i>
                <span>Manage Users</span>
            </a>
            <a href="{{ route('admin.posts') }}" class="action-card">
                <i class="fas fa-images"></i>
                <span>Manage Posts</span>
            </a>
            <a href="{{ route('admin.comments') }}" class="action-card">
                <i class="fas fa-comments"></i>
                <span>Manage Comments</span>
            </a>
            <a href="{{ route('admin.stories') }}" class="action-card">
                <i class="fas fa-camera"></i>
                <span>Manage Stories</span>
            </a>
            <a href="{{ route('admin.system-info') }}" class="action-card">
                <i class="fas fa-server"></i>
                <span>System Info</span>
            </a>
            <a href="#" onclick="showCreateAdminModal()" class="action-card">
                <i class="fas fa-user-plus"></i>
                <span>Create Admin</span>
            </a>
        </div>
    </div>

    
    <div class="recent-activity">
        <div class="activity-section">
            <h2>Recent Users</h2>
            <div class="activity-list">
                @forelse($stats['recent_users'] as $user)
                <div class="activity-item">
                    <div class="activity-avatar">
                        @if($user->profile && $user->profile->avatar)
                            <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder"><i class="fas fa-user"></i></div>
                        @endif
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ $user->name }}</div>
                        <div class="activity-meta">{{ $user->created_at->diffForHumans() }}</div>
                    </div>
                    <a href="{{ route('admin.users.show', $user) }}" class="activity-action">View</a>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <span>No recent users</span>
                </div>
                @endforelse
            </div>
        </div>

        <div class="activity-section">
            <h2>Recent Posts</h2>
            <div class="activity-list">
                @forelse($stats['recent_posts'] as $post)
                <div class="activity-item">
                    <div class="activity-avatar">
                        @if($post->user->profile && $post->user->profile->avatar)
                            <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder"><i class="fas fa-user"></i></div>
                        @endif
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ Str::limit($post->content ?? 'Media post', 50) }}</div>
                        <div class="activity-meta">by {{ $post->user->name }} • {{ $post->created_at->diffForHumans() }}</div>
                    </div>
                    <a href="{{ route('admin.posts') }}" class="activity-action">View</a>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <span>No recent posts</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>


<div id="create-admin-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create Admin Account</h3>
            <button type="button" class="modal-close" onclick="hideCreateAdminModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.create-admin') }}">
            @csrf
            <div class="form-group">
                <label for="admin-username">Username</label>
                <input type="text" id="admin-username" name="username" required minlength="3" maxlength="50">
            </div>
            <div class="form-group">
                <label for="admin-email">Email</label>
                <input type="email" id="admin-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="admin-password">Password</label>
                <input type="password" id="admin-password" name="password" required minlength="8">
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="hideCreateAdminModal()">Cancel</button>
                <button type="submit" class="btn-primary">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
    text-align: center;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: var(--twitter-dark);
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 16px;
    color: var(--twitter-gray);
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-card.admin-highlight {
    border-color: var(--error-color);
    background: linear-gradient(135deg, rgba(244, 33, 46, 0.1), var(--card-bg));
    box-shadow: 0 4px 16px rgba(244, 33, 46, 0.2);
}

.stat-card.admin-highlight .stat-icon {
    background: var(--error-color);
    color: white;
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.3);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    color: var(--twitter-gray);
    font-weight: 500;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 40px;
}

.quick-actions h2 {
    font-size: 24px;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 20px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.action-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: var(--twitter-dark);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    border-color: var(--twitter-blue);
    background: var(--hover-bg);
}

.action-card i {
    font-size: 24px;
    color: var(--twitter-blue);
}

.action-card span {
    font-size: 14px;
    font-weight: 500;
}

/* Recent Activity */
.recent-activity {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.activity-section h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 16px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.activity-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateX(2px);
}

.activity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.activity-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 16px;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 2px;
}

.activity-meta {
    font-size: 12px;
    color: var(--twitter-gray);
}

.activity-action {
    background: var(--twitter-blue);
    color: white;
    padding: 6px 12px;
    border-radius: 16px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.activity-action:hover {
    background: #1991DB;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 32px;
    margin-bottom: 12px;
    display: block;
    opacity: 0.5;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(2px);
}

.modal-content {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.modal-header {
    padding: 24px 24px 0 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 24px;
    padding-bottom: 16px;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: var(--twitter-light);
    color: var(--twitter-dark);
}

.modal-content form {
    padding: 0 24px 24px 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--twitter-dark);
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    font-family: inherit;
    background: var(--input-bg);
    color: var(--twitter-dark);
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn-secondary {
    background: white;
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--twitter-light);
    border-color: #AAB8C2;
}

.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #1991DB;
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-dashboard {
        padding: 16px;
    }

    .page-header h1 {
        font-size: 24px;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }

    .stat-number {
        font-size: 24px;
    }

    .actions-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .recent-activity {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .modal-content {
        width: 95%;
        margin: 20px;
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

// Close modal when clicking outside
document.getElementById('create-admin-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCreateAdminModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('create-admin-modal').style.display === 'flex') {
        hideCreateAdminModal();
    }
});
</script>
@endsection
