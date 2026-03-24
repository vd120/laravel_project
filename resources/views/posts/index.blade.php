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
        
        {{-- Load More Button --}}
        @if($posts->hasMorePages())
        <div class="load-more-container" id="load-more-container">
            <button id="load-more-btn" class="btn btn-primary" onclick="loadMorePosts()">
                <i class="fas fa-spinner fa-spin" id="load-more-spinner" style="display: none;"></i>
                {{ __('messages.load_more') }}
            </button>
        </div>
        @endif
        
        {{-- Loading Indicator for Infinite Scroll --}}
        <div id="infinite-scroll-loader" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: var(--primary-color);"></i>
        </div>
        
        {{-- No More Posts Message --}}
        <div id="no-more-posts" style="display: none; text-align: center; padding: 30px; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;"></i>
            <p>{{ __('messages.no_more_posts') }}</p>
        </div>
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

// Infinite Scroll and Load More Functionality
let currentPage = {{ $posts->currentPage() }};
let lastPage = {{ $posts->lastPage() }};
let isLoading = false;
let hasMorePosts = {{ $posts->hasMorePages() ? 'true' : 'false' }};

// Load more posts function
function loadMorePosts() {
    if (isLoading || !hasMorePosts || currentPage >= lastPage) return;
    
    isLoading = true;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const loadMoreContainer = document.getElementById('load-more-container');
    const infiniteScrollLoader = document.getElementById('infinite-scroll-loader');
    
    // Show loading state
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
        loadMoreSpinner.style.display = 'inline';
    }
    if (infiniteScrollLoader) {
        infiniteScrollLoader.style.display = 'block';
    }
    
    const nextPage = currentPage + 1;
    const perPage = 15;
    
    fetch(`{{ route('posts.load-more') }}?page=${nextPage}&per_page=${perPage}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.html) {
            // Insert new posts before the load more button
            const postsContainer = document.getElementById('posts-container');
            
            // Create temporary container to parse HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.html;
            
            // Insert each new post
            Array.from(tempDiv.children).forEach(child => {
                if (loadMoreContainer) {
                    loadMoreContainer.before(child);
                } else {
                    postsContainer.appendChild(child);
                }
            });
            
            // Update pagination state
            currentPage = data.next_page || currentPage + 1;
            hasMorePosts = data.has_more;
            
            // Hide or update load more button
            if (!hasMorePosts) {
                // Hide the load more button container completely
                if (loadMoreContainer) {
                    loadMoreContainer.style.display = 'none';
                }
                // Show "no more posts" message
                const noMorePosts = document.getElementById('no-more-posts');
                if (noMorePosts) {
                    noMorePosts.style.display = 'block';
                }
            }
            
            // Show success toast with translation
            showToast(window.postTranslations.new_posts_loaded, 'success');
        }
    })
    .catch(error => {
        console.error('Error loading more posts:', error);
        showToast(window.postTranslations.failed_to_load_posts, 'error');
    })
    .finally(() => {
        isLoading = false;
        if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
            loadMoreSpinner.style.display = 'none';
        }
        if (infiniteScrollLoader) {
            infiniteScrollLoader.style.display = 'none';
        }
    });
}

// Infinite scroll trigger
function handleInfiniteScroll() {
    if (isLoading || !hasMorePosts) return;
    
    const scrollPosition = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.offsetHeight;
    const threshold = 200; // pixels from bottom
    
    // Auto-load when user is near bottom
    if (scrollPosition >= documentHeight - threshold) {
        loadMorePosts();
    }
}

// Initialize infinite scroll listener
if (hasMorePosts) {
    window.addEventListener('scroll', function() {
        // Debounce scroll events
        if (window.scrollTimeout) {
            clearTimeout(window.scrollTimeout);
        }
        window.scrollTimeout = setTimeout(handleInfiniteScroll, 150);
    });
}

// Track followed users online status
let followedUsersOnlineState = {
    lastCheck: null,
    onlineUserIds: new Set(),
    pollInterval: null,
    pollingActive: false
};

// Poll for followed users' online status
function pollFollowedUsersOnline() {
    if (!document.visibilityState || document.visibilityState === 'visible') {
        fetch(`{{ route('followed-users.online') }}${followedUsersOnlineState.lastCheck ? `?last_check=${followedUsersOnlineState.lastCheck}` : ''}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update last check time
                followedUsersOnlineState.lastCheck = data.current_time;
                
                // Show toast for newly online users
                if (data.newly_online && data.newly_online.length > 0) {
                    data.newly_online.forEach(onlineUser => {
                        // Only show toast if we haven't already shown it for this user
                        if (!followedUsersOnlineState.onlineUserIds.has(onlineUser.id)) {
                            showFollowedUserOnlineToast(onlineUser);
                            followedUsersOnlineState.onlineUserIds.add(onlineUser.id);
                        }
                    });
                }
                
                // Track all currently online users
                data.online_users.forEach(onlineUser => {
                    followedUsersOnlineState.onlineUserIds.add(onlineUser.id);
                });
            }
        })
        .catch(error => {
            console.error('Error polling followed users online status:', error);
        });
    }
}

