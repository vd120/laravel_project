<div class="post-card" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-author">
            <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->username }}" class="author-avatar">
            <div class="author-info">
                <a href="{{ route('users.show', $post->user) }}" class="author-name">{{ $post->user->username }}</a>
                @auth
                    @if(auth()->id() !== $post->user->id)
                        @php $isFollowing = auth()->user()->isFollowing($post->user); @endphp
                        <button type="button" class="quick-follow-btn {{ $isFollowing ? 'following' : '' }}" onclick="quickFollow('{{ $post->user->username }}', this)" data-following="{{ $isFollowing ? 'true' : 'false' }}">
                            <span>{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                        </button>
                    @endif
                @endauth
                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
            </div>
            @if($post->is_private)
                <span class="privacy-badge"><i class="fas fa-lock"></i> Private</span>
            @endif
        </div>
        @if($post->user_id === auth()->id())
            <button type="button" class="delete-post-btn" onclick="deletePost('{{ $post->slug }}', this)" title="Delete post">
                <i class="fas fa-trash"></i>
            </button>
        @endif
    </div>

    @if($post->content)
        <div class="post-content">
            @php
                $content = app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content);
                $contentLength = strlen(strip_tags($post->content));
                $shouldTruncate = $contentLength > 300;
                $truncatedContent = $shouldTruncate ? substr(strip_tags($post->content), 0, 300) . '...' : $post->content;
                if ($shouldTruncate) {
                    $truncatedContent = app(\App\Services\MentionService::class)->convertMentionsToLinks($truncatedContent);
                }
            @endphp
            <p class="post-text {{ $shouldTruncate ? 'truncated' : '' }}" 
               data-full-content="{{ htmlspecialchars($content, ENT_QUOTES, 'UTF-8') }}"
               data-truncated-content="{{ htmlspecialchars($truncatedContent, ENT_QUOTES, 'UTF-8') }}">
                {!! $shouldTruncate ? $truncatedContent : $content !!}
            </p>
            @if($shouldTruncate)
                <button type="button" class="show-more-btn" onclick="togglePostContent(this)">
                    <span class="show-more-text">Show more</span>
                    <span class="show-less-text" style="display: none;">Show less</span>
                </button>
            @endif
        </div>
    @endif

    @if($post->media && $post->media->count() > 0)
        @php
            $mediaCount = $post->media->count();
            $remainingCount = $mediaCount - 4;
            $mediaData = $post->media->map(function($m, $index) {
                return [
                    'index' => $index,
                    'type' => $m->media_type,
                    'src' => asset('storage/' . $m->media_path)
                ];
            });
        @endphp
        <div class="post-media fb-grid fb-grid-{{ $mediaCount }}" 
             data-post-id="{{ $post->id }}" 
             data-media-count="{{ $mediaCount }}"
             data-media-list="{{ json_encode($mediaData) }}">
            @foreach($post->media as $index => $media)
                @if($index < 4)
                    @if($media->media_type === 'image')
                        <div class="media-item {{ $index === 3 && $remainingCount > 0 ? 'has-more' : '' }}" onclick="openMediaModal('{{ $post->id }}', '{{ $index }}')">
                            <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" loading="lazy" data-media-index="{{ $index }}">
                            @if($index === 3 && $remainingCount > 0)
                                <div class="more-overlay">
                                    <span class="more-count">+{{ $remainingCount }}</span>
                                </div>
                            @endif
                        </div>
                    @elseif($media->media_type === 'video')
                        <div class="media-item">
                            <video controls preload="metadata">
                                <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                            </video>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

    <div class="post-actions">
        @if(auth()->check())
            <button type="button" class="action-btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}" onclick="toggleLike('{{ $post->slug }}', this)">
                <i class="fas fa-heart"></i>
                <span class="count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button" class="action-btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}" onclick="toggleSave('{{ $post->slug }}', this)">
                <i class="fas fa-bookmark"></i>
                <span>{{ $post->savedBy(auth()->user()) ? 'Saved' : 'Save' }}</span>
            </button>
        @else
            <button type="button" class="action-btn" onclick="showLoginModal('like', 'Like posts to show your support!')">
                <i class="fas fa-heart"></i>
                <span>{{ $post->likes->count() }}</span>
            </button>
            <button type="button" class="action-btn" onclick="showLoginModal('save', 'Save posts to access later!')">
                <i class="fas fa-bookmark"></i>
                <span>Save</span>
            </button>
        @endif
        <button type="button" class="action-btn" onclick="copyPostLink('{{ $post->slug }}')">
            <i class="fas fa-share"></i>
            <span>Share</span>
        </button>
        <button type="button" class="action-btn likers-btn" onclick="showLikers('{{ $post->slug }}')">
            <i class="fas fa-users"></i>
            <span class="likers-count">{{ $post->likes->count() }}</span>
        </button>
    </div>

    <div class="post-comments-section">
        <h4>Comments ({{ $post->comments->count() }})</h4>
        
        @if(auth()->check())
            <div class="comment-form">
                <textarea id="comment-content-{{ $post->slug }}" placeholder="Write a comment..." maxlength="5000"></textarea>
                <button type="button" onclick="submitComment('{{ $post->slug }}', {{ $post->id }})">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        @else
            <div class="guest-message">
                <p><a href="{{ route('login') }}">Login</a> to comment</p>
            </div>
        @endif

        <div class="comments-list">
            @php
                $sortedComments = $post->comments->sortByDesc('created_at');
                $visibleComments = $sortedComments->take(2);
                $hasMore = $sortedComments->count() > 2;
            @endphp
            
            @foreach($visibleComments as $comment)
                @include('partials.comment', ['comment' => $comment])
            @endforeach
            
            @if($hasMore)
                <div class="show-more-comments">
                    <button type="button" onclick="toggleComments({{ $post->id }}, true)">
                        Show {{ $sortedComments->count() - 2 }} more comments
                    </button>
                </div>
                <div class="hidden-comments" id="hidden-comments-{{ $post->id }}" style="display: none;">
                    @foreach($sortedComments->skip(2) as $comment)
                        @include('partials.comment', ['comment' => $comment])
                    @endforeach
                    <button type="button" class="hide-comments" onclick="toggleComments({{ $post->id }}, false)">
                        Hide comments
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.post-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: var(--shadow);
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.post-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.author-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #8B5CF6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}

.author-info {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.quick-follow-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    border-radius: var(--radius-full);
    background: var(--primary);
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    width: fit-content;
}

.quick-follow-btn:hover {
    background: var(--primary-hover);
    opacity: 0.9;
}

.quick-follow-btn.following {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
}

.quick-follow-btn.following:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: #ef4444;
    color: #ef4444;
}

.author-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    font-size: 15px;
    line-height: 1.2;
}

.author-name:hover {
    text-decoration: underline;
}

.post-time {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.2;
}

.privacy-badge {
    background: #ef4444;
    color: white;
    font-size: 10px;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    margin-left: 6px;
    font-weight: 600;
}

.delete-post-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.delete-post-btn:hover {
    background: var(--surface-hover);
    color: #ef4444;
}

.post-content {
    margin-bottom: 12px;
}

.post-content p {
    margin: 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--text);
    word-wrap: break-word;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Noto Sans Arabic', 'Tahoma', 'Arial', sans-serif;
    unicode-bidi: embed;
}

/* Arabic and RTL text support */
.post-content p:lang(ar),
.post-content p[dir="rtl"],
.post-content p[lang="ar"] {
    direction: rtl;
    text-align: right;
}

.post-content a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

/* Show More/Less Button */
.show-more-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 0;
    margin-top: 4px;
    transition: all 0.2s ease;
}

.show-more-btn:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.post-text.truncated {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-text.expanded {
    display: block;
}

.post-content a:hover {
    text-decoration: underline;
}

/* Facebook-style Media Grid */
.post-media {
    margin: 0 -16px 0 -16px;
    display: grid;
    overflow: hidden;
}

/* Remove gap for Facebook-style seamless grid */
.fb-grid {
    gap: 2px;
}

/* Single image - full width, natural aspect ratio preserved */
.fb-grid-1 {
    grid-template-columns: 1fr;
}

.fb-grid-1 .media-item {
    max-height: 600px;
}

.fb-grid-1 .media-item img,
.fb-grid-1 .media-item video {
    width: 100%;
    max-height: 600px;
    object-fit: contain;
    background: var(--surface-hover);
}

/* Two images - side by side, equal height */
.fb-grid-2 {
    grid-template-columns: 1fr 1fr;
}

.fb-grid-2 .media-item {
    aspect-ratio: 1 / 1;
}

.fb-grid-2 .media-item img,
.fb-grid-2 .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Three images - first image takes full left, two stacked on right */
.fb-grid-3 {
    grid-template-columns: 2fr 1fr;
    grid-template-rows: 1fr 1fr;
}

.fb-grid-3 .media-item:first-child {
    grid-row: span 2;
}

.fb-grid-3 .media-item img,
.fb-grid-3 .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Four images - 2x2 grid */
.fb-grid-4,
.fb-grid-5,
.fb-grid-6,
.fb-grid-7,
.fb-grid-8,
.fb-grid-9,
.fb-grid-10,
.fb-grid-11,
.fb-grid-12,
.fb-grid-13,
.fb-grid-14,
.fb-grid-15,
.fb-grid-16,
.fb-grid-17,
.fb-grid-18,
.fb-grid-19,
.fb-grid-20 {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
}

.fb-grid-4 .media-item img,
.fb-grid-4 .media-item video,
.fb-grid-5 .media-item img,
.fb-grid-5 .media-item video,
.fb-grid-6 .media-item img,
.fb-grid-6 .media-item video,
.fb-grid-7 .media-item img,
.fb-grid-7 .media-item video,
.fb-grid-8 .media-item img,
.fb-grid-8 .media-item video,
.fb-grid-9 .media-item img,
.fb-grid-9 .media-item video,
.fb-grid-10 .media-item img,
.fb-grid-10 .media-item video,
.fb-grid-11 .media-item img,
.fb-grid-11 .media-item video,
.fb-grid-12 .media-item img,
.fb-grid-12 .media-item video,
.fb-grid-13 .media-item img,
.fb-grid-13 .media-item video,
.fb-grid-14 .media-item img,
.fb-grid-14 .media-item video,
.fb-grid-15 .media-item img,
.fb-grid-15 .media-item video,
.fb-grid-16 .media-item img,
.fb-grid-16 .media-item video,
.fb-grid-17 .media-item img,
.fb-grid-17 .media-item video,
.fb-grid-18 .media-item img,
.fb-grid-18 .media-item video,
.fb-grid-19 .media-item img,
.fb-grid-19 .media-item video,
.fb-grid-20 .media-item img,
.fb-grid-20 .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Media item styling */
.media-item {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    min-height: 200px;
    background: var(--surface-hover);
}

.media-item:hover {
    opacity: 0.95;
}

.media-item img,
.media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.media-item video {
    background: #000;
}

/* Facebook-style +N overlay - positioned on last visible image */
.media-item.has-more {
    position: relative;
}

.media-item.has-more .more-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.media-item.has-more .more-count {
    color: white;
    font-size: 32px;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Video indicator */
.media-item.video-indicator::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    z-index: 2;
}

.media-item.video-indicator::after {
    content: '\25B6';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 20px;
    z-index: 2;
}

/* Post actions - Facebook style */
.post-actions {
    display: flex;
    gap: 4px;
    padding: 8px 0 0 0;
    margin-top: 4px;
    border-top: none;
    border-bottom: none;
    margin-bottom: 8px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: none;
    border-radius: var(--radius);
    background: transparent;
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
    flex: 1;
    justify-content: center;
}

.action-btn:hover {
    background: var(--surface-hover);
}

.action-btn.liked {
    color: #ef4444;
}

.action-btn.saved {
    color: var(--primary);
}

.action-btn.likers-btn {
    flex: unset;
    margin-left: auto;
    background: transparent;
}

.post-comments-section {
    border-top: 1px solid var(--border);
    padding-top: 12px;
}

.post-comments-section h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.comment-form {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    align-items: center;
}

.comment-form textarea {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-full);
    background: var(--surface);
    color: var(--text);
    font-size: 14px;
    resize: none;
    height: 40px;
    line-height: 20px;
    transition: all 0.2s ease;
}

