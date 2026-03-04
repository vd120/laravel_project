@extends('layouts.app')

@section('title', 'Create Group')

@section('content')
<style>
/* Use chat theme variables */
:root {
    --wa-bg: var(--bg, #111b21);
    --wa-panel: var(--surface, #202c33);
    --wa-panel-hover: var(--surface-hover, #2a3942);
    --wa-border: var(--border, #2f3b43);
    --wa-text: var(--text, #e9edef);
    --wa-text-muted: var(--text-muted, #8696a0);
    --wa-accent: var(--primary, #00a884);
    --wa-blue: var(--primary, #53bdeb);
    --wa-green: var(--success, #25d366);
    --wa-red: var(--danger, #f15c6d);
}

.create-group-page {
    min-height: calc(100vh - 64px);
    background: var(--wa-bg);
    padding-top: 20px;
}

.create-group-container {
    max-width: 600px;
    margin: 20px auto;
    background: var(--wa-panel);
    min-height: calc(100vh - 84px);
    border-radius: 16px;
}

.create-group-header {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
}

.back-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--wa-text-muted);
    text-decoration: none;
    border-radius: 50%;
    margin-right: 16px;
    transition: background 0.2s;
}

.back-link:hover {
    background: var(--wa-panel-hover);
}

.create-group-header h1 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--wa-text);
}

.create-group-form {
    padding: 24px 16px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--wa-text);
    margin-bottom: 8px;
}

.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: var(--wa-bg);
    border: 1px solid var(--wa-border);
    border-radius: 8px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.form-group input[type="text"]:focus,
.form-group textarea:focus {
    border-color: var(--wa-accent);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.avatar-upload {
    text-align: center;
    margin-bottom: 32px;
}

.avatar-label {
    cursor: pointer;
}

.avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--wa-bg);
    border: 2px dashed var(--wa-border);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.2s;
}

.avatar-preview:hover {
    border-color: var(--wa-accent);
    background: var(--wa-panel-hover);
}

.avatar-preview i {
    font-size: 32px;
    color: var(--wa-text-muted);
    margin-bottom: 8px;
}

.avatar-preview span {
    font-size: 12px;
    color: var(--wa-text-muted);
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.checkbox-group {
    background: var(--wa-bg);
    padding: 16px;
    border-radius: 8px;
    border: 1px solid var(--wa-border);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-label span {
    font-size: 14px;
    color: var(--wa-text);
}

.help-text {
    font-size: 12px;
    color: var(--wa-text-muted);
    margin-top: 4px;
}

.members-selection {
    background: var(--wa-bg);
    padding: 16px;
    border-radius: 8px;
    border: 1px solid var(--wa-border);
}

.members-selection label:first-child {
    font-size: 16px;
    font-weight: 600;
}

.friends-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
    max-height: 400px;
    overflow-y: auto;
}

.friends-list::-webkit-scrollbar {
    width: 6px;
}

.friends-list::-webkit-scrollbar-thumb {
    background: var(--wa-border);
    border-radius: 3px;
}

.friend-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: var(--wa-panel);
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.friend-item:hover {
    background: var(--wa-panel-hover);
}

.friend-item input[type="checkbox"] {
    display: none;
}

.friend-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}

.friend-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.friend-info span {
    font-size: 14px;
    color: var(--wa-text);
}

.checkmark {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--wa-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--wa-text-muted);
    font-size: 12px;
    transition: all 0.2s;
}

.friend-item input:checked + .friend-info + .checkmark {
    background: var(--wa-accent);
    color: white;
}

.no-friends {
    text-align: center;
    padding: 30px;
    color: var(--wa-text-muted);
}

.no-friends p {
    margin: 0 0 16px 0;
    font-size: 14px;
}

.btn-explore {
    display: inline-block;
    color: var(--wa-accent);
    text-decoration: none;
    font-weight: 600;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
}

.btn-cancel {
    flex: 1;
    padding: 14px 24px;
    background: var(--wa-bg);
    color: var(--wa-text);
    border: none;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: opacity 0.2s;
}

.btn-cancel:hover {
    opacity: 0.9;
}

.btn-create {
    flex: 2;
    padding: 14px 24px;
    background: var(--wa-accent);
    color: white;
    border: none;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.2s;
}

.btn-create:hover {
    opacity: 0.9;
}

.error-message {
    display: block;
    color: var(--wa-red);
    font-size: 12px;
    margin-top: 6px;
}

/* Responsive */
@media (max-width: 768px) {
    .create-group-page {
        padding-top: 0;
    }
}
</style>

<div class="create-group-page">
    <div class="create-group-container">
        <div class="create-group-header">
            <a href="{{ route('chat.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Create New Group</h1>
        </div>

        <form action="{{ route('groups.store') }}" method="POST" enctype="multipart/form-data" class="create-group-form">
            @csrf

            <div class="form-group avatar-upload">
                <label for="avatar" class="avatar-label">
                    <div class="avatar-preview" id="avatarPreview">
                        <i class="fas fa-users"></i>
                        <span>Add Photo</span>
                    </div>
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(event)" hidden>
            </div>

            <div class="form-group">
                <label for="name">Group Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter group name" required maxlength="100">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="What's this group about?" maxlength="500" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}>
                    <span>Private Group</span>
                </label>
                <p class="help-text">Private groups are only visible to members</p>
            </div>

            <div class="form-group members-selection">
                <label>Select Members (Optional)</label>
                <p class="help-text">You can add members now or later</p>

                @if($friends->count() > 0)
                <div class="friends-list">
                    @foreach($friends as $friend)
                    <label class="friend-item">
                        <input type="checkbox" name="members[]" value="{{ $friend->id }}" {{ in_array($friend->id, old('members', [])) ? 'checked' : '' }}>
                        <div class="friend-info">
                            <img src="{{ $friend->avatar_url }}" alt="{{ $friend->username }}">
                            <span>{{ $friend->username }}</span>
                        </div>
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                    </label>
                    @endforeach
                </div>
                @else
                <div class="no-friends">
                    <p>You're not following anyone yet. You can add members later!</p>
                    <a href="{{ route('explore') }}" class="btn-explore">Explore Users</a>
                </div>
                @endif
                @error('members')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('chat.index') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-create">
                    <i class="fas fa-users"></i>
                    Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection