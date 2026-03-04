@extends('layouts.app')

@section('title', 'Home')

@section('content')
<style>
.feed-container { max-width: 680px; margin: 0 auto; }

/* Stories Section */
.stories-section { margin-bottom: 24px; }
.stories-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.stories-header h3 { font-size: 18px; font-weight: 700; color: var(--text); }
.stories-scroll { display: flex; gap: 16px; overflow-x: auto; padding: 8px 0; scrollbar-width: none; }
.stories-scroll::-webkit-scrollbar { display: none; }

.story-item { flex-shrink: 0; text-align: center; cursor: pointer; }
.story-avatar-wrapper { 
    width: 72px; height: 72px; border-radius: 50%; 
    padding: 3px; background: linear-gradient(135deg, var(--primary), var(--secondary));
    margin-bottom: 8px;
}
.story-avatar { 
    width: 100%; height: 100%; border-radius: 50%; border: 3px solid var(--bg); 
    overflow: hidden; background: var(--surface);
}
.story-avatar img { width: 100%; height: 100%; object-fit: cover; }
.story-avatar .avatar-placeholder { 
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 24px; color: var(--text);
}
.story-item.create .story-avatar-wrapper { background: var(--border); }
.story-item.create .add-icon { 
    position: absolute; bottom: 0; right: 0; width: 24px; height: 24px;
    background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 12px; border: 3px solid var(--bg);
}
.story-name { font-size: 12px; color: var(--text-muted); max-width: 72px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Create Post - Clean Professional Design */
.create-post {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    margin-bottom: 24px;
}
.create-post-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.create-post-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
.create-post-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}
.create-post-author {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}
.create-post textarea {
    width: 100%;
    min-height: 80px;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--bg);
    color: var(--text);
    font-size: 15px;
    resize: vertical;
    transition: border-color 0.2s ease;
}
.create-post textarea:focus {
    outline: none;
    border-color: var(--primary);
}
.create-post textarea::placeholder {
    color: var(--text-muted);
}

.post-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 12px;
}
.post-actions-left {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.post-action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: none;
    border-radius: var(--radius);
    background: transparent;
    color: var(--text-muted);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.post-action-btn:hover {
    color: var(--primary);
    background: rgba(139, 92, 246, 0.1);
}
.post-action-btn i {
    font-size: 16px;
}
.privacy-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-muted);
}
.privacy-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.privacy-btn.active {
    background: rgba(139, 92, 246, 0.1);
    border-color: var(--primary);
    color: var(--primary);
}
.privacy-btn i {
    font-size: 14px;
}
.btn-primary {
    background: var(--primary);
    border: none;
    padding: 10px 24px;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 14px;
    color: white;
    cursor: pointer;
    transition: background 0.2s ease;
}
.btn-primary:hover {
    background: var(--primary-hover);
}

/* Posts Feed */
.posts-feed { display: flex; flex-direction: column; gap: 20px; }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; display: block; opacity: 0.5; }
.empty-state h3 { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.empty-state p { color: var(--text-muted); margin-bottom: 24px; }

/* Guest CTA */
.guest-cta { text-align: center; padding: 40px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); margin: 20px 0; }
.guest-cta h3 { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
.guest-cta p { color: var(--text-muted); margin-bottom: 20px; }

@media (max-width: 640px) {
    .feed-container { padding: 0 12px; }
    .create-post { padding: 16px; }
    .story-avatar-wrapper { width: 60px; height: 60px; }
    .story-name { max-width: 60px; }
    
    /* Larger remove button for mobile */
    #media-previews button {
        width: 28px !important;
        height: 28px !important;
        top: 6px !important;
        right: 6px !important;
    }
    
    /* Mobile clear all button - full width */
    #clear-all-media-btn {
        width: 100%;
        justify-content: center;
        padding: 12px 16px;
        font-size: 14px;
        margin-bottom: 12px;
    }
    
    /* Adjust media preview grid for mobile */
    #media-previews {
        gap: 6px !important;
    }
    
    #media-previews > div {
        width: 80px !important;
        height: 80px !important;
    }
}

/* Light theme button fix */
body.light-theme .btn-primary {
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}
body.light-theme .btn-primary:hover {
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
}
</style>