.comment-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--surface-hover);
}

.comment-form textarea::placeholder {
    color: var(--text-muted);
}

.comment-form button {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.comment-form button:hover {
    background: var(--primary-hover);
    transform: scale(1.05);
}

.comment-form button:active {
    transform: scale(0.98);
}

.guest-message {
    text-align: center;
    padding: 16px;
    background: var(--surface);
    border-radius: var(--radius);
    margin-bottom: 12px;
    font-size: 14px;
    color: var(--text-muted);
}

.guest-message a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.guest-message a:hover {
    text-decoration: underline;
}

.show-more-comments,
.hide-comments {
    text-align: left;
    padding: 8px 0;
}

.show-more-comments button,
.hide-comments {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    transition: all 0.2s ease;
}

.show-more-comments button:hover,
.hide-comments:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .post-card {
        padding: 12px;
        border-radius: 0;
        border-left: none;
        border-right: none;
        margin-bottom: 8px;
    }

    .post-media {
        margin: 8px -12px -4px -12px;
    }

    .fb-grid-1 .media-item {
        max-height: 500px;
    }

    .fb-grid-1 .media-item img,
    .fb-grid-1 .media-item video {
        max-height: 500px;
    }

    .post-actions {
        margin-top: 4px;
    }

    .action-btn {
        padding: 8px 6px;
        font-size: 13px;
    }

    .action-btn span:not(.count):not(.likers-count) {
        display: none;
    }

    .action-btn .count,
    .action-btn .likers-count {
        display: inline;
    }

    .action-btn i {
        font-size: 18px;
    }

    /* Disable hover effects on mobile */
    .quick-follow-btn:hover { background: var(--primary); opacity: 1; }
    .quick-follow-btn.following:hover { background: transparent; border-color: var(--border); color: var(--text); }
    .author-name:hover { text-decoration: none; }
    .delete-post-btn:hover { background: transparent; }
    .post-content a:hover { text-decoration: none; }
    .media-item:hover { opacity: 1; }
    .action-btn:hover { background: transparent; }
    .comment-form button:hover { transform: none; }
    .show-more-comments button:hover, .hide-comments:hover { color: var(--text-muted); }
    .comment-action-btn:hover { background: transparent; color: var(--text-muted); }
    .delete-comment-btn:hover { opacity: 0.7; background: transparent; }
    .show-more-replies button:hover, .hide-replies:hover, .show-replies-btn:hover { text-decoration: none; }
    .comment-name:hover { color: var(--text); }
}

/* Disable ALL hover effects on touch devices */
@media (hover: none) {
    .quick-follow-btn:hover { background: var(--primary); opacity: 1; }
    .quick-follow-btn.following:hover { background: transparent; border-color: var(--border); color: var(--text); }
    .author-name:hover { text-decoration: none; }
    .delete-post-btn:hover { background: transparent; }
    .post-content a:hover { text-decoration: none; }
    .media-item:hover { opacity: 1; }
    .action-btn:hover { background: transparent; }
    .comment-form button:hover { transform: none; }
    .show-more-comments button:hover, .hide-comments:hover { color: var(--text-muted); }
    .comment-action-btn:hover { background: transparent; color: var(--text-muted); }
    .delete-comment-btn:hover { opacity: 0.7; background: transparent; }
    .show-more-replies button:hover, .hide-replies:hover, .show-replies-btn:hover { text-decoration: none; }
    .comment-name:hover { color: var(--text); }
    .media-modal-nav:hover { background: rgba(0, 0, 0, 0.5); }
}

