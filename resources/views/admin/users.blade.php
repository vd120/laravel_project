@extends('layouts.app')

@section('title', 'Manage Users - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>Users</h1>
                <p>Manage and moderate platform users</p>
            </div>
        </div>
        <div class="header-stats">
            <span class="total-badge">{{ $users->total() }} Total</span>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="filters-section">
        <div class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Search users..." autocomplete="off">
            </div>
            <select id="filter-select" class="filter-select">
                <option value="">All Users</option>
                <option value="admin" {{ request('admin_filter') === 'admin' ? 'selected' : '' }}>Admins Only</option>
                <option value="user" {{ request('admin_filter') === 'user' ? 'selected' : '' }}>Regular Users</option>
            </select>
            @if(request('search') || request('admin_filter'))
            <a href="{{ route('admin.users') }}" class="clear-btn">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </div>
    </div>

    {{-- Users Table --}}
    @if($users->count() > 0)
    <div class="users-table">
        <div class="table-header-row">
            <div class="col-user">User</div>
            <div class="col-email">Email</div>
            <div class="col-role">Role</div>
            <div class="col-status">Status</div>
            <div class="col-joined">Joined</div>
            <div class="col-actions">Actions</div>
        </div>

        @foreach($users as $user)
        <div class="table-row">
            <div class="col-user">
                <div class="user-cell">
                    <div class="user-avatar">
                        @if($user->profile && $user->profile->avatar)
                            <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="">
                        @else
                            <div class="avatar-initials">{{ substr($user->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ $user->name }}</span>
                        @if($user->profile && $user->profile->bio)
                        <span class="user-bio">{{ Str::limit($user->profile->bio, 25) }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-email">{{ $user->email }}</div>
            <div class="col-role">
                @if($user->is_admin)
                <span class="badge admin">Admin</span>
                @else
                <span class="badge user">User</span>
                @endif
            </div>
            <div class="col-status">
                @if($user->is_suspended)
                <span class="badge suspended">Suspended</span>
                @elseif($user->profile && $user->profile->is_private)
                <span class="badge private">Private</span>
                @else
                <span class="badge active">Active</span>
                @endif
            </div>
            <div class="col-joined">{{ $user->created_at->format('M j, Y') }}</div>
            <div class="col-actions">
                <div class="action-buttons">
                    <a href="{{ route('admin.users.show', $user) }}" class="action-btn view" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if($user->id !== auth()->id() && !$user->is_admin)
                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>No users found</h3>
        <p>No users match your search criteria.</p>
    </div>
    @endif
</div>

<style>
.admin-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 16px 40px;
}

.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: -16px -16px 24px;
    padding: 24px 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 0 0 20px 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.back-btn:hover {
    background: rgba(255,255,255,0.3);
}

.admin-header h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: white;
}

.admin-header p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
}

.total-badge {
    background: rgba(255,255,255,0.25);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.filters-section {
    margin-bottom: 24px;
}

.search-form {
    display: flex;
    gap: 12px;
    align-items: center;
}

.search-box {
    flex: 1;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-box input {
    width: 100%;
    padding: 14px 16px 14px 46px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background: var(--card-bg);
    color: var(--text);
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-select {
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 13px;
    background: var(--bg);
    color: var(--text);
    min-width: 130px;
    cursor: pointer;
}

.clear-btn {
    padding: 12px 16px;
    background: var(--bg);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.clear-btn:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.users-table {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
}

.table-header-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr;
    gap: 16px;
    padding: 16px 20px;
    background: var(--bg);
    border-bottom: 1px solid var(--border-color);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
    transition: background 0.2s ease;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: var(--hover-bg);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.user-avatar img {
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
    font-weight: 700;
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.user-bio {
    font-size: 12px;
    color: var(--text-muted);
}

.col-email {
    font-size: 13px;
    color: var(--text-muted);
}

.col-joined {
    font-size: 13px;
    color: var(--text-muted);
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge.admin { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.badge.user { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.badge.suspended { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.badge.private { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.badge.active { background: rgba(16, 185, 129, 0.15); color: #10b981; }

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.action-btn.view {
    background: rgba(59, 130, 246, 0.15);
    color: #3b82f6;
}

.action-btn.view:hover {
    background: #3b82f6;
    color: white;
}

.action-btn.edit {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.action-btn.edit:hover {
    background: #10b981;
    color: white;
}

.action-btn.delete {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.action-btn.delete:hover {
    background: #ef4444;
    color: white;
}

.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 32px;
    color: #3b82f6;
}

.empty-state h3 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
}

.empty-state p {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
}

@media (max-width: 900px) {
    .table-header-row {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        gap: 12px;
        padding: 20px;
    }
    
    .col-user { order: 1; }
    .col-email { order: 2; }
    .col-role { order: 3; }
    .col-status { order: 4; }
    .col-joined { order: 5; }
    .col-actions { order: 6; justify-content: flex-start; }
    
    .col-email::before { content: 'Email: '; color: var(--text-muted); font-size: 12px; }
    .col-role::before { content: 'Role: '; color: var(--text-muted); font-size: 12px; }
    .col-status::before { content: 'Status: '; color: var(--text-muted); font-size: 12px; }
    .col-joined::before { content: 'Joined: '; color: var(--text-muted); font-size: 12px; }
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .header-left {
        flex-direction: column;
    }

    .search-form {
        flex-direction: column;
    }

    .search-box input {
        width: 100%;
    }

    .filter-select, .clear-btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const filterSelect = document.getElementById('filter-select');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                if (query.length === 0) {
                    applyFilter();
                }
                return;
            }

            searchTimeout = setTimeout(function() {
                applyFilter();
            }, 500);
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            applyFilter();
        });
    }
});

function applyFilter() {
    const search = document.getElementById('search-input').value.trim();
    const filter = document.getElementById('filter-select').value;
    
    let url = '{{ route("admin.users") }}?';
    const params = [];
    
    if (search.length >= 2) {
        params.push('search=' + encodeURIComponent(search));
    }
    if (filter) {
        params.push('admin_filter=' + encodeURIComponent(filter));
    }
    
    window.location.href = url + params.join('&');
}
</script>
@endsection
