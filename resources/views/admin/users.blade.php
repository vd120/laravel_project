@extends('layouts.app')

@section('title', 'Manage Users - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>Manage Users</h1>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by username or email..." class="search-input">
            </div>
            <select name="admin_filter" class="filter-select">
                <option value="">All Users</option>
                <option value="admin" {{ request('admin_filter') === 'admin' ? 'selected' : '' }}>Admins Only</option>
                <option value="user" {{ request('admin_filter') === 'user' ? 'selected' : '' }}>Regular Users</option>
            </select>
            <button type="submit" class="btn-primary">Filter</button>
            @if(request('search') || request('admin_filter'))
            <a href="{{ route('admin.users') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="data-table-container">
        <div class="table-header">
            <h2>Users ({{ $users->total() }})</h2>
        </div>

        @if($users->count() > 0)
        <div class="responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Suspension</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="user-cell">
                                @if($user->profile && $user->profile->avatar)
                                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" class="user-avatar-small">
                                @else
                                    <div class="user-avatar-placeholder-small">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div class="user-info">
                                    <span class="username">{{ $user->name }}</span>
                                    @if($user->profile && $user->profile->bio)
                                        <span class="user-bio">{{ Str::limit($user->profile->bio, 30) }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge admin-badge">Admin</span>
                            @else
                                <span class="badge user-badge">User</span>
                            @endif
                        </td>
                        <td>
                            @if($user->profile && $user->profile->is_private)
                                <span class="badge private-badge">Private</span>
                            @else
                                <span class="badge public-badge">Public</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_suspended)
                                <span class="badge suspended-badge">Suspended</span>
                            @else
                                <span class="badge active-badge">Active</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-action view-btn" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-action edit-btn" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id() && !$user->is_admin)
                                <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline-form" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete-btn" title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No users found</h3>
            <p>No users match your current filters.</p>
        </div>
        @endif
    </div>
</div>

<style>
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.header-actions {
    display: flex;
    gap: 12px;
}

/* Filters Section */
.filters-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-form {
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.search-group {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--twitter-gray);
}

.search-input {
    width: 100%;
    padding: 10px 16px 10px 40px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
}

.filter-select {
    padding: 10px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    min-width: 150px;
}

/* Data Table */
.data-table-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--twitter-light);
}

.table-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.responsive-table {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: var(--twitter-light);
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: var(--twitter-dark);
    border-bottom: 1px solid var(--border-color);
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.user-avatar-placeholder-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
}

.username {
    font-weight: 600;
    color: var(--twitter-dark);
    display: block;
}

.user-bio {
    font-size: 12px;
    color: var(--twitter-gray);
    margin-top: 2px;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.admin-badge {
    background: #dc3545;
    color: white;
}

.user-badge {
    background: var(--twitter-blue);
    color: white;
}

.private-badge {
    background: #6c757d;
    color: white;
}

.public-badge {
    background: #28a745;
    color: white;
}

.suspended-badge {
    background: #ffc107;
    color: #212529;
}

.active-badge {
    background: #17a2b8;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 14px;
}

.view-btn {
    background: var(--twitter-blue);
    color: white;
}

.view-btn:hover {
    background: #1991DB;
}

.edit-btn {
    background: #28a745;
    color: white;
}

.edit-btn:hover {
    background: #218838;
}

.delete-btn {
    background: #dc3545;
    color: white;
}

.delete-btn:hover {
    background: #c82333;
}

.inline-form {
    display: inline;
}

/* Pagination */
.pagination-container {
    padding: 20px;
    background: var(--twitter-light);
    border-top: 1px solid var(--border-color);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
}

.empty-state p {
    margin: 0;
}

/* Button Styles */
.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #1991DB;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--card-bg);
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--hover-bg);
    border-color: var(--twitter-blue);
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-page {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-group {
        min-width: auto;
    }

    .data-table {
        font-size: 14px;
    }

    .data-table th,
    .data-table td {
        padding: 12px 8px;
    }

    .user-cell {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 4px;
    }

    .btn-action {
        padding: 6px;
    }
}
</style>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this user? This action cannot be undone.');
}
</script>
@endsection