/* Media Modal Styles */
.media-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.media-modal.active {
    display: flex;
}

.media-modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.media-modal-content img,
.media-modal-content video {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.media-modal-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    padding: 8px 12px;
    z-index: 10000;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.media-modal-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    font-size: 24px;
    padding: 16px 12px;
    cursor: pointer;
    border-radius: 8px;
    z-index: 10000;
    min-width: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.media-modal-nav:hover {
    background: rgba(0, 0, 0, 0.8);
}

.media-modal-prev {
    left: 10px;
}

.media-modal-next {
    right: 10px;
}

.media-modal-counter {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 14px;
    font-weight: 600;
    background: rgba(0, 0, 0, 0.5);
    padding: 6px 12px;
    border-radius: 16px;
}

/* Mobile Navigation Buttons - Always visible on mobile */
@media (max-width: 768px) {
    .media-modal-nav {
        background: rgba(0, 0, 0, 0.7);
        font-size: 28px;
        padding: 20px 10px;
        min-width: 60px;
    }
    
    .media-modal-prev {
        left: 5px;
    }
    
    .media-modal-next {
        right: 5px;
    }
    
    .media-modal-close {
        top: 10px;
        right: 10px;
    }
    
    .media-modal-counter {
        bottom: 20px;
    }
}

/* Larger images on desktop */
@media (min-width: 769px) {
    .fb-grid-1 .media-item {
        max-height: 800px;
    }

    .fb-grid-1 .media-item img,
    .fb-grid-1 .media-item video {
        max-height: 800px;
    }

    .fb-grid-2 .media-item {
        aspect-ratio: 4 / 3;
    }

    .fb-grid-3 .media-item {
        min-height: 300px;
    }

    .fb-grid-4 .media-item,
    .fb-grid-5 .media-item,
    .fb-grid-6 .media-item,
    .fb-grid-7 .media-item,
    .fb-grid-8 .media-item,
    .fb-grid-9 .media-item,
    .fb-grid-10 .media-item {
        min-height: 250px;
    }
}

/* Toast animations */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Reply form styles for dynamic comments */
.reply-form {
    padding-left: 46px;
    margin-bottom: 12px;
}

.reply-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.reply-input-wrapper textarea {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-full);
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    resize: none;
    height: 36px;
    line-height: 20px;
}

.reply-input-wrapper textarea:focus {
    outline: none;
    border-color: var(--primary);
}

.reply-input-wrapper button {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    cursor: pointer;
    font-size: 12px;
    transition: transform 0.2s ease;
}

.reply-input-wrapper button:hover {
    transform: scale(1.05);
}

.cancel-reply {
    margin-top: 8px;
    margin-left: 40px;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
}

.cancel-reply:hover {
    color: var(--text);
}

/* Comment item styles - needed for dynamically added comments */
.comment-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-item.nested {
    margin-left: 20px;
    padding-left: 16px;
    border-left: 2px solid var(--border);
}

.comment-item.level-1 {
    margin-left: 20px;
}

.comment-item.level-2 {
    margin-left: 40px;
}

.comment-item.level-3 {
    margin-left: 60px;
}

.comment-item.level-4 {
    margin-left: 80px;
}

.comment-item.level-5 {
    margin-left: 100px;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.comment-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.comment-avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #8B5CF6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.comment-author-info {
    display: flex;
    flex-direction: column;
}

.comment-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    font-size: 14px;
}

.comment-name:hover {
    color: var(--primary);
}

.comment-time {
    font-size: 11px;
    color: var(--text-muted);
}

.delete-comment-btn {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    font-size: 12px;
    opacity: 0.7;
    transition: all 0.2s ease;
}

.delete-comment-btn:hover {
    opacity: 1;
    background: rgba(239, 68, 68, 0.1);
}

.comment-content {
    margin-bottom: 8px;
    padding-left: 46px;
}

.comment-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text);
    word-wrap: break-word;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Noto Sans Arabic', 'Tahoma', 'Arial', sans-serif;
    unicode-bidi: embed;
}

/* Arabic and RTL text support */
.comment-content p:lang(ar),
.comment-content p[dir="rtl"],
.comment-content p[lang="ar"] {
    direction: rtl;
    text-align: right;
}

.comment-content a {
    color: var(--primary);
    text-decoration: none;
}

.comment-actions-bar {
    display: flex;
    gap: 12px;
    padding-left: 46px;
    margin-bottom: 8px;
}

.comment-action-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border: none;
    border-radius: var(--radius-full);
    background: none;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-action-btn:hover {
    background: var(--surface-hover);
    color: var(--text);
}