// Show toast when a followed user comes online
function showFollowedUserOnlineToast(user) {
    const avatarHtml = user.avatar_url 
        ? `<img src="${user.avatar_url}" alt="${user.username}" style="width: 32px; height: 32px; border-radius: 50%; margin-right: 10px;">`
        : `<div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; margin-right: 10px; color: white; font-weight: bold;">${user.username.charAt(0).toUpperCase()}</div>`;
    
    const message = `
        <div style="display: flex; align-items: center;">
            ${avatarHtml}
            <div>
                <strong>${user.username}</strong>
                <span style="display: block; font-size: 13px; opacity: 0.8;">${window.chatTranslations.is_now_online || 'is now online'}</span>
            </div>
        </div>
    `;
    
    showToast(message, 'success', 3000);
}

// Start polling for followed users' online status
function startFollowedUsersOnlinePolling() {
    if (followedUsersOnlineState.pollingActive) return;
    
    followedUsersOnlineState.pollingActive = true;
    
    // Initial poll
    pollFollowedUsersOnline();
    
    // Poll every 10 seconds
    followedUsersOnlineState.pollInterval = setInterval(pollFollowedUsersOnline, 10000);
}

// Stop polling when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        if (followedUsersOnlineState.pollInterval) {
            clearInterval(followedUsersOnlineState.pollInterval);
            followedUsersOnlineState.pollingActive = false;
        }
    } else {
        startFollowedUsersOnlinePolling();
    }
});

// Start polling on page load
startFollowedUsersOnlinePolling();

// Initialize - using global showToast from layout
</script>

<!-- Report Modal -->
<div id="report-modal" class="modal report-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-flag"></i> {{ __('messages.report_post') }}</h3>
            <button type="button" class="modal-close" onclick="closeReportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="report-form" method="POST">
            @csrf
            <div class="modal-body">
                <p class="report-description">{{ __('messages.report_description') }}</p>
                
                <div class="form-group">
                    <label for="report-reason">{{ __('messages.select_reason') }}:</label>
                    <select name="reason" id="report-reason" required onchange="toggleOtherReason()">
                        <option value="">{{ __('messages.choose_reason') }}</option>
                        <option value="spam">{{ __('messages.reason_spam') }}</option>
                        <option value="inappropriate">{{ __('messages.reason_inappropriate') }}</option>
                        <option value="harassment">{{ __('messages.reason_harassment') }}</option>
                        <option value="hate_speech">{{ __('messages.reason_hate_speech') }}</option>
                        <option value="violence">{{ __('messages.reason_violence') }}</option>
                        <option value="misinformation">{{ __('messages.reason_misinformation') }}</option>
                        <option value="copyright">{{ __('messages.reason_copyright') }}</option>
                        <option value="other">{{ __('messages.reason_other') }}</option>
                    </select>
                </div>

                <div class="form-group" id="other-reason-group" style="display: none;">
                    <label for="report-content">{{ __('messages.additional_details') }} ({{ __('messages.optional') }}):</label>
                    <textarea name="content" id="report-content" rows="4" maxlength="1000" placeholder="{{ __('messages.report_details_placeholder') }}"></textarea>
                    <small class="char-count"><span id="char-count">0</span>/1000</small>
                </div>

                <div class="report-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>{{ __('messages.report_warning') }}</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeReportModal()">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" id="submit-report-btn" disabled>
                    <i class="fas fa-flag"></i> {{ __('messages.submit_report') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
