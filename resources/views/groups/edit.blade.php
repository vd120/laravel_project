@extends('layouts.app')

@section('title', __('chat.edit_group') . ': ' . $group->name)

@section('content')
<div class="group-edit-page">
    <div class="edit-header">
        <a href="{{ route('groups.show', $group->slug) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> {{ __('chat.back_to_group') }}
        </a>
        <h1>{{ __('chat.edit_group') }}</h1>
    </div>

    <form action="{{ route('groups.update', $group) }}" method="POST" enctype="multipart/form-data" class="edit-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3>{{ __('chat.group_info') }}</h3>

            <div class="form-group">
                <label for="name">{{ __('chat.group_name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $group->name) }}" required>
            </div>

            <div class="form-group">
                <label for="description">{{ __('chat.description') }}</label>
                <textarea id="description" name="description" rows="4">{{ old('description', $group->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="avatar">{{ __('chat.group_avatar') }}</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                @if($group->avatar)
                    <p class="current-file">{{ __('chat.current') }}: <img src="{{ asset('storage/' . $group->avatar) }}" alt="Current avatar" class="current-avatar"></p>
                @endif
            </div>
        </div>

        <div class="form-section">
            <h3>{{ __('chat.group_members') }}</h3>
            <p class="section-desc">{{ __('chat.manage_members_desc') }}</p>

            <div class="members-list">
                @foreach($group->members as $member)
                    <div class="member-item">
                        <div class="member-info">
                            <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}" class="member-avatar">
                            <span class="member-name">{{ $member->user->name }}</span>
                            @if($member->is_admin)
                                <span class="admin-badge">{{ __('chat.admin') }}</span>
                            @endif
                        </div>
                        @if($member->user_id !== auth()->id())
                            <button type="button" class="remove-member-btn" onclick="removeMember({{ $group->id }}, {{ $member->user_id }})">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="add-members">
                <h4>{{ __('chat.add_members') }}</h4>
                <div class="search-users">
                    <input type="text" id="userSearch" placeholder="{{ __('chat.search_users') }}" oninput="searchUsers(this.value)">
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('chat.save_changes') }}</button>
            <a href="{{ route('groups.show', $group) }}" class="btn">{{ __('chat.cancel') }}</a>
        </div>
    </form>

    <div class="danger-zone">
        <h3>{{ __('chat.danger_zone') }}</h3>
        <p>{{ __('chat.danger_zone_desc') }}</p>
        <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('{{ __('chat.delete_group_confirm') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">{{ __('chat.delete_group') }}</button>
        </form>
    </div>
</div>

<style>
.group-edit-page {
    max-width: 700px;
    margin: 0 auto;
    padding: 24px;
}

.edit-header {
    margin-bottom: 24px;
}

.back-link {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.edit-header h1 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}

.edit-form {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 24px;
}

.form-section {
    margin-bottom: 32px;
}

.form-section:last-of-type {
    margin-bottom: 0;
}

.form-section h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.section-desc {
    color: var(--text-muted);
    font-size: 13px;
    margin: -8px 0 16px 0;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="file"],
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
}

.current-file {
    margin-top: 8px;
    font-size: 13px;
    color: var(--text-muted);
}

.current-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 8px;
}

.members-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 24px;
}

.member-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.member-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.member-avatar,
.member-avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.member-avatar-placeholder {
    background: var(--surface-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.member-name {
    font-weight: 500;
}

.admin-badge {
    background: var(--primary);
    color: white;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.remove-member-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.2s;
}

.remove-member-btn:hover {
    background: rgba(244, 63, 94, 0.1);
    color: var(--accent);
}

.add-members h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
}

.search-users {
    position: relative;
}

.search-users input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-top: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
}

.search-result-item {
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: var(--surface-hover);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
}

.btn:hover {
    background: var(--surface-hover);
}

.btn-primary {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
}

.btn-danger {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.danger-zone {
    background: rgba(244, 63, 94, 0.05);
    border: 1px solid rgba(244, 63, 94, 0.2);
    border-radius: var(--radius-lg);
    padding: 24px;
}

.danger-zone h3 {
    color: var(--accent);
    font-size: 16px;
    margin: 0 0 8px 0;
}

.danger-zone p {
    color: var(--text-muted);
    font-size: 13px;
    margin: 0 0 16px 0;
}

@media (max-width: 768px) {
    .group-edit-page {
        padding: 16px;
    }

    .edit-form {
        padding: 16px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>

<script>
let searchTimeout;

function searchUsers(query) {
    clearTimeout(searchTimeout);
    const results = document.getElementById('searchResults');
    
    if (query.length < 2) {
        results.innerHTML = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`/search?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.users && data.users.length > 0) {
                results.innerHTML = data.users.map(user => `
                    <div class="search-result-item" onclick="addMember({{ $group->id }}, ${user.id})">
                        <span>${user.name}</span>
                    </div>
                `).join('');
            } else {
                results.innerHTML = '<div class="search-result-item">No users found</div>';
            }
        })
        .catch(() => {
            results.innerHTML = '';
        });
    }, 300);
}

function addMember(groupId, userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/groups/${groupId}/members`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to add member');
        }
    })
    .catch(() => {
        alert('Error adding member');
    });
}

function removeMember(groupId, userId) {
    if (!confirm('Remove this member from the group?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/groups/${groupId}/members/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove member');
        }
    })
    .catch(() => {
        alert('Error removing member');
    });
}
</script>
@endsection