.comment-action-btn.liked {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.replies-container {
    margin-top: 8px;
}

.show-more-replies,
.hide-replies,
.show-replies-always {
    padding-left: 46px;
    margin-top: 8px;
}

.show-more-replies button,
.hide-replies,
.show-replies-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.show-more-replies button:hover,
.hide-replies:hover,
.show-replies-btn:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .comment-item.nested {
        margin-left: 12px;
        padding-left: 12px;
    }

    .comment-item.level-1 {
        margin-left: 12px;
    }

    .comment-item.level-2 {
        margin-left: 24px;
    }

    .comment-item.level-3 {
        margin-left: 36px;
    }

    .comment-item.level-4 {
        margin-left: 48px;
    }

    .comment-item.level-5 {
        margin-left: 60px;
    }

    .comment-content,
    .comment-actions-bar,
    .reply-form {
        padding-left: 0;
    }

    .show-more-replies,
    .hide-replies {
        padding-left: 0;
    }

    /* Mobile-friendly button styles */
    .comment-action-btn {
        padding: 8px 12px;
        min-height: 44px;
        -webkit-tap-highlight-color: transparent;
    }

    .comment-action-btn:active {
        background: var(--surface-hover);
        transform: scale(0.98);
    }

    .delete-comment-btn {
        padding: 10px;
        min-width: 44px;
        min-height: 44px;
        -webkit-tap-highlight-color: transparent;
    }

    .delete-comment-btn:active {
        opacity: 1;
        background: rgba(239, 68, 68, 0.2);
    }

    .show-replies-btn {
        padding: 10px 0;
        min-height: 44px;
        -webkit-tap-highlight-color: transparent;
    }

    .reply-input-wrapper textarea {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }

    .reply-input-wrapper button {
        min-width: 44px;
        min-height: 44px;
    }

    .cancel-reply {
        padding: 10px;
        min-height: 44px;
    }
}
</style>

<!-- Media Modal -->
<div id="media-modal" class="media-modal" onclick="closeMediaModal(event)">
    <div class="media-modal-content" onclick="event.stopPropagation()">
        <button class="media-modal-close" onclick="closeMediaModal()">&times;</button>
        <button class="media-modal-nav media-modal-prev" onclick="navigateMedia(-1)">&#8249;</button>
        <div id="media-modal-image"></div>
        <button class="media-modal-nav media-modal-next" onclick="navigateMedia(1)">&#8250;</button>
        <div class="media-modal-counter" id="media-modal-counter"></div>
    </div>
</div>

