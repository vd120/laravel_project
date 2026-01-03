@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <h2>Edit Profile</h2>

    @if(session('success'))
        <div style="color: var(--success-color); margin-bottom: 15px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf

    <div style="margin-bottom: 20px; padding: 15px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px;">
        <label style="display: flex; align-items: center; cursor: pointer;">
            <input type="checkbox" name="is_private" value="1" {{ old('is_private', $user->profile->is_private ?? false) ? 'checked' : '' }} style="margin-right: 10px;">
            <span style="font-weight: bold; color: var(--twitter-dark);">Private Account</span>
        </label>
        <p style="margin: 5px 0 0 25px; color: var(--twitter-gray); font-size: 14px;">When your account is private, only approved followers can see your posts</p>
        @error('is_private') <div style="color: var(--error-color); margin-top: 5px;">{{ $message }}</div> @enderror
    </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                <div style="position: relative; margin-bottom: 10px; border: 2px dashed var(--border-color); border-radius: 8px; padding: 10px; background: var(--card-bg);">
                    <img id="cover-preview" src="{{ $user->profile && $user->profile->cover_image ? asset('storage/' . $user->profile->cover_image) : '' }}" alt="Cover Preview" style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px; {{ $user->profile && $user->profile->cover_image ? 'display: block;' : 'display: none;' }}" onerror="this.style.display='none'; document.getElementById('cover-placeholder').style.display='block';">
                    <div class="preview-placeholder" id="cover-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: var(--twitter-gray); font-size: 14px; {{ $user->profile && $user->profile->cover_image ? 'display: none;' : 'display: block;' }}">
                        Cover Image Preview
                    </div>
                </div>
                <label for="cover_image">Cover Image</label><br>
                <input type="file" name="cover_image" id="cover_image" accept="image/*" style="margin-top: 5px;" onchange="previewImage(this, document.getElementById('cover-preview'))">
                @if($user->profile && $user->profile->cover_image)
                    <button type="button" onclick="deleteCoverImage()" style="margin-top: 5px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">Delete Cover Image</button>
                @endif
                @error('cover_image') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>

            <div>
                <div style="position: relative; margin-bottom: 10px; border: 2px dashed var(--border-color); border-radius: 50%; padding: 5px; background: var(--card-bg); width: 110px; height: 110px; margin: 0 auto;">
                    <img id="avatar-preview" src="{{ $user->profile && $user->profile->avatar ? asset('storage/' . $user->profile->avatar) : '' }}" alt="Avatar Preview" style="width: 100px; height: 100px; border-radius: 50%; {{ $user->profile && $user->profile->avatar ? 'display: block;' : 'display: none;' }}" onerror="this.style.display='none'; document.getElementById('avatar-placeholder').style.display='block';">
                    <div class="preview-placeholder" id="avatar-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: var(--twitter-gray); font-size: 12px; {{ $user->profile && $user->profile->avatar ? 'display: none;' : 'display: block;' }}">
                        Profile Picture Preview
                    </div>
                </div>
                <label for="avatar">Profile Picture</label><br>
                <input type="file" name="avatar" id="avatar" accept="image/*" style="margin-top: 5px;" onchange="previewImage(this, document.getElementById('avatar-preview'))">
                @if($user->profile && $user->profile->avatar)
                    <button type="button" onclick="deleteAvatar()" style="margin-top: 5px; padding: 5px 10px; background: var(--error-color); color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">Delete Profile Picture</button>
                @endif
                @error('avatar') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>
        </div>



        <div style="margin-bottom: 15px;">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->name) }}" required minlength="3" maxlength="50" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;" title="Username must be 3-50 characters, using only letters, numbers, underscores, and hyphens">
            <div id="username-status" class="username-status"></div>
            @error('username') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
            @error('email') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label for="about">About (Detailed description)</label>
            <textarea name="about" id="about" rows="4" maxlength="1000" placeholder="Write more about yourself..." style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">{{ old('about', $user->profile->about ?? '') }}</textarea>
            @error('about') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="margin-bottom: 15px;">
                <label for="website">Website</label>
                <input type="url" name="website" id="website" value="{{ old('website', $user->profile->website ?? '') }}" placeholder="https://yourwebsite.com" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                @error('website') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 15px;">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" value="{{ old('location', $user->profile->location ?? '') }}" placeholder="City, Country" maxlength="255" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                @error('location') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 15px;">
                <label for="occupation">Occupation</label>
                <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $user->profile->occupation ?? '') }}" placeholder="Your job title" maxlength="255" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                @error('occupation') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 15px;">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->profile->phone ?? '') }}" placeholder="+1 (555) 123-4567" maxlength="20" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                @error('phone') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="margin-bottom: 15px;">
                <label for="birth_date">Birth Date</label>
                <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $user->profile->birth_date ?? '') }}" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                @error('birth_date') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 15px;">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px;">
                    <option value="">Select Gender</option>
                    <option value="male" {{ old('gender', $user->profile->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $user->profile->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender', $user->profile->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender') <div style="color: var(--error-color);">{{ $message }}</div> @enderror
            </div>
        </div>



        <button type="submit" class="btn">Update Profile</button>
        <a href="{{ route('users.show', $user) }}" style="margin-left: 10px;">Cancel</a>
    </form>



    
    <div style="margin-top: 40px; padding: 20px; background: var(--error-bg); border: 2px solid var(--error-color); border-radius: 8px;">
        <h3 style="color: var(--error-color); margin-top: 0; margin-bottom: 15px;">⚠️ Danger Zone</h3>

        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--twitter-dark); margin-bottom: 10px;">Delete Account</h4>
            <p style="color: var(--twitter-gray); margin-bottom: 15px; font-size: 14px;">
                Once you delete your account, there is no going back. Please be certain.
                This action will permanently delete your account and all associated data including:
            </p>
            <ul style="color: var(--twitter-gray); font-size: 14px; margin-bottom: 15px; padding-left: 20px;">
                <li>All your posts and media</li>
                <li>All your comments and likes</li>
                <li>Your followers and following relationships</li>
                <li>Your stories and story views</li>
                <li>Your profile information and settings</li>
                <li>All saved posts</li>
            </ul>
        </div>

        <button type="button" onclick="showDeleteConfirmation()" style="padding: 10px 20px; background: var(--error-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;" onmouseover="this.style.background='#c53030'" onmouseout="this.style.background='var(--error-color)'">
            Delete My Account
        </button>
    </div>

    
    <div id="delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: var(--card-bg); padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; text-align: center; border: 1px solid var(--border-color);">
            <h3 style="color: var(--error-color); margin-top: 0;">Are you absolutely sure?</h3>
            <p style="color: var(--twitter-gray); margin-bottom: 20px;">
                This action cannot be undone. Your account and all data will be permanently deleted.
            </p>
            <div style="margin-bottom: 20px;">
                <label for="delete-email" style="display: block; margin-bottom: 10px; font-weight: bold; color: var(--twitter-dark);">
                    Enter your email address:
                </label>
                <input type="email" id="delete-email" placeholder="your.email@example.com" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px; text-align: center; font-size: 16px; margin-bottom: 15px;">

                <label for="delete-confirmation" style="display: block; margin-bottom: 10px; font-weight: bold; color: var(--twitter-dark);">
                    Type "DELETE" to confirm:
                </label>
                <input type="text" id="delete-confirmation" placeholder="Type DELETE here" style="width: 100%; padding: 10px; background: var(--input-bg); color: var(--twitter-dark); border: 1px solid var(--border-color); border-radius: 5px; text-align: center; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="confirmDeleteAccount()" style="padding: 10px 20px; background: var(--error-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;" id="confirm-delete-btn" disabled>
                    Yes, Delete My Account
                </button>
                <button type="button" onclick="hideDeleteConfirmation()" style="padding: 10px 20px; background: var(--twitter-gray); color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Username input field animations */
#username {
    transition: all 0.3s ease;
    position: relative;
}

#username.checking {
    border-color: #ffa500;
    box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
    animation: pulseBorder 1.5s ease-in-out infinite;
}

