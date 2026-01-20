<div class="post" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
<style>
.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    position: relative;
}

.post-delete-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
    z-index: 2;
}

.post-delete-btn:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.05);
}

.post-delete-btn:active {
    transform: scale(0.95);
}

.post .user {
    flex: 1;
    margin-right: 50px; /* Space for the delete button */
}

.content {
    margin: 16px 0;
    line-height: 1.6;
    font-size: 18px;
    color: var(--twitter-dark);
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    max-height: 500px;
    overflow-y: auto;
    padding-right: 8px;
}

.content::-webkit-scrollbar {
    width: 4px;
}

.content::-webkit-scrollbar-track {
    background: transparent;
}

.content::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 2px;
}

.content::-webkit-scrollbar-thumb:hover {
    background: var(--twitter-gray);
}

.read-more-btn {
    background: none;
    border: none;
    color: var(--twitter-blue);
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    padding: 4px 0;
    margin-top: 8px;
    text-decoration: underline;
    transition: color 0.2s ease;
}

.read-more-btn:hover {
    color: var(--twitter-dark);
}

.content-container {
    position: relative;
}

.content.truncated {
    max-height: 120px;
    overflow: hidden;
    position: relative;
}

.content.truncated::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(transparent, var(--card-bg));
    pointer-events: none;
}

.reaction-buttons .btn {
    margin-right: 30px !important;
}

.reaction-buttons .btn:last-child {
    margin-right: 0 !important;
}

/* Like Container - Like button and counter together */
.like-container {
    display: inline-flex;
    align-items: center;
    gap: 2px; /* Much smaller gap */
    margin-right: 30px !important;
}

.like-count {
    font-size: 14px;
    font-weight: 600;
    color: var(--twitter-gray);
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 2px 0px; /* Removed horizontal padding */
    border-radius: 12px;
    user-select: none;
    margin-left: 2px; /* Small margin instead */
}

.like-count:hover {
    color: var(--twitter-blue);
    background: rgba(29, 161, 242, 0.1);
    transform: scale(1.05);
}

.like-count:active {
    transform: scale(0.95);
}

/* Likers Button Styling */
.likers-btn {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
    border: 1px solid #17a2b8 !important;
    transition: all 0.3s ease !important;
    font-size: 12px !important;
    padding: 6px 10px !important;
    min-height: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.likers-btn:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3) !important;
}

.likers-btn:active {
    transform: translateY(0) !important;
}

.likers-btn i {
    font-size: 11px !important;
}

/* Likers Modal Styles */
.likers-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.likers-modal-content {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    animation: modalSlideUp 0.3s ease-out;
}

.likers-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.likers-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.likers-modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.likers-modal-close:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.likers-modal-body {
    padding: 0;
    max-height: 400px;
    overflow-y: auto;
}

.liker-item {
    display: flex;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.liker-item:hover {
    background: var(--hover-bg);
}

.liker-item:last-child {
    border-bottom: none;
}

.liker-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 16px;
    flex-shrink: 0;
}

.liker-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.liker-info {
    flex: 1;
    min-width: 0;
}

.liker-name {
    font-weight: 600;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 16px;
    display: block;
    margin-bottom: 2px;
    transition: color 0.2s ease;
}

.liker-name:hover {
    color: var(--twitter-dark);
}

