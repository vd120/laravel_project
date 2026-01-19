@extends('layouts.app')

@section('title', $post->user->name . ' on Laravel Social')

@section('content')
<div class="post-detail-page">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="{{ url()->previous() }}" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </a>
    </div>

    <!-- Main Post Content -->
    <div class="post-container">
        <article class="main-post" data-post-id="{{ $post->id }}">
            <!-- Post Header -->
            <header class="post-header">
                <div class="post-author">
                    <div class="author-avatar">
                        @if($post->user->profile && $post->user->profile->avatar)
                            <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="{{ $post->user->name }}" loading="lazy">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="author-info">
                        <h2 class="author-name">
                            <a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a>
                        </h2>
                        <time class="post-time" datetime="{{ $post->created_at->toISOString() }}">
                            {{ $post->created_at->diffForHumans() }}
                        </time>
                    </div>
                </div>

                <!-- Follow Button (if not own post) -->
                @if($post->user_id !== auth()->id())
                    <div class="post-actions">
                        <button type="button"
                                class="follow-button {{ auth()->user()->isFollowing($post->user) ? 'following' : '' }}"
                                data-user-id="{{ $post->user->id }}"
                                onclick="toggleFollow(this, {{ $post->user->id }})">
                            <span class="follow-text">{{ auth()->user()->isFollowing($post->user) ? 'Following' : 'Follow' }}</span>
                        </button>
                    </div>
                @endif
            </header>

            <!-- Post Content -->
            @if($post->content)
                <div class="post-content">
                    <div class="content-text">
                        {!! app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content) !!}
                    </div>
                </div>
            @endif

            <!-- Post Media -->
            @if($post->media && $post->media->count() > 0)
                <div class="post-media-section">
                    @if($post->media->count() === 1)
                        @php $media = $post->media->first(); @endphp
                        @if($media->media_type === 'image')
                            <div class="single-media">
                                <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post media" loading="lazy">
                            </div>
                        @elseif($media->media_type === 'video')
                            <div class="single-media video-wrapper">
                                <video controls preload="metadata" poster="">
                                    <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @endif
                    @else
                        <div class="media-grid">
                            @foreach($post->media as $media)
                                <div class="media-item">
                                    @if($media->media_type === 'image')
                                        <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post media" loading="lazy">
                                    @elseif($media->media_type === 'video')
                                        <video controls preload="metadata">
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

            <!-- Post Actions -->
            <div class="post-actions-bar">
                <div class="action-buttons">
                    <button type="button"
                            class="action-btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}"
                            onclick="toggleLike('{{ $post->slug }}', this)"
                            aria-label="Like post">
                        <i class="fas fa-heart"></i>
                        <span class="action-count">{{ $post->likes->count() }}</span>
                    </button>

                    <button type="button"
                            class="action-btn comment-btn"
                            onclick="focusCommentForm()"
                            aria-label="Comment on post">
                        <i class="fas fa-comment"></i>
                        <span class="action-count">{{ $post->comments->count() }}</span>
                    </button>

                    <button type="button"
                            class="action-btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}"
                            onclick="toggleSave('{{ $post->slug }}', this)"
                            aria-label="Save post">
                        <i class="fas fa-bookmark"></i>
                        <span class="save-text">{{ $post->savedBy(auth()->user()) ? 'Saved' : 'Save' }}</span>
                    </button>

                    <button type="button"
                            class="action-btn share-btn"
                            onclick="copyPostLink('{{ $post->slug }}')"
                            aria-label="Share post">
                        <i class="fas fa-share"></i>
                        <span class="share-text">Share</span>
                    </button>
                </div>

                <!-- Delete Button (own posts only) -->
                @if($post->user_id === auth()->id())
                    <div class="post-owner-actions">
                        <button type="button"
                                class="delete-btn"
                                onclick="deletePost('{{ $post->slug }}', this)"
                                aria-label="Delete post">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
            </div>
        </article>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <div class="comments-header">
            <h3 class="comments-title">
                <i class="fas fa-comments"></i>
                Comments <span class="comment-count">({{ $post->comments->count() }})</span>
            </h3>
        </div>

        <!-- Comment Form -->
        @if(auth()->check())
            <div class="comment-form-wrapper">
                <form class="comment-form" onsubmit="submitComment(event, {{ $post->id }})">
                    <div class="comment-input-area">
                        <div class="comment-avatar">
                            @if(auth()->user()->profile && auth()->user()->profile->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="avatar-placeholder small">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="comment-input-wrapper">
                            <textarea id="comment-content-{{ $post->id }}"
                                      name="content"
                                      placeholder="Write a comment..."
                                      maxlength="280"
                                      required
                                      class="comment-textarea"></textarea>
                            <div class="comment-actions">
                                <button type="submit" class="comment-submit-btn">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Comment</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Comments List -->
        <div class="comments-list">
            @forelse($post->comments as $comment)
                @include('partials.comment', ['comment' => $comment])
            @empty
                <div class="no-comments">
                    <div class="no-comments-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4>No comments yet</h4>
                    <p>Be the first to share your thoughts!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* Modern Post Detail Page Styles */
.post-detail-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

/* Page Header */
.page-header {
    margin-bottom: 24px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    color: var(--twitter-dark);
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.back-button:hover {
    background: var(--hover-bg);
    transform: translateX(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.back-button i {
    font-size: 12px;
}

/* Main Post Container */
.post-container {
    margin-bottom: 32px;
}

.main-post {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease;
}

.main-post:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

/* Post Header */
.post-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 20px 16px 20px;
    border-bottom: 1px solid var(--border-color);
}

.post-author {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--border-color);
    flex-shrink: 0;
}

.author-avatar img,
.avatar-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.author-info {
    flex: 1;
    min-width: 0;
}

.author-name {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.author-name a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.author-name a:hover {
    color: var(--twitter-blue);
}

.post-time {
    font-size: 14px;
    color: var(--twitter-gray);
    margin: 0;
}

.post-actions {
    flex-shrink: 0;
}

.follow-button {
    padding: 6px 16px;
    border: 1px solid var(--twitter-blue);
    border-radius: 20px;
    background: transparent;
    color: var(--twitter-blue);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.follow-button:not(.following):hover {
    background: var(--twitter-blue);
    color: white;
}

.follow-button.following {
    background: var(--success-color);
    border-color: var(--success-color);
    color: white;
}

/* Post Content */
.post-content {
    padding: 20px;
}

.content-text {
    font-size: 18px;
    line-height: 1.6;
    color: var(--twitter-dark);
    margin: 0;
    word-wrap: break-word;
}

/* Post Media */
.post-media-section {
    position: relative;
}

.single-media {
    position: relative;
    width: 100%;
}

.single-media img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 0;
}

.video-wrapper {
    background: #000;
}

.video-wrapper video {
    width: 100%;
    height: auto;
    display: block;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2px;
    background: var(--border-color);
}

.media-item {
    position: relative;
    background: #000;
    aspect-ratio: 1;
    overflow: hidden;
}

.media-item img,
.media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Post Actions */
.post-actions-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-top: 1px solid var(--border-color);
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 16px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: var(--twitter-gray);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.action-btn i {
    font-size: 16px;
}

.action-count {
    font-weight: 600;
}

.like-btn.liked {
    color: var(--error-color);
}

.like-btn.liked:hover {
    background: rgba(244, 33, 46, 0.1);
}

.save-btn.saved {
    color: var(--success-color);
}

.save-btn.saved:hover {
    background: rgba(0, 186, 124, 0.1);
}

.share-btn:hover {
    color: var(--twitter-blue);
}

.post-owner-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.delete-btn {
    padding: 8px;
    border: none;
    background: transparent;
    color: var(--twitter-gray);
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.delete-btn:hover {
    background: rgba(244, 33, 46, 0.1);
    color: var(--error-color);
}

.delete-btn i {
    font-size: 16px;
}

/* Comments Section */
.comments-section {
    border-top: 1px solid var(--border-color);
    padding-top: 24px;
}

.comments-header {
    margin-bottom: 20px;
}

.comments-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.comments-title i {
    color: var(--twitter-blue);
    font-size: 20px;
}

.comment-count {
    color: var(--twitter-gray);
    font-weight: 500;
}

/* Comment Form */
.comment-form-wrapper {
    margin-bottom: 24px;
}

.comment-form {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.comment-input-area {
    display: flex;
    gap: 12px;
    padding: 16px;
}

.comment-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.comment-avatar img,
.avatar-placeholder.small {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder.small {
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.comment-input-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.comment-textarea {
    width: 100%;
    min-height: 60px;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 20px;
    font-family: inherit;
    font-size: 15px;
    line-height: 1.4;
    resize: none;
    transition: border-color 0.2s ease;
    background: var(--input-bg);
    color: var(--twitter-dark);
}

.comment-textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    background: var(--card-bg);
}

.comment-textarea::placeholder {
    color: var(--twitter-gray);
}

.comment-actions {
    display: flex;
    justify-content: flex-end;
}

.comment-submit-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: var(--twitter-blue);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-submit-btn:hover {
    background: #1991DB;
    transform: translateY(-1px);
}

.comment-submit-btn i {
    font-size: 12px;
}

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* No Comments State */
.no-comments {
    text-align: center;
    padding: 40px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    color: var(--twitter-gray);
}

.no-comments-icon {
    margin-bottom: 16px;
}

.no-comments-icon i {
    font-size: 32px;
    opacity: 0.5;
}

.no-comments h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.no-comments p {
    margin: 0;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .post-detail-page {
        padding: 16px;
    }

    .main-post {
        border-radius: 12px;
    }

    .post-header {
        padding: 16px;
    }

    .author-name {
        font-size: 16px;
    }

    .post-time {
        font-size: 13px;
    }

    .post-content {
        padding: 16px;
    }

    .content-text {
        font-size: 16px;
    }

    .post-actions-bar {
        padding: 12px 16px;
    }

    .comments-title {
        font-size: 18px;
    }

    .comment-input-area {
        padding: 12px;
    }
}

@media (max-width: 768px) {
    .post-detail-page {
        padding: 12px;
    }

    .page-header {
        margin-bottom: 20px;
    }

    .back-button {
        padding: 6px 12px;
        font-size: 13px;
    }

    .post-header {
        padding: 12px;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .post-author {
        width: 100%;
    }

    .post-actions {
        align-self: flex-end;
    }

    .follow-button {
        padding: 4px 12px;
        font-size: 13px;
    }

    .post-content {
        padding: 12px;
    }

    .content-text {
        font-size: 15px;
    }

    .action-buttons {
        gap: 12px;
    }

    .action-btn {
        padding: 6px 8px;
        font-size: 13px;
    }

    .action-btn i {
        font-size: 14px;
    }

    .comments-section {
        padding-top: 20px;
    }

    .comments-title {
        font-size: 16px;
    }

    .comment-input-area {
        flex-direction: column;
        gap: 8px;
    }

    .comment-avatar {
        align-self: flex-start;
    }

    .comment-textarea {
        font-size: 14px;
        min-height: 80px;
    }
}

@media (max-width: 480px) {
    .post-detail-page {
        padding: 8px;
    }

    .back-button {
        padding: 4px 8px;
        font-size: 12px;
    }

    .main-post {
        border-radius: 8px;
    }

    .post-header {
        padding: 8px;
    }

    .author-avatar {
        width: 40px;
        height: 40px;
    }

    .author-name {
        font-size: 15px;
    }

    .post-time {
        font-size: 12px;
    }

    .follow-button {
        padding: 3px 8px;
        font-size: 12px;
    }

    .post-content {
        padding: 8px;
    }

    .content-text {
        font-size: 14px;
    }

    .media-grid {
        grid-template-columns: 1fr;
        gap: 1px;
    }

    .post-actions-bar {
        padding: 8px;
    }

    .action-buttons {
        gap: 8px;
    }

    .action-btn {
        padding: 4px 6px;
        font-size: 12px;
    }

    .action-btn i {
        font-size: 12px;
    }

    .delete-btn {
        padding: 6px;
    }

    .comments-title {
        font-size: 15px;
    }

    .comment-input-area {
        padding: 8px;
    }

    .comment-avatar {
        width: 32px;
        height: 32px;
    }

    .comment-textarea {
        font-size: 13px;
        min-height: 60px;
        padding: 8px 12px;
    }

    .comment-submit-btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .no-comments {
        padding: 24px 16px;
    }

    .no-comments-icon i {
        font-size: 24px;
    }

    .no-comments h4 {
        font-size: 14px;
    }
}
</style>
@endsection