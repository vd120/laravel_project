@php
    $level = $level ?? 0;
    $maxLevel = 4;
@endphp

<div class="comment-item {{ $level > 0 ? 'nested' : '' }} level-{{ $level }}" data-comment-id="{{ $comment->id }}">
    <div class="comment-header">
        <div class="comment-author">
            <img src="{{ $comment->user->avatar_url }}" alt="Avatar" class="comment-avatar">
            <div class="comment-author-info">
                <a href="{{ route('users.show', $comment->user) }}" class="comment-name">{{ $comment->user->username }}</a>
                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @if(auth()->check() && $comment->user_id === auth()->id())
            <button type="button" class="delete-comment-btn" onclick="deleteComment({{ $comment->id }}, this)" title="Delete comment">
                <i class="fas fa-trash-alt"></i>
            </button>
        @endif
    </div>

    <div class="comment-content">
        <p>{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content) !!}</p>
    </div>

    <div class="comment-actions-bar">
        @if(auth()->check())
            <button type="button" class="comment-action-btn {{ $comment->likedBy(auth()->user()) ? 'liked' : '' }}" onclick="likeComment({{ $comment->id }}, this)">
                <i class="fas fa-heart"></i>
                <span>{{ $comment->likes->count() }}</span>
            </button>
            @if($level < $maxLevel)
                <button type="button" class="comment-action-btn" onclick="toggleReplyForm({{ $comment->id }})">
                    <i class="fas fa-reply"></i>
                    <span>Reply</span>
                </button>
            @endif
        @else
            <button type="button" class="comment-action-btn" onclick="showLoginModal('like', 'Like comments to show appreciation!')">
                <i class="fas fa-heart"></i>
                <span>{{ $comment->likes->count() }}</span>
            </button>
            @if($level < $maxLevel)
                <button type="button" class="comment-action-btn" onclick="showLoginModal('reply', 'Login to reply to comments!')">
                    <i class="fas fa-reply"></i>
                    <span>Reply</span>
                </button>
            @endif
        @endif
    </div>

    @if($level < $maxLevel)
        <div class="reply-form" id="reply-form-{{ $comment->id }}" style="display: none;">
            <div class="reply-input-wrapper">
                @if(auth()->check())
                    <img src="{{ auth()->user()->avatar_url }}" alt="Your avatar" class="reply-avatar">
                @endif
                <textarea id="reply-content-{{ $comment->id }}" placeholder="Write a reply..." maxlength="5000"></textarea>
                <button type="button" onclick="submitReply({{ $comment->id }}, {{ $comment->post_id }})">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <button type="button" class="cancel-reply" onclick="toggleReplyForm({{ $comment->id }})">Cancel</button>
        </div>
    @endif

    @if($comment->replies && $comment->replies->count() > 0)
        @php
            // Hide all replies initially, show button to reveal
            // Sort by newest first
            $hiddenReplies = $comment->replies->sortByDesc('created_at');
            $hasReplies = $hiddenReplies->count() > 0;
        @endphp

        <div class="replies-container">
            @if($hasReplies)
                <div class="show-replies-always">
                    <button type="button" class="show-replies-btn" onclick="toggleNestedReplies({{ $comment->id }}, true)">
                        Show {{ $comment->replies->count() }} {{ $comment->replies->count() == 1 ? 'reply' : 'replies' }}
                    </button>
                </div>
                <div class="hidden-replies" id="hidden-replies-{{ $comment->id }}" style="display: none;">
                    @foreach($hiddenReplies as $reply)
                        @include('partials.comment', ['comment' => $reply, 'level' => $level + 1])
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

<style>
.comment-item {
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s ease;
}

.comment-item:hover {
    background: var(--surface-hover);
    margin: 0 -16px;
    padding: 14px 16px;
    border-radius: var(--radius);
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
    margin-bottom: 10px;
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
    flex-shrink: 0;
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
    gap: 2px;
}

