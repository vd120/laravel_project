@php
    $level = $level ?? 0;
    $maxLevel = 3; // limit nesting to 3 levels
@endphp

<div class="comment {{ $level > 0 ? 'nested-comment' : 'main-comment' }} level-{{ $level }}" data-comment-id="{{ $comment->id }}">
    <div class="comment-avatar">
        @if($comment->user->profile && $comment->user->profile->avatar)
            <img src="{{ asset('storage/' . $comment->user->profile->avatar) }}" alt="Avatar" class="comment-user-avatar">
        @else
            <div class="comment-user-avatar-placeholder">
                <i class="fas fa-user"></i>
            </div>
        @endif
    </div>

    <div class="comment-content-wrapper">
        <div class="comment-header">
            <div class="comment-user-info">
                <a href="{{ route('users.show', $comment->user) }}" class="comment-user-name">{{ $comment->user->name }}</a>
                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div class="comment-actions">
                <button type="button" class="comment-delete-btn" onclick="deleteComment({{ $comment->id }}, this)" title="Delete comment">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        <div class="comment-body">
            <div class="comment-text">{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content) !!}</div>
        </div>

        <div class="comment-footer">
            <div class="comment-interactions">
                <button type="button" class="comment-like-btn {{ $comment->likedBy(auth()->user()) ? 'liked' : '' }}" onclick="likeComment({{ $comment->id }}, this)">
                    <i class="fas fa-heart"></i>
                    <span class="comment-like-count">{{ $comment->likes->count() }}</span>
                </button>
                @if($level < $maxLevel)
                    <button type="button" class="comment-reply-btn" onclick="toggleReplyForm({{ $comment->id }})">
                        <i class="fas fa-reply"></i>
                        Reply
                    </button>
                @endif
            </div>
        </div>

        <div id="reply-form-{{ $comment->id }}" class="comment-reply-form" style="display: none;">
            <div class="reply-form-container">
                <div class="reply-avatar">
                    @if(auth()->user()->profile && auth()->user()->profile->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="Your avatar">
                    @else
                        <div class="reply-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <div class="reply-input-container">
                    <textarea id="reply-content-{{ $comment->id }}" placeholder="Write a reply..." maxlength="280" required class="reply-textarea"></textarea>
                    <div class="reply-actions">
                        <button type="button" class="reply-submit-btn" onclick="submitReply({{ $comment->id }}, {{ $comment->post_id }})">
                            <i class="fas fa-paper-plane"></i>
                            Reply
                        </button>
                        <button type="button" class="reply-cancel-btn" onclick="toggleReplyForm({{ $comment->id }})">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($comment->replies && $comment->replies->count() > 0)
        @php
            $visibleReplies = $comment->replies->take(3);
            $hiddenReplies = $comment->replies->skip(3);
            $hasMoreReplies = $hiddenReplies->count() > 0;
        @endphp

        <div class="comment-replies">
            @foreach($visibleReplies as $reply)
                @include('partials.comment', ['comment' => $reply, 'level' => $level + 1])
            @endforeach

            @if($hasMoreReplies)
                <div class="replies-hidden" id="hidden-replies-{{ $comment->id }}" style="display: none;">
                    @foreach($hiddenReplies as $reply)
                        @include('partials.comment', ['comment' => $reply, 'level' => $level + 1])
                    @endforeach
                </div>

                <div class="show-more-nested-replies-container" id="show-more-nested-replies-container-{{ $comment->id }}">
                    <button type="button" class="show-more-nested-replies-btn" onclick="toggleNestedReplies({{ $comment->id }}, true)">
                        <i class="fas fa-chevron-down"></i>
                        Show {{ $hiddenReplies->count() }} more repl{{ $hiddenReplies->count() > 1 ? 'ies' : 'y' }}
                    </button>
                </div>

                <div class="hide-nested-replies-container" id="hide-nested-replies-container-{{ $comment->id }}" style="display: none;">
                    <button type="button" class="hide-nested-replies-btn" onclick="toggleNestedReplies({{ $comment->id }}, false)">
                        <i class="fas fa-chevron-up"></i>
                        Hide replies
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
