<div class="post" data-post-id="{{ $post->id }}">
    <div class="user">
        <a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a>
        @if($post->is_private)
            <span class="privacy-badge private" style="font-size: 10px; padding: 1px 4px;">
                <i class="fas fa-lock"></i> Private
            </span>
        @endif
        <small>{{ $post->created_at->diffForHumans() }}</small>
        @if($post->user_id !== auth()->id())
            <button type="button"
                    class="btn follow-btn {{ auth()->user()->isFollowing($post->user) ? 'following' : '' }}"
                    data-user-id="{{ $post->user->id }}"
                    data-username="{{ $post->user->name }}"
                    onclick="toggleFollow(this, {{ $post->user->id }})"
                    style="font-size: 11px; padding: 3px 8px; margin-left: 10px; background: {{ auth()->user()->isFollowing($post->user) ? '#28a745' : 'var(--twitter-blue)' }};">
                {{ auth()->user()->isFollowing($post->user) ? 'Following' : 'Follow' }}
            </button>
        @endif
    </div>
    @if($post->content)
        <div class="content">{{ $post->content }}</div>
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
        <button onclick="copyPostLink({{ $post->id }})" class="btn" style="background: #6c757d; margin-left: 10px;">
            <i class="fas fa-share"></i> Share
        </button>
        @if($post->user_id === auth()->id())
        <button type="button" class="btn" style="background: red; margin-left: 10px;" onclick="deletePost({{ $post->id }}, this)">Delete</button>
        @endif
    </div>
    <hr>
    <h4>Comments</h4>
    <div class="comment-form-container">
        <textarea id="comment-content-{{ $post->id }}" placeholder="Add a comment..." maxlength="280" required></textarea>
        <button type="button" class="btn" style="font-size: 12px; padding: 5px 10px;" onclick="submitComment({{ $post->id }})">Comment</button>
    </div>
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

@if($hasMoreComments) <!-- Show button for posts with 3+ comments -->
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
