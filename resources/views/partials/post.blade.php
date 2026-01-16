<div class="post" data-post-id="{{ $post->id }}">
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
</style>
    <div class="post-header">
        <div class="user" style="display: flex; align-items: center; gap: 8px;">
            @if($post->user->profile && $post->user->profile->avatar)
                <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="{{ $post->user->name }}'s avatar" class="user-avatar-small" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid #e1e8ed;">
            @else
                <div class="user-avatar-small user-avatar-placeholder" style="width: 32px; height: 32px; border-radius: 50%; background-color: #e1e8ed; display: flex; align-items: center; justify-content: center; color: #657786; font-weight: 600; border: 2px solid #e1e8ed; font-size: 14px;">
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
                        style="font-size: 11px; padding: 3px 8px; background: {{ auth()->user()->isFollowing($post->user) ? '#28a745' : 'var(--twitter-blue)' }}; margin-left: 8px;">
                    {{ auth()->user()->isFollowing($post->user) ? 'Following' : 'Follow' }}
                </button>
            @endif
            @if($post->is_private)
                <span class="privacy-badge private" style="font-size: 10px; padding: 1px 4px; margin-left: 8px;">
                    <i class="fas fa-lock"></i> Private
                </span>
            @endif
            <small style="color: var(--twitter-gray); font-size: 12px; margin-left: 8px;">{{ $post->created_at->diffForHumans() }}</small>
        </div>
        @if($post->user_id === auth()->id())
            <button type="button" class="post-delete-btn" onclick="deletePost({{ $post->id }}, this)" title="Delete post">
                <i class="fas fa-trash"></i>
            </button>
        @endif
    </div>
    @if($post->content)
        <div class="content">{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content) !!}</div>
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

    <div>
        @if(auth()->check())
            <button type="button"
                    class="btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}"
                    onclick="toggleLike({{ $post->id }}, this)"
                    style="background: {{ $post->likedBy(auth()->user()) ? 'red' : 'var(--twitter-blue)' }};">
                <i class="fas fa-heart"></i> <span class="like-count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button"
                    class="btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}"
                    onclick="toggleSave({{ $post->id }}, this)"
                    style="background: {{ $post->savedBy(auth()->user()) ? '#17a2b8' : '#6c757d' }}; margin-left: 10px;">
                <i class="fas fa-bookmark"></i> <span class="save-text">{{ $post->savedBy(auth()->user()) ? 'Saved' : 'Save' }}</span>
            </button>
        @else
            <button type="button"
                    class="btn like-btn"
                    onclick="showLoginModal('like', 'Like posts to show your appreciation and support creators!')"
                    style="background: var(--twitter-blue);">
                <i class="fas fa-heart"></i> <span class="like-count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button"
                    class="btn save-btn"
                    onclick="showLoginModal('save', 'Save posts to keep your favorite content organized and easily accessible!')"
                    style="background: #6c757d; margin-left: 10px;">
                <i class="fas fa-bookmark"></i> Save
            </button>
        @endif
        <button onclick="copyPostLink({{ $post->id }})" class="btn" style="background: #6c757d; margin-left: 10px;">
            <i class="fas fa-share"></i> Share
        </button>
    </div>
    <hr>
    <h4>Comments</h4>
    @if(auth()->check())
        <div class="comment-form-container">
            <textarea id="comment-content-{{ $post->id }}" placeholder="Add a comment..." maxlength="280" required></textarea>
            <button type="button" class="btn" style="font-size: 12px; padding: 5px 10px;" onclick="submitComment({{ $post->id }})">Comment</button>
        </div>
    @else
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