<div class="feed-container">
    @if(session('verified'))
        <script>showToast('Email verified successfully! Welcome to the platform.', 'success');</script>
    @endif

    @auth
    {{-- Stories --}}
    @if($followedUsersWithStories->count() > 0 || $myStories->count() > 0)
    <div class="stories-section">
        <div class="stories-header">
            <h3>Stories</h3>
            <a href="{{ route('stories.index') }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 13px;">
                <i class="fas fa-external-link-alt"></i> View All
            </a>
        </div>
        <div class="stories-scroll">
            @if($myStories->count() > 0)
                @php
                $latestMyStory = $myStories->sortByDesc('created_at')->first();
                @endphp
                <div class="story-item" onclick="viewStoryFromHome('{{ auth()->user()->username }}', '{{ $latestMyStory->slug }}')">
                    <div class="story-avatar-wrapper">
                        <div class="story-avatar">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}">
                        </div>
                    </div>
                    <div class="story-name">Your story</div>
                </div>
            @else
            <div class="story-item create" onclick="window.location.href='{{ route('stories.create') }}'" style="position: relative;">
                <div class="story-avatar-wrapper">
                    <div class="story-avatar">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                        <div class="add-icon"><i class="fas fa-plus"></i></div>
                    </div>
                </div>
                <div class="story-name">Create</div>
            </div>
            @endif

            @foreach($followedUsersWithStories as $user)
                @php
                $latestStory = $user->activeStories->sortByDesc('created_at')->first();
                @endphp
                @if($latestStory)
                <div class="story-item" onclick="viewStoryFromHome('{{ $user->username }}', '{{ $latestStory->slug }}')">
                    <div class="story-avatar-wrapper">
                        <div class="story-avatar">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}">
                        </div>
                    </div>
                    <div class="story-name">{{ $user->username }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Create Post - Clean Professional Design --}}
    <div class="create-post">
        <div class="create-post-header">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="create-post-avatar">
            <span class="create-post-author">{{ auth()->user()->name }}</span>
        </div>
        <textarea id="post-content" placeholder="What's on your mind?"></textarea>
        <div class="post-actions">
            <div class="post-actions-left">
                <label for="media" class="post-action-btn" style="cursor: pointer;">
                    <i class="fas fa-image"></i> <span>Photo</span>
                </label>
                <input type="file" id="media" accept="image/*,video/*" multiple style="display: none;" onchange="previewMedia(this)">
                <button type="button" class="privacy-btn" id="privacy-btn" onclick="togglePrivacy()">
                    <i class="fas fa-globe" id="privacy-icon"></i> <span id="privacy-text">Public</span>
                </button>
            </div>
            <button type="button" class="btn btn-primary" onclick="submitPost()">
                Post
            </button>
        </div>
        <input type="hidden" id="is-private" value="0">
        <div id="media-preview-container" style="display: none; margin-top: 16px;">
            <div id="media-previews" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
        </div>
    </div>
    @endauth

    {{-- Posts Feed --}}
    <div class="posts-feed" id="posts-container">
        @forelse($posts as $post)
            @include('partials.post', ['post' => $post])
        @empty
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>No posts yet</h3>
                <p>Be the first to share something!</p>
            </div>
        @endforelse
    </div>

    @guest
    <div class="guest-cta">
        <h3>Join the Community</h3>
        <p>Sign up to post, like, comment, and connect with others.</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
            <a href="{{ route('login') }}" class="btn">Sign In</a>
        </div>
    </div>
    @endguest
</div>

<script>
function viewStory(user, storySlug) { window.location.href = '/stories/' + user + '/' + storySlug; }
function viewStoryFromHome(user, storySlug) { window.location.href = '/stories/' + user + '/' + storySlug + '?from=home'; }

function togglePrivacy() {
    const btn = document.getElementById('privacy-btn');
    const icon = document.getElementById('privacy-icon');
    const text = document.getElementById('privacy-text');
    const input = document.getElementById('is-private');
    
    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        icon.className = 'fas fa-globe';
        text.textContent = 'Public';
        input.value = '0';
    } else {
        btn.classList.add('active');
        icon.className = 'fas fa-lock';
        text.textContent = 'Private';
        input.value = '1';
    }
}

