@extends('layouts.app')

@section('title', 'Create Group')

@section('content')
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
                    <span class="checkbox-custom"></span>
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
                            @if($friend->profile && $friend->profile->avatar)
                                <img src="{{ asset('storage/' . $friend->profile->avatar) }}" alt="{{ $friend->name }}">
                            @else
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <span>{{ $friend->name }}</span>
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

<style>
.create-group-page {
    min-height: 100vh;
    padding: 100px 20px 40px;
    background: var(--twitter-light);
}

.create-group-container {
    max-width: 600px;
    margin: 0 auto;
    background: var(--card-bg);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.create-group-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.back-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.create-group-header h1 {
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 8px;
}

.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 4px rgba(29, 161, 242, 0.1);
}

.avatar-upload {
    text-align: center;
}

.avatar-label {
    cursor: pointer;
    display: inline-block;
}

.avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s;
}

.avatar-preview:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
}

.avatar-preview i {
    font-size: 40px;
    margin-bottom: 8px;
}

.avatar-preview span {
    font-size: 12px;
    font-weight: 600;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.checkbox-group {
    background: var(--hover-bg);
    padding: 16px;
    border-radius: 12px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-weight: 600;
}

.checkbox-label input {
    display: none;
}

.checkbox-custom {
    width: 24px;
    height: 24px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.checkbox-label input:checked + .checkbox-custom {
    background: var(--twitter-blue);
    border-color: var(--twitter-blue);
}

.checkbox-label input:checked + .checkbox-custom::after {
    content: '✓';
    color: white;
    font-size: 14px;
}

.help-text {
    font-size: 13px;
    color: var(--twitter-gray);
    margin-top: 8px;
}

.members-selection .friends-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 12px;
}

.friend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background 0.2s;
}

.friend-item:last-child {
    border-bottom: none;
}

.friend-item:hover {
    background: var(--hover-bg);
}

.friend-item input {
    display: none;
}

.friend-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.friend-info img,
.friend-info .avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.friend-info .avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
}

.checkmark {
    width: 24px;
    height: 24px;
    border: 2px solid var(--border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: all 0.2s;
}

.friend-item input:checked ~ .checkmark {
    background: var(--twitter-blue);
    border-color: var(--twitter-blue);
    color: white;
}

.no-friends {
    text-align: center;
    padding: 40px;
    background: var(--hover-bg);
    border-radius: 12px;
}

.btn-explore {
    display: inline-block;
    margin-top: 16px;
    padding: 12px 24px;
    background: var(--twitter-blue);
    color: white;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-explore:hover {
    background: #1a8cd8;
}

.error-message {
    color: var(--error-color);
    font-size: 13px;
    margin-top: 4px;
    display: block;
}

.form-actions {
    display: flex;
    gap: 16px;
    margin-top: 32px;
}

.btn-cancel {
    flex: 1;
    padding: 14px 24px;
    background: var(--hover-bg);
    color: var(--twitter-dark);
    border-radius: 25px;
    text-align: center;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: var(--border-color);
}

.btn-create {
    flex: 2;
    padding: 14px 24px;
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}



.btn-create:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
    
@media (max-width: 480px) {
    .create-group-page {
        padding: 80px 16px 24px;
    }

    .create-group-container {
        padding: 20px;
    }

    .create-group-header h1 {
        font-size: 20px;
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
    }

    .avatar-preview i {
        font-size: 32px;
    }
}
</style>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection