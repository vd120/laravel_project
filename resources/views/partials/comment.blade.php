@php
    $level = $level ?? 0;
    $maxLevel = 4;
@endphp

<div class="comment-item {{ $level > 0 ? 'nested' : '' }} level-{{ $level }}" data-comment-id="{{ $comment->id }}">
    <div class="comment-header">
        <div class="comment-author">
            @if($comment->user->profile && $comment->user->profile->avatar)
                <img src="{{ asset('storage/' . $comment->user->profile->avatar) }}" alt="Avatar" class="comment-avatar">
            @else
                <div class="comment-avatar-placeholder">{{ substr($comment->user->name, 0, 1) }}</div>
            @endif
            <div class="comment-author-info">
                <a href="{{ route('users.show', $comment->user) }}" class="comment-name">{{ $comment->user->name }}</a>
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
                    @if(auth()->user()->profile && auth()->user()->profile->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="Your avatar" class="reply-avatar">
                    @else
                        <div class="reply-avatar-placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    @endif
                @endif
                <textarea id="reply-content-{{ $comment->id }}" placeholder="Write a reply..." maxlength="280"></textarea>
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
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-item.nested {
    margin-left: 20px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
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
    border: 2px solid var(--border-color);
}

.comment-avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-blue), #8B5CF6);
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
    color: var(--twitter-dark);
    text-decoration: none;
    font-size: 14px;
}

.comment-name:hover {
    color: var(--twitter-blue);
}

.comment-time {
    font-size: 11px;
    color: var(--twitter-gray);
}

.delete-comment-btn {
    background: none;
    border: none;
    color: var(--error-color);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    font-size: 12px;
    opacity: 0.7;
    transition: all 0.2s ease;
}

.delete-comment-btn:hover {
    opacity: 1;
    background: rgba(244, 33, 46, 0.1);
}

.comment-content {
    margin-bottom: 8px;
    padding-left: 46px;
}

.comment-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: var(--twitter-dark);
    word-wrap: break-word;
}

.comment-content a {
    color: var(--twitter-blue);
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
    border-radius: 12px;
    background: none;
    color: var(--twitter-gray);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-action-btn:hover {
    background: var(--twitter-light);
    color: var(--twitter-dark);
}

.comment-action-btn.liked {
    color: var(--error-color);
    background: rgba(244, 33, 46, 0.1);
}

.reply-form {
    padding-left: 46px;
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
    border: 2px solid var(--border-color);
}

.reply-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-blue), #8B5CF6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
}

.reply-input-wrapper textarea {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 16px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    font-size: 13px;
    resize: none;
    height: 36px;
    line-height: 20px;
}

.reply-input-wrapper textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
}

.reply-input-wrapper button {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: var(--twitter-blue);
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
    color: var(--twitter-gray);
    font-size: 12px;
    cursor: pointer;
}

.cancel-reply:hover {
    color: var(--twitter-dark);
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
    color: var(--twitter-blue);
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