// Store uploaded files in an array for management
let uploadedFiles = [];

function previewMedia(input) {
    if (!input.files || input.files.length === 0) return;
    
    // Add new files to the array
    Array.from(input.files).forEach(file => {
        uploadedFiles.push(file);
    });
    
    renderMediaPreviews();
}

function renderMediaPreviews() {
    const container = document.getElementById('media-preview-container');
    const previews = document.getElementById('media-previews');
    previews.innerHTML = '';
    
    if (uploadedFiles.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    // Add clear all button
    const clearAllBtn = document.createElement('button');
    clearAllBtn.type = 'button';
    clearAllBtn.id = 'clear-all-media-btn';
    clearAllBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear All';
    clearAllBtn.onclick = clearAllMedia;
    clearAllBtn.style.cssText = `
        padding: 8px 16px; background: rgba(220,38,38,0.1); color: #dc2626; 
        border: 1px solid rgba(220,38,38,0.3); border-radius: 8px; cursor: pointer;
        font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px;
        transition: all 0.2s; margin-bottom: 8px;
    `;
    clearAllBtn.onmouseover = function() { this.style.background = 'rgba(220,38,38,0.2)'; };
    clearAllBtn.onmouseout = function() { this.style.background = 'rgba(220,38,38,0.1)'; };
    previews.appendChild(clearAllBtn);
    
    uploadedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.style.cssText = 'position: relative; width: 100px; height: 100px; border-radius: 12px; overflow: hidden; flex-shrink: 0;';
            
            let mediaContent = '';
            if (file.type.startsWith('image/')) {
                mediaContent = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                mediaContent = `<video src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;"></video>`;
            }
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.onclick = function() { removeMedia(index); };
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.style.cssText = `
                position: absolute; top: 4px; right: 4px; width: 24px; height: 24px; 
                background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 50%; 
                cursor: pointer; display: flex; align-items: center; justify-content: center;
                font-size: 12px; transition: all 0.2s; z-index: 10; -webkit-tap-highlight-color: transparent;
            `;
            
            // Touch and mouse events
            removeBtn.onmouseover = function() { this.style.background = 'rgba(220,38,38,0.9)'; this.style.transform = 'scale(1.1)'; };
            removeBtn.onmouseout = function() { this.style.background = 'rgba(0,0,0,0.7)'; this.style.transform = 'scale(1)'; };
            removeBtn.ontouchstart = function() { this.style.background = 'rgba(220,38,38,0.9)'; this.style.transform = 'scale(1.1)'; };
            removeBtn.ontouchend = function() { this.style.background = 'rgba(0,0,0,0.7)'; this.style.transform = 'scale(1)'; };
            
            div.appendChild(removeBtn);
            
            // Add media content
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = mediaContent;
            div.appendChild(tempDiv.firstElementChild);
            
            previews.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function clearAllMedia() {
    if (uploadedFiles.length === 0) return;
    if (!confirm('Remove all uploaded media?')) return;
    
    uploadedFiles = [];
    updateFileInput();
    renderMediaPreviews();
}

function removeMedia(index) {
    // Remove the file at the specified index
    uploadedFiles.splice(index, 1);
    
    // Update the file input with remaining files
    updateFileInput();
    
    // Re-render previews
    renderMediaPreviews();
}

function updateFileInput() {
    const fileInput = document.getElementById('media');
    const dataTransfer = new DataTransfer();
    
    uploadedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    
    fileInput.files = dataTransfer.files;
}

function submitPost() {
    const content = document.getElementById('post-content').value.trim();
    const isPrivate = document.getElementById('is-private').value;
    const mediaFiles = document.getElementById('media').files;

    if (!content && mediaFiles.length === 0) {
        showToast('Please enter content or add media', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('content', content);
    formData.append('is_private', isPrivate);
    Array.from(mediaFiles).forEach((file, i) => formData.append(`media[${i}]`, file));

    // Show loading state
    const submitBtn = document.querySelector('button[onclick="submitPost()"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
    submitBtn.disabled = true;

    fetch('/posts', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Post created!', 'success');
            // Reload page to show new post
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Failed to create post', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Error creating post', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Initialize - using global showToast from layout
</script>
@endsection