.comment-name {
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
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
    color: var(--text-muted);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    font-size: 12px;
    opacity: 0;
    transition: all 0.2s ease;
}

.comment-item:hover .delete-comment-btn {
    opacity: 0.7;
}

.delete-comment-btn:hover {
    opacity: 1 !important;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.comment-content {
    margin-bottom: 10px;
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
    font-weight: 500;
}

.comment-content a:hover {
    text-decoration: underline;
}

.comment-actions-bar {
    display: flex;
    gap: 8px;
    align-items: center;
}

.comment-action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border: none;
    border-radius: var(--radius-full);
    background: transparent;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-action-btn:hover {
    background: var(--surface);
    color: var(--text);
}

.comment-action-btn.liked {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.comment-action-btn i {
    font-size: 13px;
}

.reply-form {
    margin-top: 10px;
    margin-bottom: 12px;
}

.reply-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.reply-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
    flex-shrink: 0;
}

.reply-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #8B5CF6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
}

.reply-input-wrapper textarea {
    flex: 1;
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-full);
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    resize: none;
    height: 36px;
    line-height: 20px;
    transition: all 0.2s ease;
}

.reply-input-wrapper textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--surface-hover);
}

.reply-input-wrapper textarea::placeholder {
    color: var(--text-muted);
}

.reply-input-wrapper button {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.reply-input-wrapper button:hover {
    background: var(--primary-hover);
    transform: scale(1.05);
}

.reply-input-wrapper button:active {
    transform: scale(0.98);
}

.cancel-reply {
    margin-top: 8px;
    margin-left: 48px;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius);
    transition: all 0.2s ease;
}

.cancel-reply:hover {
    color: var(--text);
    background: var(--surface);
}

.replies-container {
    margin-top: 10px;
}

.show-more-replies,
.hide-replies,
.show-replies-always {
    margin-top: 10px;
}

.show-more-replies button,
.hide-replies,
.show-replies-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: var(--radius);
    transition: all 0.2s ease;
}

.show-more-replies button:hover,
.hide-replies:hover,
.show-replies-btn:hover {
    background: var(--surface);
    color: var(--primary-hover);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .comment-item:hover {
        background: transparent;
        margin: 0;
        padding: 14px 0;
    }
    
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

    .comment-content {
        padding-left: 0;
    }

    .comment-actions-bar {
        padding-left: 0;
    }

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
        background: var(--surface);
        transform: scale(0.98);
    }

    .delete-comment-btn {
        padding: 10px;
        min-width: 44px;
        min-height: 44px;
        -webkit-tap-highlight-color: transparent;
        opacity: 0.7;
    }

    .delete-comment-btn:active {
        opacity: 1;
        background: rgba(239, 68, 68, 0.2);
    }

    .show-replies-btn {
        padding: 10px 12px;
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
        margin-left: 44px;
    }

    /* Disable hover effects on mobile */
    .comment-name:hover { color: var(--text); }
    .comment-action-btn:hover { background: transparent; color: var(--text-muted); }
    .reply-input-wrapper button:hover { transform: none; }
    .cancel-reply:hover { background: transparent; color: var(--text-muted); }
    .show-more-replies button:hover, .hide-replies:hover, .show-replies-btn:hover { background: transparent; text-decoration: none; }
}

/* Disable ALL hover effects on touch devices */
@media (hover: none) {
    .comment-item:hover {
        background: transparent;
        margin: 0;
        padding: 14px 0;
    }
    
    .comment-name:hover { color: var(--text); }
    .delete-comment-btn:hover { opacity: 0.7; background: transparent; }
    .comment-action-btn:hover { background: transparent; color: var(--text-muted); }
    .reply-input-wrapper button:hover { transform: none; }
    .cancel-reply:hover { background: transparent; color: var(--text-muted); }
    .show-more-replies button:hover, .hide-replies:hover, .show-replies-btn:hover { background: transparent; text-decoration: none; }
}
</style>

<script>
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
    const parentComment = document.querySelector('[data-comment-id="' + commentId + '"]');
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
</script>
