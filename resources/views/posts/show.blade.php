@extends('layouts.app')

@section('content')
<div class="post-detail">
    <p class="back-link"><a href="{{ url()->previous() }}">← Back</a></p>

    <div class="post" data-post-id="{{ $post->id }}">
        <div class="user">
            <a href="{{ route('users.show', $post->user) }}">@{{ $post->user->name }}</a>
            <small>{{ $post->created_at->diffForHumans() }}</small>
            @if($post->user_id !== auth()->id())
                <button type="button"
                        class="btn follow-btn {{ auth()->user()->isFollowing($post->user) ? 'following' : '' }}"
                        onclick="toggleFollow(this, {{ $post->user->id }})"
                        style="font-size: 11px; padding: 3px 8px; margin-left: 10px; background: {{ auth()->user()->isFollowing($post->user) ? '#28a745' : 'var(--twitter-blue)' }};">
                    {{ auth()->user()->isFollowing($post->user) ? 'Following' : 'Follow' }}
                </button>
            @endif
        </div>

        @if($post->content)
            <div class="content">{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content) !!}</div>
        @endif

        @if($post->media && $post->media->count() > 0)
            <div class="post-media" style="margin: 15px 0;">
                @if($post->media->count() === 1)
                    @php $media = $post->media->first(); @endphp
                @if($media->media_type === 'image')
                    <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" loading="lazy" style="width: 100%; height: auto; border-radius: 12px; display: block;">
                @elseif($media->media_type === 'video')
                    <div class="video-container">
                        <video controls preload="metadata" poster="" style="width: 100%; height: auto; display: block; border-radius: 12px;">
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
                    <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                        @foreach($post->media as $media)
                            <div class="media-item">
                                @if($media->media_type === 'image')
                                    <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" style="width: 100%; height: 250px; object-fit: cover; border-radius: 10px;">
                                @elseif($media->media_type === 'video')
                                    <video controls style="width: 100%; height: 250px; object-fit: cover; border-radius: 10px;">
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

        <div style="margin-top: 15px;">
            <button type="button"
                    class="btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}"
                    onclick="toggleLike({{ $post->id }}, this)"
                    style="background: {{ $post->likedBy(auth()->user()) ? 'red' : 'var(--twitter-blue)' }};">
                <i class="fas fa-heart"></i> <span class="like-count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button"
                    class="btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}"
                    onclick="toggleSave({{ $post->id }}, this)"
                    style="margin-left: 10px; background: {{ $post->savedBy(auth()->user()) ? '#17a2b8' : '#6c757d' }};">
                <i class="fas fa-bookmark"></i> <span class="save-text">{{ $post->savedBy(auth()->user()) ? 'Saved' : 'Save' }}</span>
            </button>
            @if($post->user_id === auth()->id())
            <button type="button" class="btn" style="background: red; margin-left: 10px;" onclick="deletePost({{ $post->id }}, this)">Delete Post</button>
            @endif
        </div>
    </div>

    <hr>

    <div class="comments-section">
        <h3>Comments ({{ $post->comments->count() }})</h3>

        @if(auth()->check())
        <div class="comment-form-container" style="margin-bottom: 20px;">
            <textarea id="comment-content-{{ $post->id }}" placeholder="Write a comment..." maxlength="280" required style="width: 100%; padding: 10px; border: 1px solid #E1E8ED; border-radius: 5px; min-height: 80px;"></textarea>
            <button type="button" class="btn" style="margin-top: 10px;" onclick="submitComment({{ $post->id }})">Post Comment</button>
        </div>
        @endif

        <div class="comments-container">
            @forelse($post->comments as $comment)
                @include('partials.comment', ['comment' => $comment])
            @empty
                <p style="color: #666; font-style: italic;">No comments yet. Be the first to comment!</p>
            @endforelse
        </div>
    </div>
</div>

<style>
.post-detail {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px;
}

.back-link {
    margin-bottom: 16px;
}

.back-link a {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s ease;
}

.back-link a:hover {
    color: var(--twitter-dark);
}

.post {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid var(--border-color);
    margin-bottom: 24px;
    transition: box-shadow 0.2s ease;
}

.post:hover {
    box-shadow: var(--shadow);
}

.user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.user a {
    color: var(--twitter-dark);
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: color 0.2s ease;
}