.liker-bio {
    font-size: 14px;
    color: var(--twitter-gray);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.liker-actions {
    margin-left: 16px;
}

.liker-actions .btn {
    font-size: 12px !important;
    padding: 6px 12px !important;
    min-height: 32px !important;
}

.likers-loading,
.no-likers,
.likers-error {
    text-align: center;
    padding: 40px 24px;
    color: var(--twitter-gray);
}

.likers-loading i,
.no-likers i,
.likers-error i {
    font-size: 32px;
    margin-bottom: 12px;
    display: block;
}

.likers-loading span,
.no-likers span,
.likers-error span {
    font-size: 16px;
}

.no-likers i {
    color: var(--error-color);
}

.likers-error i {
    color: #dc3545;
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .likers-modal-content {
        width: 95%;
        max-width: none;
        margin: 20px;
        max-height: 90vh;
    }

    .likers-modal-header {
        padding: 16px 20px;
    }

    .likers-modal-header h3 {
        font-size: 16px;
    }

    .liker-item {
        padding: 12px 20px;
    }

    .liker-avatar {
        width: 40px;
        height: 40px;
        margin-right: 12px;
    }

    .liker-name {
        font-size: 15px;
    }

    .liker-bio {
        font-size: 13px;
    }
}
</style>
    <div class="post-header">
        <div class="user" style="display: flex; align-items: center; gap: 8px;">
            @if($post->user->profile && $post->user->profile->avatar)
                <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="{{ $post->user->name }}'s avatar" class="user-avatar-small" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--border-color);">
            @else
                <div class="user-avatar-small user-avatar-placeholder" style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--twitter-gray); font-weight: 600; border: 2px solid var(--border-color); font-size: 14px;">
                    {{ substr($post->user->name, 0, 1) }}
                </div>
            @endif
            <a href="{{ route('users.show', $post->user) }}" style="font-weight: 600; color: var(--twitter-dark); text-decoration: none;">{{ $post->user->name }}</a>
            @if(auth()->check() && $post->user_id !== auth()->id())
                <button type="button"
                        class="btn follow-btn {{ auth()->user()->isFollowing($post->user) ? 'following' : '' }}"
                        data-user-id="{{ $post->user->id }}"
                        data-username="{{ $post->user->name }}"
                        onclick="toggleFollow(this, {{ $post->user->id }})"
                        style="font-size: 11px; padding: 3px 8px; background: {{ auth()->user()->isFollowing($post->user) ? 'var(--success-color)' : 'var(--twitter-blue)' }}; margin-left: 8px;">
                    {{ auth()->user()->isFollowing($post->user) ? 'Following' : 'Follow' }}
                </button>
            @endif
            @if($post->is_private)
                <span class="privacy-badge private" style="font-size: 10px; padding: 1px 4px; margin-left: 8px; background-color: var(--error-color); color: white; border: 1px solid var(--error-color);">
                    <i class="fas fa-lock"></i> Private
                </span>
            @endif
            <small style="color: var(--twitter-gray); font-size: 12px; margin-left: 8px;">{{ $post->created_at->diffForHumans() }}</small>
        </div>
        @if($post->user_id === auth()->id())
            <button type="button" class="post-delete-btn" onclick="deletePost('{{ $post->slug }}', this)" title="Delete post">
                <i class="fas fa-trash"></i>
            </button>
        @endif
    </div>
    @if($post->content)
        <div class="content-container">
            <div class="content" id="content-{{ $post->id }}">
                {!! app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content) !!}
            </div>
            @if(strlen($post->content) > 500)
                <button type="button" class="read-more-btn" onclick="toggleReadMore({{ $post->id }})" id="read-more-btn-{{ $post->id }}">
                    Read more
                </button>
            @endif
        </div>
    @endif

    @if($post->media && $post->media->count() > 0)
        <div class="post-media" style="margin: 10px 0;">
            @if($post->media->count() === 1)
                @php $media = $post->media->first(); @endphp
                @if($media->media_type === 'image')
                    <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" loading="lazy" style="width: 100%; height: auto; border-radius: 12px; display: block;">
                @elseif($media->media_type === 'video')
                    <div class="video-container">
                        <video controls preload="metadata" poster="" loading="lazy" style="width: 100%; height: auto; display: block; border-radius: 12px;">
                            <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video-overlay" onclick="playVideo(this)" style="pointer-events: auto;">
                            <button class="play-button" type="button">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @else
                <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                    @foreach($post->media as $media)
                        <div class="media-item">
                            @if($media->media_type === 'image')
                                <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" loading="lazy" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            @elseif($media->media_type === 'video')
                                <video controls loading="lazy" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                                    <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="reaction-buttons">
        @if(auth()->check())
            <button type="button"
                    class="btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}"
                    onclick="toggleLike('{{ $post->slug }}', this)">
                <i class="fas fa-heart"></i> <span class="like-count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button"
                    class="btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}"
                    onclick="toggleSave('{{ $post->slug }}', this)">
                <i class="fas fa-bookmark"></i> <span class="save-text">{{ $post->savedBy(auth()->user()) ? 'Saved' : 'Save' }}</span>
            </button>
            <button onclick="copyPostLink('{{ $post->slug }}')" class="btn">
                <i class="fas fa-share"></i> Share
            </button>
            @if($post->likes->count() > 0)
                <button type="button"
                        class="btn likers-btn"
                        onclick="showLikers('{{ $post->slug }}')"
                        title="View who liked this post">
                    <i class="fas fa-users"></i> Likers
                </button>
            @endif
        @else
            <button type="button"
                    class="btn like-btn"
                    onclick="showLoginModal('like', 'Like posts to show your appreciation and support creators!')">
                <i class="fas fa-heart"></i> Like
            </button>
            @if($post->likes->count() > 0)
                <button type="button"
                        class="btn likers-btn"
                        onclick="showLoginModal('view_likers', 'Login to see who liked this post!')">
                    <i class="fas fa-thumbs-up"></i> {{ $post->likes->count() }}
                </button>
            @endif
            <button type="button"
                    class="btn save-btn"
                    onclick="showLoginModal('save', 'Save posts to keep your favorite content organized and easily accessible!')">
                <i class="fas fa-bookmark"></i> Save
            </button>
            <button onclick="copyPostLink('{{ $post->slug }}')" class="btn">
                <i class="fas fa-share"></i> Share
            </button>
        @endif
    </div>
    <hr>
    <h4>Comments</h4>
    @if(auth()->check() && !request()->routeIs('users.saved-posts'))
        <div class="comment-form-container">
            <textarea id="comment-content-{{ $post->id }}" placeholder="Add a comment..." maxlength="280" required></textarea>
            <button type="button" class="btn" style="font-size: 12px; padding: 5px 10px;" onclick="submitComment({{ $post->id }})">Comment</button>
        </div>
    @elseif(!auth()->check() && !request()->routeIs('users.saved-posts'))
        <div class="guest-comment-message" style="text-align: center; padding: 20px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 15px;">
            <i class="fas fa-comment-slash" style="font-size: 24px; color: var(--twitter-gray); margin-bottom: 10px;"></i>
            <p style="color: var(--twitter-gray); margin: 0; font-size: 14px;">Please <a href="{{ route('login') }}" style="color: var(--twitter-blue); text-decoration: none; font-weight: 500;">login</a> to comment on posts</p>
        </div>
    @endif
    <div class="comments-container" style="margin-top: 15px;">
