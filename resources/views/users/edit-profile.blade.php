@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<style>
.edit-profile-container { max-width: 680px; margin: 0 auto; }
.edit-header { margin-bottom: 32px; }
.edit-header h1 { font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 8px; }
.edit-header p { color: var(--text-muted); font-size: 14px; }

.edit-card { 
    background: var(--surface); border: 1px solid var(--border); 
    border-radius: var(--radius-lg); padding: 32px; margin-bottom: 24px;
}
.edit-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.edit-card h3 i { color: var(--primary); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: var(--text); }
.form-group label span { color: var(--text-muted); font-weight: 400; }
.form-input, .form-textarea, .form-select {
    width: 100%; padding: 12px 16px; font-size: 15px;
    border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--bg); color: var(--text); transition: all var(--transition);
}
.form-input:focus, .form-textarea:focus, .form-select:focus {
    outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}
.form-textarea { min-height: 100px; resize: vertical; }
.form-select { cursor: pointer; }
.form-checkbox { 
    display: flex; align-items: center; gap: 12px; padding: 16px;
    background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius);
    cursor: pointer; transition: all var(--transition);
}
.form-checkbox:hover { border-color: var(--primary); }
.form-checkbox input { width: 20px; height: 20px; accent-color: var(--primary); }
.form-checkbox-text { flex: 1; }
.form-checkbox-text strong { display: block; color: var(--text); font-size: 14px; }
.form-checkbox-text span { color: var(--text-muted); font-size: 13px; }

.image-upload { display: flex; align-items: center; gap: 20px; }
.image-preview { 
    width: 100px; height: 100px; border-radius: 50%; overflow: hidden;
    background: var(--bg); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center;
}
.image-preview.cover { width: 200px; height: 120px; border-radius: var(--radius); }
.image-preview img { width: 100%; height: 100%; object-fit: cover; }
.image-preview .placeholder { font-size: 32px; color: var(--text-muted); }
.image-upload-actions { flex: 1; }
.upload-btn { 
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--bg); color: var(--text); font-size: 14px; cursor: pointer;
    transition: all var(--transition);
}
.upload-btn:hover { border-color: var(--primary); color: var(--primary); }
.file-input { display: none; }

.form-actions { 
    display: flex; justify-content: space-between; align-items: center; 
    padding-top: 24px; border-top: 1px solid var(--border); margin-top: 32px;
}
.danger-zone { 
    background: rgba(244, 63, 94, 0.05); border: 1px solid rgba(244, 63, 94, 0.2);
    border-radius: var(--radius); padding: 24px; margin-top: 32px;
}
.danger-zone h4 { color: var(--accent); font-size: 16px; font-weight: 700; margin-bottom: 12px; }
.danger-zone p { color: var(--text-muted); font-size: 14px; margin-bottom: 16px; }

@media (max-width: 640px) {
    .form-row { grid-template-columns: 1fr; }
    .edit-card { padding: 20px; }
    .image-upload { flex-direction: column; text-align: center; }
    .form-actions { flex-direction: column; gap: 16px; }
}
</style>