.user a:hover {
    color: var(--twitter-blue);
}

.user small {
    color: var(--twitter-gray);
    font-size: 12px;
}

.follow-btn {
    margin-left: auto;
    padding: 4px 8px;
    font-size: 11px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.follow-btn:not(.following) {
    background: var(--twitter-blue);
    color: white;
}

.follow-btn.following {
    background: #28a745;
    color: white;
}

.content {
    margin: 16px 0;
    line-height: 1.6;
    font-size: 18px;
    color: var(--twitter-dark);
}

.post-media {
    margin: 20px 0;
}

.post-media img,
.post-media video {
    border-radius: 12px;
    display: block;
}

.media-grid {
    display: grid;
    gap: 12px;
    margin-top: 16px;
}

.media-item img,
.media-item video {
    border-radius: 10px;
    width: 100%;
    display: block;
}

.video-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.video-overlay:hover {
    background: rgba(0, 0, 0, 0.2);
}

.play-button {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.play-button:hover {
    transform: scale(1.1);
    background: white;
}

.play-button i {
    color: var(--twitter-blue);
    font-size: 20px;
    margin-left: 3px;
}

.post .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 8px;
    margin-bottom: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.like-btn {
    background: var(--twitter-blue);
    color: white;
}

.like-btn.liked {
    background: #dc3545;
}

.save-btn {
    background: #6c757d;
    color: white;
}

.save-btn.saved {
    background: #17a2b8;
}

hr {
    border: none;
    border-top: 1px solid var(--border-color);
    margin: 24px 0;
}

.comments-section {
    margin-top: 24px;
}

.comments-section h3 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.comment-form-container {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid var(--border-color);
}

.comment-form-container textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 12px;
    transition: border-color 0.2s ease;
}

.comment-form-container textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
}

.comment-form-container .btn {
    padding: 8px 16px;
    background: var(--twitter-blue);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-form-container .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.comments-container {
    margin-top: 20px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .post-detail {
        padding: 12px;
    }

    .post {
        padding: 16px;
        margin-bottom: 20px;
    }

    .user {
        gap: 6px;
    }

    .user a {
        font-size: 15px;
    }

    .content {
        font-size: 16px;
        margin: 14px 0;
    }

    .post-media {
        margin: 16px 0;
    }

    .media-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .media-item img,
    .media-item video {
        height: 200px;
    }

    .play-button {
        width: 50px;
        height: 50px;
    }

    .play-button i {
        font-size: 16px;
    }

    .comments-section h3 {
        font-size: 18px;
        margin-bottom: 16px;
    }

    .comment-form-container {
        padding: 12px;
    }

    .comment-form-container textarea {
        font-size: 13px;
        min-height: 60px;
    }
}

@media (max-width: 480px) {
    .post-detail {
        padding: 8px;
    }

    .post {
        padding: 12px;
        border-radius: 8px;
    }

    .user {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .follow-btn {
        margin-left: 0;
        margin-top: 4px;
        align-self: flex-start;
    }

    .content {
        font-size: 15px;
        margin: 12px 0;
    }

    .post .btn {
        padding: 6px 12px;
        font-size: 13px;
        margin-right: 6px;
        margin-bottom: 6px;
    }

    .media-grid {
        grid-template-columns: 1fr;
    }

    .media-item img,
    .media-item video {
        height: 180px;
        border-radius: 8px;
    }

    .play-button {
        width: 44px;
        height: 44px;
    }

    .play-button i {
        font-size: 14px;
    }

    hr {
        margin: 20px 0;
    }

    .comments-section {
        margin-top: 20px;
    }

    .comments-section h3 {
        font-size: 16px;
    }

    .comment-form-container {
        padding: 10px;
    }

    .comment-form-container textarea {
        font-size: 12px;
        min-height: 50px;
        padding: 8px;
    }

    .comment-form-container .btn {
        padding: 6px 12px;
        font-size: 13px;
    }
}

@media (max-width: 360px) {
    .post-detail {
        padding: 6px;
    }

    .post {
        padding: 8px;
    }

    .content {
        font-size: 14px;
    }

    .post .btn {
        padding: 4px 8px;
        font-size: 12px;
    }

    .media-item img,
    .media-item video {
        height: 150px;
    }

    .comments-section h3 {
        font-size: 15px;
    }
}
</style>
@endsection
