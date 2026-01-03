@extends('layouts.app')

@section('title', 'Edit User - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>Edit User</h1>
        <div class="header-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to User Details
            </a>
        </div>
    </div>

    <div class="edit-form-container">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2>Basic Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->name) }}" required minlength="3" maxlength="50">
                        @error('username')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" minlength="8">
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="is_admin">Admin Privileges</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                            <label for="is_admin" class="checkbox-label">Grant admin privileges</label>
                        </div>
                        @error('is_admin')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="is_suspended">Account Suspension</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_suspended" name="is_suspended" value="1" {{ old('is_suspended', $user->is_suspended) ? 'checked' : '' }}>
                            <label for="is_suspended" class="checkbox-label">Suspend this account</label>
                        </div>
                        @error('is_suspended')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <small class="form-help">Suspended users cannot log in and will see a suspension message.</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Profile Information</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="3" maxlength="500" placeholder="Tell us about yourself...">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                        @error('bio')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="about">About</label>
                        <textarea id="about" name="about" rows="4" maxlength="1000" placeholder="More detailed information...">{{ old('about', $user->profile->about ?? '') }}</textarea>
                        @error('about')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" value="{{ old('website', $user->profile->website ?? '') }}" placeholder="https://example.com">
                        @error('website')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location', $user->profile->location ?? '') }}" placeholder="City, Country">
                        @error('location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" id="occupation" name="occupation" value="{{ old('occupation', $user->profile->occupation ?? '') }}" placeholder="Your job title">
                        @error('occupation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->profile->phone ?? '') }}" placeholder="+1 (555) 123-4567">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Not specified</option>
                            <option value="male" {{ old('gender', $user->profile->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->profile->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $user->profile->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="birth_date">Birth Date</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->profile->birth_date ?? '') }}">
                        @error('birth_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="is_private">Privacy Settings</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_private" name="is_private" value="1" {{ old('is_private', $user->profile->is_private ?? false) ? 'checked' : '' }}>
                            <label for="is_private" class="checkbox-label">Make profile private</label>
                        </div>
                        @error('is_private')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Profile Images</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Current Avatar</label>
                        <div class="current-image">
                            @if($user->profile && $user->profile->avatar)
                                <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Current Avatar" class="current-avatar">
                                <div class="image-actions">
                                    <label for="avatar" class="btn-secondary btn-small">
                                        <i class="fas fa-upload"></i>
                                        Change Avatar
                                    </label>
                                    <button type="button" onclick="removeCurrentAvatar()" class="btn-danger btn-small">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="no-image">
                                    <i class="fas fa-user"></i>
                                    <span>No avatar set</span>
                                    <label for="avatar" class="btn-primary btn-small">
                                        <i class="fas fa-upload"></i>
                                        Upload Avatar
                                    </label>
                                </div>
                            @endif
                        </div>
                        <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;">
                        <input type="hidden" id="remove_avatar" name="remove_avatar" value="0">
                        @error('avatar')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label>Current Cover Image</label>
                        <div class="current-image">
                            @if($user->profile && $user->profile->cover_image)
                                <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="Current Cover" class="current-cover">
                                <div class="image-actions">
                                    <label for="cover_image" class="btn-secondary btn-small">
                                        <i class="fas fa-upload"></i>
                                        Change Cover
                                    </label>
                                    <button type="button" onclick="removeCurrentCover()" class="btn-danger btn-small">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <span>No cover image set</span>
                                    <label for="cover_image" class="btn-primary btn-small">
                                        <i class="fas fa-upload"></i>
                                        Upload Cover
                                    </label>
                                </div>
                            @endif
                        </div>
                        <input type="file" id="cover_image" name="cover_image" accept="image/*" style="display: none;">
                        <input type="hidden" id="remove_cover" name="remove_cover" value="0">
                        @error('cover_image')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Update User
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.admin-page {
    max-width: 800px;
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

.edit-form-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    box-shadow: var(--shadow);
}

.form-section {
    margin-bottom: 30px;
}

.form-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
    padding-bottom: 10px;
    border-bottom: 2px solid var(--twitter-blue);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    background: var(--input-bg);
    color: var(--twitter-dark);
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.4;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 0;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--twitter-blue);
}

.checkbox-label {
    font-weight: 500;
    color: var(--twitter-dark);
    cursor: pointer;
    user-select: none;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-primary:hover {
    background: #1991DB;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--card-bg);
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 12px 24px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
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

    .edit-form-container {
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        justify-content: center;
    }
}

/* Profile Images Section */
.current-image {
    position: relative;
    margin-top: 8px;
}

.current-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--border-color);
    box-shadow: var(--shadow);
}

.current-cover {
    width: 100%;
    max-width: 400px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid var(--border-color);
    box-shadow: var(--shadow);
}

.image-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 8px 12px;
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.no-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    background: var(--twitter-light);
}

.no-image i {
    font-size: 32px;
    color: var(--twitter-gray);
}

.no-image span {
    color: var(--twitter-gray);
    font-weight: 500;
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

    .edit-form-container {
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        justify-content: center;
    }

    .image-actions {
        flex-direction: column;
    }

    .current-avatar {
        width: 100px;
        height: 100px;
    }

    .current-cover {
        height: 120px;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 24px;
    }

    .form-section h2 {
        font-size: 18px;
    }

    .edit-form-container {
        padding: 16px;
    }

    .current-avatar {
        width: 80px;
        height: 80px;
    }

    .current-cover {
        height: 100px;
    }

    .no-image {
        padding: 20px;
    }

    .no-image i {
        font-size: 24px;
    }
}
</style>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this user? This action cannot be undone.');
}

function removeCurrentAvatar() {
    if (confirm('Are you sure you want to remove the current avatar?')) {
        // Set the hidden input value to indicate avatar should be removed
        document.getElementById('remove_avatar').value = '1';

        // Reset the file input
        document.getElementById('avatar').value = '';

        // Hide the current avatar display and show the upload placeholder
        document.querySelector('.current-image').innerHTML = `
            <div class="no-image">
                <i class="fas fa-user"></i>
                <span>No avatar set</span>
                <label for="avatar" class="btn-primary btn-small">
                    <i class="fas fa-upload"></i>
                    Upload Avatar
                </label>
            </div>
        `;
    }
}

function removeCurrentCover() {
    if (confirm('Are you sure you want to remove the current cover image?')) {
        // Set the hidden input value to indicate cover should be removed
        document.getElementById('remove_cover').value = '1';

        // Reset the file input
        document.getElementById('cover_image').value = '';

        // Hide the current cover display and show the upload placeholder
        const coverContainer = document.querySelectorAll('.current-image')[1];
        coverContainer.innerHTML = `
            <div class="no-image">
                <i class="fas fa-image"></i>
                <span>No cover image set</span>
                <label for="cover_image" class="btn-primary btn-small">
                    <i class="fas fa-upload"></i>
                    Upload Cover
                </label>
            </div>
        `;
    }
}
</script>
@endsection