<script>
if (typeof window.postFunctionsInitialized === 'undefined') {
    window.postFunctionsInitialized = true;

    function deletePost(slug, btn) {
        if (!confirm('Are you sure you want to delete this post?')) return;
        
        const postCard = btn.closest('.post-card');
        
        fetch(`/posts/${slug}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                if (postCard) postCard.remove();
                showToast('Post deleted successfully', 'success');
                return { success: true };
            }
            // If not ok, try to parse JSON or reload
            return response.json().catch(() => {
                window.location.reload();
            });
        })
        .then(data => {
            if (data && data.success && postCard) {
                postCard.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to delete post', 'error');
            window.location.reload();
        });
    }

    function toggleLike(slug, btn) {
        // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
        const count = btn.querySelector('.count');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = count ? parseInt(count.textContent) || 0 : 0;
        
        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (count) count.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (count) count.textContent = currentCount + 1;
        }
        
        fetch(`/posts/${slug}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update likers button count
                var likersBtn = btn.closest('.post-actions').querySelector('.likers-btn');
                if (likersBtn) {
                    var likersCountSpan = likersBtn.querySelector('.likers-count');
                    if (likersCountSpan) {
                        likersCountSpan.textContent = data.likes_count > 0 ? data.likes_count : '';
                    }
                }
            } else {
                // Revert on failure
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (count) count.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (count) count.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (count) count.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (count) count.textContent = currentCount;
            }
        });
    }

    function toggleSave(slug, btn) {
        fetch(`/posts/${slug}/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.saved) {
                btn.classList.add('saved');
                btn.querySelector('span').textContent = 'Saved';
                showToast('Post saved successfully', 'success');
            } else {
                btn.classList.remove('saved');
                btn.querySelector('span').textContent = 'Save';
                showToast('Post removed from saved', 'info');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function copyPostLink(slug) {
        const url = window.location.origin + '/posts/' + slug;

        // Use Clipboard API if available (HTTPS / localhost)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard!', 'success');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(ta);
            if (ok) {
                showToast('Link copied to clipboard!', 'success');
            } else {
                showToast('Copy failed — please copy manually: ' + text, 'error');
            }
        } catch (e) {
            showToast('Copy failed — please copy manually: ' + text, 'error');
        }
    }

    function showLikers(slug) {
        fetch(`/posts/${slug}/likers`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.likers && data.likers.length > 0) {
                showLikersModal(data.likers);
            } else {
                showToast('No likes yet', 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Could not load likers', 'error');
        });
    }

    function showLikersModal(likers) {
        // Remove existing modal if any
        const existingModal = document.getElementById('likers-modal');
        if (existingModal) existingModal.remove();

        const modal = document.createElement('div');
        modal.id = 'likers-modal';
        modal.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            background: var(--surface, #161616);
            border: 1px solid var(--border, #2a2a2a);
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border, #2a2a2a);
        `;
        header.innerHTML = `
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text);">Likes (${likers.length})</h3>
            <button onclick="document.getElementById('likers-modal').remove()" style="background: none; border: none; color: var(--text-muted, #86868b); font-size: 24px; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        `;

        const list = document.createElement('div');
        list.style.cssText = 'display: flex; flex-direction: column; gap: 8px;';

        likers.forEach(liker => {
            const avatar = liker.avatar || null;
            const displayName = liker.username || liker.name || 'User';
            const initial = displayName ? displayName.charAt(0).toUpperCase() : '?';

            const item = document.createElement('a');
            item.href = `/users/${liker.username}`;
            item.style.cssText = `
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px;
                border-radius: 12px;
                text-decoration: none;
                color: inherit;
                transition: background 0.2s;
            `;
            item.onmouseover = () => item.style.background = 'var(--surface-hover, #1c1c1e)';
            item.onmouseout = () => item.style.background = 'transparent';

            item.innerHTML = `
                ${avatar
                    ? `<img src="${avatar}" alt="${displayName}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">`
                    : `<div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary, #5e60ce), var(--secondary, #4ea8de)); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: white;">${initial}</div>`
                }
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 14px;">@${displayName}</div>
                    ${liker.name ? `<div style="font-size: 12px; color: var(--text-muted, #86868b);">${liker.name}</div>` : ''}
                </div>
                ${liker.is_verified ? '<i class="fas fa-check-circle" style="color: #22c55e; font-size: 16px;"></i>' : ''}
            `;

            list.appendChild(item);
        });

        content.appendChild(header);
        content.appendChild(list);
        modal.appendChild(content);
        document.body.appendChild(modal);

        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
    }

    function submitComment(postSlug, postId) {
        const textarea = document.getElementById(`comment-content-${postSlug}`);
        const content = textarea.value.trim();
        
        if (!content) return;
        
        fetch(`/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content: content, post_id: postId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.comment) {
                textarea.value = '';

                // Add comment to the list dynamically without page reload
                const commentsList = document.querySelector(`#post-${postId} .comments-list`);
                if (commentsList && data.comment) {
                    const user = data.comment.user || { username: '{{ auth()->user() ? auth()->user()->username : "user" }}', name: '{{ auth()->user() ? auth()->user()->name : "User" }}', profile: { avatar: null } };
                    const avatar = user.profile && user.profile.avatar
                        ? `/storage/${user.profile.avatar}`
                        : null;
                    const firstLetter = user.username ? user.username.charAt(0).toUpperCase() : '?';

                    const userName = data.comment.user && data.comment.user.username ? data.comment.user.username : '{{ auth()->user() ? auth()->user()->username : "user" }}';
                    const avatarUrl = data.comment.user && data.comment.user.avatar_url ? data.comment.user.avatar_url : null;

                    const commentHtml = `
                        <div class="comment-item level-0" data-comment-id="${data.comment.id}">
                            <div class="comment-header">
                                <div class="comment-author">
                                    ${avatarUrl
                                        ? `<img src="${avatarUrl}" alt="Avatar" class="comment-avatar">`
                                        : `<div class="comment-avatar-placeholder">?</div>`
                                    }
                                    <div class="comment-author-info">
                                        <a href="/users/${userName}" class="comment-name">${userName}</a>
                                        <span class="comment-time">Just now</span>
                                    </div>
                                </div>
                                <button type="button" class="delete-comment-btn" onclick="deleteComment(${data.comment.id}, this)" title="Delete comment">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="comment-content">
                                <p>${data.comment.content}</p>
                            </div>
                            <div class="comment-actions-bar">
                                <button type="button" class="comment-action-btn" onclick="likeComment(${data.comment.id}, this)">
                                    <i class="fas fa-heart"></i>
                                    <span>0</span>
                                </button>
                                <button type="button" class="comment-action-btn" onclick="toggleReplyForm(${data.comment.id})">
                                    <i class="fas fa-reply"></i>
                                    <span>Reply</span>
                                </button>
                            </div>
                            <div class="reply-form" id="reply-form-${data.comment.id}" style="display: none;">
                                <div class="reply-input-wrapper">
                                    <textarea id="reply-content-${data.comment.id}" placeholder="Write a reply..." maxlength="5000"></textarea>
                                    <button type="button" onclick="submitReply(${data.comment.id}, ${postId})">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <button type="button" class="cancel-reply" onclick="toggleReplyForm(${data.comment.id})">Cancel</button>
                            </div>
                        </div>
                    `;
                    
                    // Insert at the beginning of comments list
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    
                    // Update comment count
                    const commentCount = document.querySelector(`#post-${postId} .post-comments-section h4`);
                    if (commentCount) {
                        const currentCount = parseInt(commentCount.textContent.match(/\d+/)) || 0;
                        commentCount.textContent = `Comments (${currentCount + 1})`;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to post comment. Please try again.', 'error');
        });
    }

    function toggleComments(postId, show) {
        const hiddenComments = document.getElementById(`hidden-comments-${postId}`);
        const showMoreBtn = document.querySelector(`#post-${postId} .show-more-comments`);
        
        if (hiddenComments) {
            hiddenComments.style.display = show ? 'block' : 'none';
        }
        
        if (showMoreBtn) {
            showMoreBtn.style.display = show ? 'none' : 'block';
        }
    }

    function likeComment(commentId, btn) {
        // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
        const countSpan = btn.querySelector('span');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = countSpan ? parseInt(countSpan.textContent) || 0 : 0;
        
        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (countSpan) countSpan.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (countSpan) countSpan.textContent = currentCount + 1;
        }
        
        fetch(`/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert on failure
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (countSpan) countSpan.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (countSpan) countSpan.textContent = currentCount;
            }
        });
    }

    function deleteComment(commentId, btn) {
        if (!confirm('Delete this comment?')) return;
        
        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentItem = btn.closest('.comment-item');
                if (commentItem) commentItem.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function submitReply(commentId, postId) {
        const textarea = document.getElementById(`reply-content-${commentId}`);
        const content = textarea.value.trim();
        
        if (!content) return;
        
        fetch(`/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content: content, post_id: postId, parent_id: commentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comment) {
                textarea.value = '';
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                if (replyForm) replyForm.style.display = 'none';

                // Add reply to the parent comment's replies container
                const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (parentComment) {
                    let repliesContainer = parentComment.querySelector('.replies-container');
                    
                    // If no replies container, create one
                    if (!repliesContainer) {
                        repliesContainer = document.createElement('div');
                        repliesContainer.className = 'replies-container';
                        parentComment.appendChild(repliesContainer);
                    }
                    
                    const userName = data.comment.user && data.comment.user.username ? data.comment.user.username : '{{ auth()->user() ? auth()->user()->username : "user" }}';
                    const avatarUrl = data.comment.user && data.comment.user.avatar_url ? data.comment.user.avatar_url : null;
                    const initial = userName.charAt(0).toUpperCase();

                    // Determine the level of the new reply based on parent comment
                    let parentLevel = null;
                    const parentCommentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (parentCommentEl) {
                        // Check for level classes (level-0, level-1, level-2, etc.)
                        for (let i = 0; i <= 5; i++) {
                            if (parentCommentEl.classList.contains('level-' + i)) {
                                parentLevel = i;
                                break;
                            }
                        }
                    }
                    const newLevel = parentLevel !== null ? parentLevel + 1 : 1;
                    
                    // Check if reply button should be shown (max level is 4, so show for level < 4)
                    const showReplyBtn = newLevel < 4;
                    
                    // Check if we need to show "Hide replies" button (when there's only 1 reply visible)
                    const existingReplies = repliesContainer.querySelectorAll('.comment-item.nested').length;
                    let showHideRepliesBtn = false;
                    let hideRepliesHtml = '';
                    
                    if (existingReplies === 0) {
                        // First reply - add hide button
                        showHideRepliesBtn = true;
                    }
                    
                    const replyHtml = `
                        <div class="comment-item nested level-${newLevel}" data-comment-id="${data.comment.id}">
                            <div class="comment-header">
                                <div class="comment-author">
                                    ${avatarUrl 
                                        ? `<img src="${avatarUrl}" alt="Avatar" class="comment-avatar">`
                                        : `<div class="comment-avatar-placeholder">${initial}</div>`
                                    }
                                    <div class="comment-author-info">
                                        <a href="/users/${userName}" class="comment-name">${userName}</a>
                                        <span class="comment-time">Just now</span>
                                    </div>
                                </div>
                                <button type="button" class="delete-comment-btn" onclick="deleteComment(${data.comment.id}, this)" title="Delete comment">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="comment-content">
                                <p>${data.comment.content}</p>
                            </div>
                            <div class="comment-actions-bar">
                                <button type="button" class="comment-action-btn" onclick="likeComment(${data.comment.id}, this)">
                                    <i class="fas fa-heart"></i>
                                    <span>0</span>
                                </button>
                                ${showReplyBtn ? `
                                <button type="button" class="comment-action-btn" onclick="toggleReplyForm(${data.comment.id})">
                                    <i class="fas fa-reply"></i>
                                    <span>Reply</span>
                                </button>
                                ` : ''}
                            </div>
                            ${showReplyBtn ? `
                            <div class="reply-form" id="reply-form-${data.comment.id}" style="display: none;">
                                <div class="reply-input-wrapper">
                                    <textarea id="reply-content-${data.comment.id}" placeholder="Write a reply..." maxlength="5000"></textarea>
                                    <button type="button" onclick="submitReply(${data.comment.id}, ${postId})">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <button type="button" class="cancel-reply" onclick="toggleReplyForm(${data.comment.id})">Cancel</button>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    repliesContainer.insertAdjacentHTML('afterbegin', replyHtml);
                    
                    // Add or update "Show replies" button (show always for 1-3 replies)
                    if (showHideRepliesBtn) {
                        let showMoreDiv = repliesContainer.querySelector('.show-more-replies');
                        let showRepliesAlways = repliesContainer.querySelector('.show-replies-always');
                        
                        if (!showRepliesAlways) {
                            showRepliesAlways = document.createElement('div');
                            showRepliesAlways.className = 'show-replies-always';
                            repliesContainer.appendChild(showRepliesAlways);
                        }
                        showRepliesAlways.innerHTML = '<button type="button" class="show-replies-btn" onclick="toggleNestedReplies(' + commentId + ', true)">Show 1 reply</button>';
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (form) {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.querySelector('textarea').focus();
            }
        }
    }

    function toggleNestedReplies(commentId, show) {
        const hiddenReplies = document.getElementById('hidden-replies-' + commentId);
        const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!parentComment) return;
        
        const showMoreBtn = parentComment.querySelector('.show-more-replies');
        const showRepliesAlways = parentComment.querySelector('.show-replies-always');
        
        if (hiddenReplies) {
            hiddenReplies.style.display = show ? 'block' : 'none';
        }
        
        // Always hide both buttons when clicked
        if (showMoreBtn) {
            showMoreBtn.style.display = 'none';
        }
        
        if (showRepliesAlways) {
            showRepliesAlways.style.display = 'none';
        }
    }

    function showLoginModal(action, message) {
        alert('Please login to ' + action);
    }

    // Media modal functions
    window.currentMediaIndex = 0;
    window.currentMediaArray = [];

    function openMediaModal(postId, index) {
        const mediaContainer = document.querySelector(`.post-media[data-post-id="${postId}"]`);
        if (!mediaContainer) return;
        
        // Get all media from data attribute (includes all images, not just visible ones)
        const mediaListData = mediaContainer.getAttribute('data-media-list');
        if (mediaListData) {
            window.currentMediaArray = JSON.parse(mediaListData);
        } else {
            // Fallback: get from DOM (only visible images)
            const mediaItems = mediaContainer.querySelectorAll('.media-item img, .media-item video');
            window.currentMediaArray = Array.from(mediaItems).map(item => ({
                src: item.src || item.querySelector('source')?.src,
                type: item.tagName === 'VIDEO' ? 'video' : 'image'
            }));
        }
        
        window.currentMediaIndex = parseInt(index);
        showMediaInModal();
        
        document.getElementById('media-modal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function showMediaInModal() {
        const container = document.getElementById('media-modal-image');
        const media = window.currentMediaArray[window.currentMediaIndex];
        
        if (!media) return;
        
        if (media.type === 'video') {
            container.innerHTML = `<video controls autoplay><source src="${media.src}" type="video/mp4"></video>`;
        } else {
            container.innerHTML = `<img src="${media.src}" alt="Media">`;
        }
        
        document.getElementById('media-modal-counter').textContent = 
            `${window.currentMediaIndex + 1} / ${window.currentMediaArray.length}`;
    }

    function closeMediaModal(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('media-modal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function navigateMedia(direction) {
        window.currentMediaIndex += direction;
        if (window.currentMediaIndex < 0) window.currentMediaIndex = window.currentMediaArray.length - 1;
        if (window.currentMediaIndex >= window.currentMediaArray.length) window.currentMediaIndex = 0;
        showMediaInModal();
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('media-modal');
        if (!modal || !modal.classList.contains('active')) return;
        
        if (e.key === 'Escape') closeMediaModal();
        if (e.key === 'ArrowLeft') navigateMedia(-1);
        if (e.key === 'ArrowRight') navigateMedia(1);
    });

    function quickFollow(username, btn) {
        fetch(`/users/${username}/follow`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.following) {
                btn.classList.add('following');
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');
                if (icon) {
                    icon.classList.remove('fa-user-plus');
                    icon.classList.add('fa-user-minus');
                }
                if (text) text.textContent = 'Following';
                btn.setAttribute('data-following', 'true');
            } else {
                btn.classList.remove('following');
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');
                if (icon) {
                    icon.classList.remove('fa-user-minus');
                    icon.classList.add('fa-user-plus');
                }
                if (text) text.textContent = 'Follow';
                btn.setAttribute('data-following', 'false');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Toggle post content show more/less
    function togglePostContent(button) {
        const postText = button.previousElementSibling;
        const showMoreText = button.querySelector('.show-more-text');
        const showLessText = button.querySelector('.show-less-text');
        
        const fullContent = postText.getAttribute('data-full-content');
        const truncatedContent = postText.getAttribute('data-truncated-content');

        if (postText.classList.contains('truncated')) {
            // Expand - show full content
            postText.classList.remove('truncated');
            postText.classList.add('expanded');
            postText.innerHTML = fullContent;
            postText.style.display = 'block';
            postText.style.webkitLineClamp = 'unset';
            postText.style.webkitBoxOrient = 'unset';
            postText.style.overflow = 'visible';
            showMoreText.style.display = 'none';
            showLessText.style.display = 'inline';
        } else {
            // Collapse - show truncated content
            postText.classList.remove('expanded');
            postText.classList.add('truncated');
            postText.innerHTML = truncatedContent;
            postText.style.display = '-webkit-box';
            postText.style.webkitLineClamp = '3';
            postText.style.webkitBoxOrient = 'vertical';
            postText.style.overflow = 'hidden';
            showMoreText.style.display = 'inline';
            showLessText.style.display = 'none';
        }
    }
}
</script>
