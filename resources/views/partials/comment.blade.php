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
            <button type="button" class="delete-comment-btn" onclick="deleteComment({{ $comment->id }}, this)" title="{{ __('messages.delete_comment') }}">
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
                    <span>{{ __('messages.reply') }}</span>
                </button>
            @endif
        @else
            <button type="button" class="comment-action-btn" onclick="showLoginModal('like', '{{ __('messages.like_comments_prompt') }}')">
                <i class="fas fa-heart"></i>
                <span>{{ $comment->likes->count() }}</span>
            </button>
            @if($level < $maxLevel)
                <button type="button" class="comment-action-btn" onclick="showLoginModal('reply', '{{ __('messages.reply_comments_prompt') }}')">
                    <i class="fas fa-reply"></i>
                    <span>{{ __('messages.reply') }}</span>
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
                <textarea id="reply-content-{{ $comment->id }}" placeholder="{{ __('messages.write_a_reply') }}" maxlength="5000"></textarea>
                <button type="button" onclick="submitReply({{ $comment->id }}, {{ $comment->post_id }})">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <button type="button" class="cancel-reply" onclick="toggleReplyForm({{ $comment->id }})">{{ __('messages.cancel') }}</button>
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
                        {{ $comment->replies->count() == 1 ? __('messages.show_reply') : __('messages.show_replies', ['count' => $comment->replies->count()]) }}
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