#username.available {
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    animation: successGlow 0.6s ease-out;
}

#username.taken {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    animation: errorShake 0.6s ease-in-out;
}

#username.invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    animation: errorShake 0.6s ease-in-out;
}

#username.error {
    border-color: #6c757d;
    box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.1);
    animation: errorPulse 0.8s ease-in-out;
}

/* Username status message - plain text only */
.username-status {
    font-size: 12px;
    margin-top: 8px;
    font-weight: 500;
    min-height: 16px;
}

.username-status.checking {
    color: #666;
}

.username-status.available {
    color: #28a745;
}

.username-status.taken {
    color: #dc3545;
}

.username-status.invalid {
    color: #dc3545;
}

.username-status.warning {
    color: #856404;
}

.username-status.error {
    color: #6c757d;
}

/* Keyframe animations */
@keyframes pulseBorder {
    0%, 100% {
        box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(255, 165, 0, 0.2);
    }
}

@keyframes successGlow {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
}

@keyframes errorShake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-3px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(3px);
    }
}

@keyframes errorPulse {
    0%, 100% {
        box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.1);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(108, 117, 125, 0.2);
    }
}

@keyframes checkingPulse {
    0%, 100% {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        transform: scale(1);
    }
    50% {
        background: linear-gradient(135deg, #ffeaa7, #fff3cd);
        transform: scale(1.01);
    }
}

@keyframes shimmer {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

@keyframes successSlideIn {
    0% {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
        background: linear-gradient(135deg, #ffffff, #d4edda);
    }
    50% {
        opacity: 0.8;
        transform: translateY(0) scale(1.02);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
    }
}

@keyframes warningSlideIn {
    0% {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
        background: linear-gradient(135deg, #ffffff, #fff3cd);
    }
    50% {
        opacity: 0.8;
        transform: translateY(0) scale(1.02);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    }
}

@keyframes errorSlideIn {
    0% {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
        background: linear-gradient(135deg, #ffffff, #f8d7da);
    }
    50% {
        opacity: 0.8;
        transform: translateY(0) scale(1.02);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    }
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3) translateY(-20px);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05) translateY(0);
    }
    70% {
        transform: scale(0.9) translateY(0);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-4px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(4px);
    }
}

@keyframes pulseError {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    25% {
        opacity: 0.8;
        transform: scale(1.05);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.1);
    }
    75% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}



/* Password input container styles */
.password-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-container input {
    flex: 1;
    padding-right: 45px; /* Make room for the toggle button */
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: color 0.2s ease, background-color 0.2s ease;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
}

.password-toggle:hover {
    color: #333;
    background-color: rgba(0, 0, 0, 0.05);
}

.password-toggle:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.password-strength {
    height: 5px;
    margin-top: 5px;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.password-strength.weak {
    background-color: #ff4444;
    width: 25%;
}

.password-strength.medium {
    background-color: #ffaa00;
    width: 50%;
}

.password-strength.strong {
    background-color: #00aa00;
    width: 75%;
}

.password-strength.very-strong {
    background-color: #00dd00;
    width: 100%;
}

#password-strength-text {
    font-size: 12px;
    margin-top: 5px;
    color: #666;
}

/* Hover effects */
#username:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

#username:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(29, 161, 242, 0.2);
}
</style>

<script>


// Image preview functionality
function previewImage(input, previewElement) {
    console.log('previewImage called for', previewElement.id);
    console.log('Input files:', input.files);

    if (input.files && input.files[0]) {
        console.log('File selected:', input.files[0].name, 'Size:', input.files[0].size);

        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('FileReader onload fired, result length:', e.target.result.length);
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';

            // Hide placeholder text
            const placeholder = previewElement.parentElement.querySelector('.preview-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
                console.log('Placeholder hidden');
            }

            console.log('Image preview updated for', previewElement.id);
        };

        reader.onerror = function(e) {
            console.error('FileReader error:', e);
        };

        console.log('Starting to read file as data URL');
        reader.readAsDataURL(input.files[0]);
    } else {
        console.log('No file selected');
    }
}



// Initialize image previews immediately and on page load
function initImagePreviews() {
    console.log('Initializing image previews');

    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    console.log('Avatar input found:', !!avatarInput);
    console.log('Avatar preview found:', !!avatarPreview);

    if (avatarInput && avatarPreview) {
        // Remove existing listeners to avoid duplicates
        avatarInput.removeEventListener('change', avatarInput._previewHandler);
        avatarInput._previewHandler = function() {
            console.log('Avatar file selected, calling previewImage');
            previewImage(this, avatarPreview);
        };
        avatarInput.addEventListener('change', avatarInput._previewHandler);
        console.log('Avatar preview listener attached');
    }

    // Cover image preview
    const coverInput = document.getElementById('cover_image');
    const coverPreview = document.getElementById('cover-preview');

    console.log('Cover input found:', !!coverInput);
    console.log('Cover preview found:', !!coverPreview);

    if (coverInput && coverPreview) {
        // Remove existing listeners to avoid duplicates
        coverInput.removeEventListener('change', coverInput._previewHandler);
        coverInput._previewHandler = function() {
            console.log('Cover file selected, calling previewImage');
            previewImage(this, coverPreview);
        };
        coverInput.addEventListener('change', coverInput._previewHandler);
        console.log('Cover preview listener attached');
    }
}

// Image deletion functions
function deleteCoverImage() {
    if (confirm('Are you sure you want to delete your cover image?')) {
        fetch('/profile/delete-cover', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the preview
                document.getElementById('cover-preview').style.display = 'none';
                document.getElementById('cover-preview').src = '';
                document.getElementById('cover-placeholder').style.display = 'block';

                // Remove the delete button
                const deleteBtn = document.querySelector('button[onclick="deleteCoverImage()"]');
                if (deleteBtn) deleteBtn.remove();

                alert('Cover image deleted successfully!');
            } else {
                alert('Failed to delete cover image: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the cover image.');
        });
    }
}

function deleteAvatar() {
    if (confirm('Are you sure you want to delete your profile picture?')) {
        fetch('/profile/delete-avatar', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the preview
                document.getElementById('avatar-preview').style.display = 'none';
                document.getElementById('avatar-preview').src = '';
                document.getElementById('avatar-placeholder').style.display = 'block';

                // Remove the delete button
                const deleteBtn = document.querySelector('button[onclick="deleteAvatar()"]');
                if (deleteBtn) deleteBtn.remove();

                alert('Profile picture deleted successfully!');
            } else {
                alert('Failed to delete profile picture: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the profile picture.');
        });
    }
}

// Initialize immediately
initImagePreviews();

// Also initialize on DOMContentLoaded for safety
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired, reinitializing image previews');
    initImagePreviews();

    // Username validation for edit profile
    const usernameInput = document.getElementById('username');
    console.log('Username input found:', !!usernameInput);

    if (usernameInput) {
        console.log('Attaching username validation listeners');

        usernameInput.addEventListener('input', function() {
            console.log('Username input event triggered:', this.value);
            const username = this.value.trim();

            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }

            // Validate input format first
            if (!validateUsernameInput(username)) {
                console.log('Username format invalid');
                return;
            }

            console.log('Scheduling username check for:', username);
            // Debounce the API call
            usernameCheckTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, 500);
        });

        // Also check on focus/blur for better UX
        usernameInput.addEventListener('blur', function() {
            const username = this.value.trim();
            console.log('Username blur event triggered:', username);
            if (username && validateUsernameInput(username)) {
                console.log('Checking username availability on blur');
                checkUsernameAvailability(username);
            }
        });

        // Initial check if there's already a value
        const initialValue = usernameInput.value.trim();
        if (initialValue) {
            console.log('Initial username value:', initialValue);
            if (validateUsernameInput(initialValue)) {
                checkUsernameAvailability(initialValue);
            }
        }
    } else {
        console.error('Username input element not found!');
    }
});

// Username checking variables
let usernameCheckTimeout = null;
let currentCheckRequest = null;

// Username validation function
function validateUsernameInput(username) {
    // Allow only alphanumeric characters, underscores, and hyphens
    const validPattern = /^[a-zA-Z0-9_-]*$/;

    if (!validPattern.test(username)) {
        const statusDiv = document.getElementById('username-status');
        statusDiv.textContent = 'Only letters, numbers, underscores, and hyphens allowed';
        statusDiv.className = 'username-status invalid';
        return false;
    }

    return true;
}

// Username availability checking function
function checkUsernameAvailability(username) {
    const statusDiv = document.getElementById('username-status');
    const usernameInput = document.getElementById('username');

    // Don't check if it's the current user's username
    if (username === '{{ auth()->user()->name }}') {
        statusDiv.textContent = '✅ This is your current username';
        statusDiv.className = 'username-status available';
        return;
    }

    if (!username) {
        statusDiv.textContent = '';
        statusDiv.className = 'username-status';
        usernameInput.classList.remove('checking', 'available', 'taken', 'invalid', 'error');
        return;
    }

    if (username.length < 3) {
        statusDiv.textContent = '⚠️ Username must be at least 3 characters';
        statusDiv.className = 'username-status warning';
        usernameInput.classList.remove('checking', 'available', 'taken', 'invalid', 'error');
        return;
    }

    // Cancel previous request if still pending
    if (currentCheckRequest) {
        currentCheckRequest.abort();
    }

    // Create new AbortController for this request
    const controller = new AbortController();
    currentCheckRequest = controller;

    // Show checking status
    statusDiv.textContent = 'Checking availability...';
    statusDiv.className = 'username-status checking';
    usernameInput.classList.remove('available', 'taken', 'invalid', 'error');
    usernameInput.classList.add('checking');

    fetch(`/api/check-username/${encodeURIComponent(username)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        signal: controller.signal
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Clear checking state
        usernameInput.classList.remove('checking');

        if (data.available) {
            statusDiv.textContent = 'Username is available';
            statusDiv.className = 'username-status available';
            usernameInput.classList.add('available');
        } else {
            statusDiv.textContent = 'Username is already taken';
            statusDiv.className = 'username-status taken';
            usernameInput.classList.add('taken');
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            // Request was cancelled, ignore
            return;
        }

        console.error('Error checking username:', error);
        statusDiv.textContent = '⚠️ Error checking username';
        statusDiv.className = 'username-status error';
        usernameInput.classList.remove('checking', 'available', 'taken', 'invalid');
        usernameInput.classList.add('error');

        // Add error animation
        statusDiv.style.animation = 'none';
        setTimeout(() => {
            statusDiv.style.animation = 'pulseError 0.8s ease-in-out';
        }, 10);
    });
}

// Delete Account Functions
function showDeleteConfirmation() {
    console.log('showDeleteConfirmation called');
    const modal = document.getElementById('delete-modal');
    console.log('Modal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
        console.log('Modal displayed');
        document.getElementById('delete-email').focus();
        document.getElementById('confirm-delete-btn').disabled = true;

        // Store the user's email for validation
        const userEmail = '{{ auth()->user()->email }}';
        console.log('User email for validation:', userEmail);

        // Add event listeners for both inputs
        function validateInputs() {
            const emailInput = document.getElementById('delete-email').value.trim();
            const confirmationInput = document.getElementById('delete-confirmation').value.toUpperCase();
            const confirmBtn = document.getElementById('confirm-delete-btn');

            console.log('Validating inputs:', { emailInput, confirmationInput, userEmail });

            if (emailInput === userEmail && confirmationInput === 'DELETE') {
                confirmBtn.disabled = false;
                confirmBtn.style.background = '#e53e3e';
                console.log('Validation passed, button enabled');
            } else {
                confirmBtn.disabled = true;
                confirmBtn.style.background = '#a0aec0';
                console.log('Validation failed, button disabled');
            }
        }

        document.getElementById('delete-email').addEventListener('input', validateInputs);
        document.getElementById('delete-confirmation').addEventListener('input', validateInputs);
    } else {
        console.error('Delete modal not found!');
    }
}

function hideDeleteConfirmation() {
    document.getElementById('delete-modal').style.display = 'none';
    document.getElementById('delete-confirmation').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
}

function confirmDeleteAccount() {
    const userEmail = '{{ auth()->user()->email }}';
    const emailInput = document.getElementById('delete-email').value.trim();
    const confirmationText = document.getElementById('delete-confirmation').value.toUpperCase();

    if (emailInput !== userEmail) {
        alert('Please enter your correct email address to confirm account deletion.');
        return;
    }

    if (confirmationText !== 'DELETE') {
        alert('Please type "DELETE" to confirm account deletion.');
        return;
    }

    if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
        return;
    }

    // Disable the button to prevent multiple submissions
    const confirmBtn = document.getElementById('confirm-delete-btn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Deleting...';

    // Submit the delete request
    fetch('/profile/delete-account', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to home page or login page
            alert('Your account has been successfully deleted.');
            window.location.href = '/';
        } else {
            alert('Failed to delete account: ' + (data.message || 'Unknown error'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Yes, Delete My Account';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting your account. Please try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Yes, Delete My Account';
    });
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthIndicator = document.getElementById('password-strength');
    const strengthText = document.getElementById('password-strength-text');

    if (!strengthIndicator || !strengthText) {
        return;
    }

    let strength = 0;
    let feedback = [];

    if (password.length >= 8) {
        strength += 1;
    } else {
        feedback.push('At least 8 characters');
    }

    if (/[a-z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Lowercase letter');
    }

    if (/[A-Z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Uppercase letter');
    }

    if (/\d/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Number');
    }

    if (/[^A-Za-z0-9]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Special character');
    }

    let strengthClass = '';
    let strengthLabel = '';

    switch(strength) {
        case 0:
        case 1:
            strengthClass = 'weak';
            strengthLabel = 'Very Weak';
            break;
        case 2:
            strengthClass = 'weak';
            strengthLabel = 'Weak';
            break;
        case 3:
            strengthClass = 'medium';
            strengthLabel = 'Medium';
            break;
        case 4:
            strengthClass = 'strong';
            strengthLabel = 'Strong';
            break;
        case 5:
            strengthClass = 'very-strong';
            strengthLabel = 'Very Strong';
            break;
    }

    strengthIndicator.className = 'password-strength ' + strengthClass;
    strengthText.textContent = strengthLabel;

    if (feedback.length > 0 && strength < 4) {
        strengthText.textContent += ' - Add: ' + feedback.slice(0, 2).join(', ');
    }
}

// Password visibility toggle function
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Show password');
    }
}

// Close modal when clicking outside
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteConfirmation();
    }
});

// Toggle password change form visibility
function togglePasswordForm() {
    const formContainer = document.getElementById('password-form-container');
    const toggleButton = document.getElementById('toggle-password-form');

    if (formContainer.style.display === 'none' || formContainer.style.display === '') {
        formContainer.style.display = 'block';
        toggleButton.innerHTML = '<i class="fas fa-times"></i> Cancel Password Change';
        toggleButton.style.background = '#dc3545';

        // Initialize password strength checker when form is shown
        setTimeout(() => {
            initPasswordStrengthChecker();
        }, 100);
    } else {
        formContainer.style.display = 'none';
        toggleButton.innerHTML = '<i class="fas fa-key"></i> Change Password';
        toggleButton.style.background = 'var(--twitter-blue)';

        // Reset form when hiding
        const form = document.getElementById('password-change-form');
        if (form) {
            form.reset();
        }

        // Reset password strength indicators
        const strengthIndicator = document.getElementById('password-strength');
        const strengthText = document.getElementById('password-strength-text');
        if (strengthIndicator) {
            strengthIndicator.className = 'password-strength';
        }
        if (strengthText) {
            strengthText.textContent = '';
        }
    }
}

// Initialize password strength checker
function initPasswordStrengthChecker() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        // Remove existing listener to avoid duplicates
        passwordInput.removeEventListener('input', checkPasswordStrength);
        // Add the listener
        passwordInput.addEventListener('input', checkPasswordStrength);
        console.log('Password strength checker initialized');
    }
}

// Initialize password strength checker
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
});
</script>