@php
    $totalComments = $post->comments->count();
    $visibleComments = $post->comments->take(2);
    $hiddenComments = $post->comments->skip(2);
    $hasMoreComments = $hiddenComments->count() > 0;
@endphp

@foreach($visibleComments as $comment)
    @include('partials.comment', ['comment' => $comment])
@endforeach

@if($hasMoreComments) 
    <div class="comments-hidden" id="hidden-comments-{{ $post->id }}" style="display: none;">
        @foreach($post->comments->skip(2) as $comment)
            @include('partials.comment', ['comment' => $comment])
        @endforeach
    </div>

    <div class="show-more-comments-container" id="show-more-container-{{ $post->id }}">
        <button type="button" class="show-more-comments-btn" onclick="toggleComments({{ $post->id }}, true)">
            <i class="fas fa-chevron-down"></i>
            Show {{ max(0, $totalComments - 2) }} more comment{{ max(0, $totalComments - 2) > 1 ? 's' : '' }}
        </button>
    </div>

    <div class="hide-comments-container" id="hide-comments-container-{{ $post->id }}" style="display: none;">
        <button type="button" class="hide-comments-btn" onclick="toggleComments({{ $post->id }}, false)">
            <i class="fas fa-chevron-up"></i>
            Hide comments
        </button>
    </div>
@endif
    </div>
</div>

<script>
function toggleReadMore(postId) {
    const content = document.getElementById('content-' + postId);
    const button = document.getElementById('read-more-btn-' + postId);

    if (content.classList.contains('truncated')) {
        // Show full content
        content.classList.remove('truncated');
        button.textContent = 'Read less';
    } else {
        // Truncate content
        content.classList.add('truncated');
        button.textContent = 'Read more';
    }
}

// Initialize truncated state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Find all content elements with read more buttons
    const readMoreButtons = document.querySelectorAll('.read-more-btn');
    readMoreButtons.forEach(button => {
        const postId = button.id.replace('read-more-btn-', '');
        const content = document.getElementById('content-' + postId);
        if (content && !content.classList.contains('truncated')) {
            content.classList.add('truncated');
        }
    });
});

// Function to show users who liked a post
function showLikers(postId) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('likers-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'likers-modal';
        modal.className = 'likers-modal-overlay';
        modal.innerHTML = `
            <div class="likers-modal-content">
                <div class="likers-modal-header">
                    <h3>Users who liked this post</h3>
                    <button type="button" class="likers-modal-close" onclick="hideLikersModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="likers-modal-body" id="likers-list">
                    <div class="likers-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Loading...</span>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Show modal
    modal.style.display = 'flex';

    // Fetch likers data
    fetch(`/posts/${postId}/likers`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const likersList = document.getElementById('likers-list');

        if (data.success && data.likers.length > 0) {
            let html = '';
            data.likers.forEach(user => {
                html += `
                    <div class="liker-item">
                        <div class="liker-avatar">
                            ${user.avatar ?
                                `<img src="${user.avatar}" alt="${user.name}">` :
                                `<div class="avatar-placeholder">${user.name.charAt(0).toUpperCase()}</div>`
                            }
                        </div>
                        <div class="liker-info">
                            <a href="/users/${user.name}" class="liker-name">${user.name}</a>
                            ${user.bio ? `<div class="liker-bio">${user.bio}</div>` : ''}
                        </div>
                        <div class="liker-actions">
                            ${user.can_follow ?
                                `<button type="button" class="btn follow-btn ${user.is_following ? 'following' : ''}"
                                        onclick="toggleFollowFromModal(this, ${user.id}, '${user.name}')">
                                    ${user.is_following ? 'Following' : 'Follow'}
                                </button>` :
                                ''
                            }
                        </div>
                    </div>
                `;
            });
            likersList.innerHTML = html;
        } else {
            likersList.innerHTML = `
                <div class="no-likers">
                    <i class="fas fa-heart-broken"></i>
                    <span>No likes yet</span>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error fetching likers:', error);
        document.getElementById('likers-list').innerHTML = `
            <div class="likers-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Failed to load likers</span>
            </div>
        `;
    });
}

function hideLikersModal() {
    const modal = document.getElementById('likers-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function toggleFollowFromModal(button, userId, username) {
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/users/${userId}/follow`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.following) {
            button.textContent = 'Following';
            button.classList.add('following');
        } else {
            button.textContent = 'Follow';
            button.classList.remove('following');
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
        button.textContent = originalText;
    })
    .finally(() => {
        button.disabled = false;
    });
}
</script>