@extends('layouts.app')

@section('title', __('admin.edit_user_title') . ' - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.users.show', $user) }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>{{ __('admin.edit_user_title') }}</h1>
                <p>{{ __('admin.edit_user_subtitle') }}</p>
            </div>
        </div>
    </div>

    <div class="edit-form">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Basic Info Section --}}
            <div class="form-card">
                <h2><i class="fas fa-user"></i> {{ __('admin.basic_info') }}</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>{{ __('admin.full_name_label') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required minlength="1" maxlength="255" autocomplete="name">
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.username_label') }} *</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_\-]+" autocomplete="username">
                        @error('username')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        <small class="help-text">{{ __('admin.username_help') }}</small>
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.email_label') }} *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.new_password_label') }} <small>({{ __('admin.leave_blank_keep_current') }})</small></label>
                        <input type="password" name="password" minlength="8" autocomplete="new-password">
                        @error('password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.account_status') }}</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                            <label for="is_admin">{{ __('admin.grant_admin') }}</label>
                        </div>
                    </div>

                    <div class="form-group full">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_suspended" id="is_suspended" value="1" {{ old('is_suspended', $user->is_suspended) ? 'checked' : '' }}>
                            <label for="is_suspended">{{ __('admin.suspend_account') }}</label>
                        </div>
                        <small class="help-text">{{ __('admin.suspend_help') }}</small>
                    </div>
                </div>
            </div>

            {{-- Profile Section --}}
            <div class="form-card">
                <h2><i class="fas fa-id-card"></i> {{ __('admin.profile_info') }}</h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>{{ __('admin.bio') }}</label>
                        <textarea name="bio" rows="3" maxlength="500" placeholder="{{ __('admin.bio_placeholder') }}">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                    </div>

                    <div class="form-group full">
                        <label>{{ __('admin.about') }}</label>
                        <textarea name="about" rows="3" maxlength="1000" placeholder="{{ __('admin.about_placeholder') }}">{{ old('about', $user->profile->about ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.website') }}</label>
                        <input type="url" name="website" value="{{ old('website', $user->profile->website ?? '') }}" placeholder="{{ __('admin.website_placeholder') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.location') }}</label>
                        <input type="text" name="location" value="{{ old('location', $user->profile->location ?? '') }}" placeholder="{{ __('admin.location_placeholder') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.occupation') }}</label>
                        <input type="text" name="occupation" value="{{ old('occupation', $user->profile->occupation ?? '') }}" placeholder="{{ __('admin.occupation_placeholder') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.phone') }}</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->profile->phone ?? '') }}" placeholder="{{ __('admin.phone_placeholder') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.gender') }}</label>
                        <select name="gender">
                            <option value="">{{ __('admin.not_specified') }}</option>
                            <option value="male" {{ old('gender', $user->profile->gender ?? '') === 'male' ? 'selected' : '' }}>{{ __('admin.male') }}</option>
                            <option value="female" {{ old('gender', $user->profile->gender ?? '') === 'female' ? 'selected' : '' }}>{{ __('admin.female') }}</option>
                            <option value="other" {{ old('gender', $user->profile->gender ?? '') === 'other' ? 'selected' : '' }}>{{ __('admin.other') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.birth_date') }}</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $user->profile->birth_date ?? '') }}">
                    </div>

                    <div class="form-group full">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_private" id="is_private" value="1" {{ old('is_private', $user->profile->is_private ?? false) ? 'checked' : '' }}>
                            <label for="is_private">{{ __('admin.make_private') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Images Section --}}
            <div class="form-card">
                <h2><i class="fas fa-images"></i> {{ __('admin.profile_images') }}</h2>
                <div class="images-grid">
                    <div class="image-upload">
                        <label>{{ __('admin.avatar') }}</label>
                        <div class="image-preview">
                            <img src="{{ $user->avatar_url }}" alt="{{ __('admin.avatar') }}" id="avatar-preview">
                        </div>
                        <div class="image-actions">
                            <label for="avatar" class="btn-upload">
                                <i class="fas fa-upload"></i> {{ __('admin.upload') }}
                            </label>
                            <button type="button" onclick="removeImage('avatar')" class="btn-remove">
                                <i class="fas fa-trash"></i> {{ __('admin.remove') }}
                            </button>
                        </div>
                        <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;" onchange="previewImage(this, 'avatar-preview')">
                        <input type="hidden" name="remove_avatar" id="remove-avatar" value="0">
                    </div>

                    <div class="image-upload">
                        <label>{{ __('admin.cover_image') }}</label>
                        <div class="image-preview cover">
                            @if($user->profile && $user->profile->cover_image)
                                <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="{{ __('admin.cover_image') }}" id="cover-preview">
                            @else
                                <div class="no-image" id="cover-preview">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </div>
                        <div class="image-actions">
                            <label for="cover_image" class="btn-upload">
                                <i class="fas fa-upload"></i> {{ __('admin.upload') }}
                            </label>
                            @if($user->profile && $user->profile->cover_image)
                            <button type="button" onclick="removeImage('cover')" class="btn-remove">
                                <i class="fas fa-trash"></i> {{ __('admin.remove') }}
                            </button>
                            @endif
                        </div>
                        <input type="file" id="cover_image" name="cover_image" accept="image/*" style="display: none;" onchange="previewImage(this, 'cover-preview')">
                        <input type="hidden" name="remove_cover" id="remove-cover" value="0">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('admin.users.show', $user) }}" class="btn-cancel">
                    <i class="fas fa-times"></i> {{ __('admin.cancel') }}
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> {{ __('admin.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 16px 40px;
}

.admin-header {
    display: flex;
    align-items: center;
    margin: 0 -16px 24px;
    padding: 20px 16px;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    border-radius: 16px 16px 20px 20px;
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

.edit-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
}

.form-card h2 {
    margin: 0 0 20px;
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.form-card h2 i {
    color: #6366f1;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-group label small {
    font-weight: 400;
    color: var(--text-muted);
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 14px;
    background: var(--input-bg);
    color: var(--text);
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.error {
    color: #ef4444;
    font-size: 12px;
}

.help-text {
    font-size: 12px;
    color: var(--text-muted);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #6366f1;
    cursor: pointer;
}

.checkbox-group label {
    font-weight: 500;
    color: var(--text);
    cursor: pointer;
    margin: 0;
}

.toggle-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.toggle-item {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.toggle-item input {
    display: none;
}

.toggle-switch {
    width: 44px;
    height: 24px;
    background: var(--border-color);
    border-radius: 12px;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.toggle-switch::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: all 0.2s ease;
}

.toggle-item input:checked + .toggle-switch {
    background: #6366f1;
}

.toggle-item input:checked + .toggle-switch.admin {
    background: #f43f5e;
}

.toggle-item input:checked + .toggle-switch.suspend {
    background: #f59e0b;
}

.toggle-item input:checked + .toggle-switch.private {
    background: #10b981;
}

.toggle-item input:checked + .toggle-switch::after {
    left: 23px;
}

.toggle-label {
    font-size: 14px;
    color: var(--text);
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.image-upload {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.image-upload label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.image-preview {
    width: 100%;
    height: 150px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--bg);
    border: 2px dashed var(--border-color);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview.cover {
    height: 120px;
}

.image-preview .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 32px;
}

.image-actions {
    display: flex;
    gap: 8px;
}

.btn-upload, .btn-remove {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
    border: none;
}

.btn-upload {
    background: #6366f1;
    color: white;
}

.btn-upload:hover {
    background: #4f46e5;
}

.btn-remove {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.btn-remove:hover {
    background: #ef4444;
    color: white;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-cancel, .btn-submit {
    padding: 14px 28px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-cancel {
    background: var(--bg);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
}

.btn-cancel:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.btn-submit {
    background: #6366f1;
    color: white;
    border: none;
}

.btn-submit:hover {
    background: #4f46e5;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .images-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel, .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(type) {
    const message = type === 'avatar' 
        ? "{{ str_replace(':type', 'avatar', __('admin.remove_image_confirm')) }}"
        : "{{ str_replace(':type', 'cover_image', __('admin.remove_image_confirm')) }}";
    if (confirm(message)) {
        document.getElementById('remove-' + type).value = '1';
        const preview = document.getElementById(type + '-preview');
        preview.innerHTML = '<i class="fas fa-' + (type === 'avatar' ? 'user' : 'image') + '"></i>';
    }
}
</script>
@endsection