<div class="edit-profile-container">
    <div class="edit-header">
        <h1>Edit Profile</h1>
        <p>Update your personal information and preferences</p>
    </div>

    <form action="{{ route('profile.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Avatar & Cover --}}
        <div class="edit-card">
            <h3><i class="fas fa-images"></i> Profile Images</h3>
            
            <div class="form-group">
                <label>Profile Picture</label>
                <div class="image-upload">
                    <div class="image-preview">
                        <img src="{{ $user->avatar_url }}" alt="Avatar" id="avatar-preview">
                    </div>
                    <div class="image-upload-actions">
                        <label class="upload-btn">
                            <i class="fas fa-camera"></i> Change Avatar
                            <input type="file" name="avatar" class="file-input" accept="image/*" onchange="previewImage(this, 'avatar-preview')">
                        </label>
                        <button type="button" class="btn btn-ghost" onclick="deleteAvatar()" style="margin-left: 8px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Cover Image</label>
                <div class="image-upload">
                    <div class="image-preview cover">
                        @if($user->profile && $user->profile->cover_image)
                            <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="Cover" id="cover-preview">
                        @else
                            <div class="placeholder"><i class="fas fa-image"></i></div>
                        @endif
                    </div>
                    <div class="image-upload-actions">
                        <label class="upload-btn">
                            <i class="fas fa-image"></i> Change Cover
                            <input type="file" name="cover_image" class="file-input" accept="image/*" onchange="previewImage(this, 'cover-preview')">
                        </label>
                        @if($user->profile && $user->profile->cover_image)
                            <button type="button" class="btn btn-ghost" onclick="deleteCover()" style="margin-left: 8px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="edit-card">
            <h3><i class="fas fa-user"></i> Basic Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    @php
                        $canChangeUsername = auth()->user()->canChangeUsername();
                        $cooldownRemaining = auth()->user()->getUsernameChangeCooldownRemaining();
                    @endphp
                    @if(!$canChangeUsername && !auth()->user()->is_admin)
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <i class="fas fa-clock" style="color: var(--accent);"></i>
                            <span style="font-size: 13px; color: var(--accent);">
                                <span id="cooldown-timer" data-seconds="{{ $cooldownRemaining['total_seconds'] }}">Loading...</span>
                            </span>
                        </div>
                    @endif
                    <input type="text" 
                           name="username" 
                           id="username"
                           class="form-input" 
                           value="{{ old('username', $user->username) }}"
                           required
                           minlength="3"
                           maxlength="50"
                           pattern="[a-zA-Z0-9_\-]+"
                           title="Username can only contain letters, numbers, underscores, and hyphens"
                           {{ !$canChangeUsername && !auth()->user()->is_admin ? 'disabled' : '' }}>
                    @if(!$canChangeUsername && !auth()->user()->is_admin)
                        <input type="hidden" name="username" value="{{ $user->username }}">
                        <span style="font-size: 12px; color: var(--text-muted);">
                            <i class="fas fa-info-circle"></i> Username can be changed every 3 days. Admins can change anytime.
                        </span>
                    @endif
                    @error('username')<span style="color: var(--accent); font-size: 13px;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                    @error('name')<span style="color: var(--accent); font-size: 13px;">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                    @error('email')<span style="color: var(--accent); font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Bio <span>(max 500 characters)</span></label>
                <textarea name="bio" class="form-textarea" maxlength="500">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label>About <span>(max 1000 characters)</span></label>
                <textarea name="about" class="form-textarea" maxlength="1000">{{ old('about', $user->profile->about ?? '') }}</textarea>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="edit-card">
            <h3><i class="fas fa-info-circle"></i> Additional Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-input" value="{{ old('location', $user->profile->location ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" class="form-input" value="{{ old('website', $user->profile->website ?? '') }}" placeholder="https://example.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Occupation</label>
                    <input type="text" name="occupation" class="form-input" value="{{ old('occupation', $user->profile->occupation ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $user->profile->phone ?? '') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="male" {{ (old('gender', $user->profile->gender ?? '') == 'male') ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ (old('gender', $user->profile->gender ?? '') == 'female') ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ (old('gender', $user->profile->gender ?? '') == 'other') ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" name="birth_date" class="form-input" value="{{ old('birth_date', $user->profile->birth_date ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Privacy --}}
        <div class="edit-card">
            <h3><i class="fas fa-lock"></i> Privacy Settings</h3>
            
            <label class="form-checkbox">
                <input type="checkbox" name="is_private" value="1" {{ (old('is_private', $user->profile->is_private ?? false)) ? 'checked' : '' }}>
                <div class="form-checkbox-text">
                    <strong>Private Account</strong>
                    <span>Only your followers can see your posts and profile details</span>
                </div>
            </label>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <a href="{{ route('users.show', $user) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>

    {{-- Danger Zone --}}
    <div class="danger-zone">
        <h4><i class="fas fa-exclamation-triangle"></i> Danger Zone</h4>
        <p>Once you delete your account, there is no going back. Please be certain.</p>
        <button type="button" class="btn" style="background: var(--accent); color: white;" onclick="confirmDeleteAccount()">
            <i class="fas fa-trash"></i> Delete Account
        </button>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new image if placeholder exists
                const container = input.closest('.image-upload').querySelector('.image-preview');
                container.innerHTML = `<img src="${e.target.result}" id="${previewId}">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteAvatar() {
    if (!confirm('Delete your profile picture?')) return;
    
    fetch('{{ route("profile.delete-avatar") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete avatar');
        }
    });
}

function deleteCover() {
    if (!confirm('Delete your cover image?')) return;
    
    fetch('{{ route("profile.delete-cover") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete cover image');
        }
    });
}

function confirmDeleteAccount() {
    if (!confirm('WARNING: This will permanently delete your account and all data. Are you sure?')) return;
    if (!confirm('This action cannot be undone. Type "DELETE" to confirm.')) return;
    
    fetch('{{ route("profile.delete-account") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("home") }}';
        } else {
            alert(data.message || 'Failed to delete account');
        }
    });
}

// Username change cooldown countdown timer
function updateCooldownTimer() {
    const timerElement = document.getElementById('cooldown-timer');
    if (!timerElement) return;

    let seconds = parseInt(timerElement.getAttribute('data-seconds'));

    if (seconds <= 0) {
        timerElement.textContent = 'You can change your username now!';
        timerElement.style.color = '#22c55e'; // green
        // Enable the username field
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            usernameInput.disabled = false;
        }
        return;
    }

    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    let timeString = '';
    if (days > 0) {
        timeString = `${days}d ${hours}h ${minutes}m ${secs}s remaining`;
    } else if (hours > 0) {
        timeString = `${hours}h ${minutes}m ${secs}s remaining`;
    } else if (minutes > 0) {
        timeString = `${minutes}m ${secs}s remaining`;
    } else {
        timeString = `${secs}s remaining`;
    }

    timerElement.textContent = timeString;

    // Update the data attribute
    timerElement.setAttribute('data-seconds', seconds - 1);

    // Update every second
    setTimeout(updateCooldownTimer, 1000);
}

// Start the timer when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateCooldownTimer();
});
</script>
@endsection
