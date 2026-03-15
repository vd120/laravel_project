@extends('layouts.app')

@section('title', __('messages.home'))

@section('content')

<link rel="stylesheet" href="{{ asset('css/posts-index.css') }}">

<div class="feed-container">
    @if(session('verified'))
        <script>showToast('{{ __('messages.email_verified_success_toast') }}', 'success');</script>
    @endif

    @auth
    {{-- Stories - Always show section --}}
    <div class="stories-section">
        <div class="stories-header">
            <h3>{{ __('messages.stories') }}</h3>
            <a href="{{ route('stories.index') }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 13px;">
                <i class="fas fa-external-link-alt"></i> {{ __('messages.view_all_stories') }}
            </a>
        </div>
        <div class="stories-scroll" id="stories-scroll">
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
                    <div class="story-name">{{ __('messages.your_story') }}</div>
                </div>
            @else
            <div class="story-item create" onclick="window.location.href='{{ route('stories.create') }}'" style="position: relative;">
                <div class="story-avatar-wrapper">
                    <div class="story-avatar">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                        <div class="add-icon"><i class="fas fa-plus"></i></div>
                    </div>
                </div>
                <div class="story-name">{{ __('messages.create_story') }}</div>
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

    {{-- Create Post - Clean Professional Design --}}
    <div class="create-post">
        <div class="create-post-header">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="create-post-avatar">
            <span class="create-post-author">{{ auth()->user()->name }}</span>
        </div>
        <textarea id="post-content" placeholder="{{ __('messages.whats_on_your_mind') }}"></textarea>
        <div class="post-actions">
            <div class="post-actions-left">
                <label for="media" class="post-action-btn" style="cursor: pointer;">
                    <i class="fas fa-image"></i> <span>{{ __('messages.photo') }}</span>
                </label>
                <input type="file" id="media" accept="image/*,video/*" multiple style="display: none;" onchange="previewMedia(this)">
                <button type="button" class="privacy-btn" id="privacy-btn" onclick="togglePrivacy()">
                    <i class="fas fa-globe" id="privacy-icon"></i> <span id="privacy-text">{{ __('messages.public') }}</span>
                </button>
            </div>
            <button type="button" class="btn btn-primary" onclick="submitPost()">
                {{ __('messages.post') }}
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
                <h3>{{ __('messages.no_posts_yet') }}</h3>
                <p>{{ __('messages.be_first_to_post') }}</p>
            </div>
        @endforelse
    </div>

    @guest
    <div class="guest-cta">
        <h3>{{ __('messages.join_community') }}</h3>
        <p>{{ __('messages.sign_up_to_post') }}</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="{{ route('register') }}" class="btn btn-primary">{{ __('messages.sign_up') }}</a>
            <a href="{{ route('login') }}" class="btn">{{ __('messages.sign_in') }}</a>
        </div>
    </div>
    @endguest
</div>

<script>
// Disable browser scroll restoration to ensure stories section is always visible at top
// if ('scrollRestoration' in history) {
//     history.scrollRestoration = 'manual';
// }

// Scroll to top immediately when page loads
// window.addEventListener('load', function() {
//     window.scrollTo(0, 0);
// });

// Also scroll to top on DOMContentLoaded as backup
// document.addEventListener('DOMContentLoaded', function() {
//     window.scrollTo(0, 0);
// });

function viewStory(user, storySlug) { window.location.href = '/stories/' + user + '/' + storySlug; }
function viewStoryFromHome(user, storySlug) { window.location.href = '/stories/' + user + '/' + storySlug + '?from=home'; }

// Add story to the stories section when following a user
function addStoryToSection(user) {
    const storiesScroll = document.querySelector('.stories-scroll');
    if (!storiesScroll) return;
    
    // Check if story already exists
    const existingStory = storiesScroll.querySelector(`[data-username="${user.username}"]`);
    if (existingStory) return; // Already exists
    
    // Create story item
    const storyItem = document.createElement('div');
    storyItem.className = 'story-item';
    storyItem.setAttribute('data-username', user.username);
    storyItem.onclick = function() { viewStoryFromHome(user.username, user.storySlug); };
    
    const avatarUrl = user.avatarUrl || '/images/default-avatar.png';
    const hasAvatar = user.avatarUrl && user.avatarUrl !== '';
    
    storyItem.innerHTML = `
        <div class="story-avatar-wrapper">
            <div class="story-avatar">
                ${hasAvatar 
                    ? `<img src="${avatarUrl}" alt="${user.username}">` 
                    : `<div class="avatar-placeholder">${user.username.charAt(0).toUpperCase()}</div>`
                }
            </div>
        </div>
        <div class="story-name">${user.username}</div>
    `;
    
    // Add after "Your Story" or "Create Story" button
    const firstStory = storiesScroll.querySelector('.story-item:not([data-username])');
    if (firstStory && firstStory.nextElementSibling) {
        storiesScroll.insertBefore(storyItem, firstStory.nextElementSibling);
    } else {
        storiesScroll.appendChild(storyItem);
    }
}

// Remove story from section when unfollowing
function removeStoryFromSection(username) {
    const storyItem = document.querySelector(`.story-item[data-username="${username}"]`);
    if (storyItem) {
        storyItem.remove();
    }
}

function togglePrivacy() {
    const btn = document.getElementById('privacy-btn');
    const icon = document.getElementById('privacy-icon');
    const text = document.getElementById('privacy-text');
    const input = document.getElementById('is-private');

    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        icon.className = 'fas fa-globe';
        text.textContent = (window.chatTranslations && window.chatTranslations.public) || 'Public';
        input.value = '0';
    } else {
        btn.classList.add('active');
        icon.className = 'fas fa-lock';
        text.textContent = (window.chatTranslations && window.chatTranslations.private) || 'Private';
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
    clearAllBtn.innerHTML = '<i class="fas fa-trash-alt"></i> ' + ((window.chatTranslations && window.chatTranslations.clear_all) || 'Clear All');
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
    if (!confirm('{{ __('messages.remove_all_media_confirm') }}')) return;

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
        showToast('{{ __('messages.please_enter_content_or_media') }}', 'error');
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('messages.posting') }}';
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
            showToast('{{ __('messages.post_created_toast') }}', 'success');
            // Reload page to show new post
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || '{{ __('messages.failed_to_create_post') }}', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(() => {
        showToast('{{ __('messages.error_creating_post') }}', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Initialize - using global showToast from layout
</script>
@endsection
