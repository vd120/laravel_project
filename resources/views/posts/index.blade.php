@extends('layouts.app')

@section('content')
<div style="padding: 20px 0;">

<!-- Stories Section -->
@if($followedUsersWithStories->count() > 0 || $myStories->count() > 0)
<div class="stories-section">
    <div class="stories-header">
        <h3>Stories</h3>
        <a href="{{ route('stories.index') }}" class="view-all-stories">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="stories-container">
        <!-- Your Story -->
        @if($myStories->count() > 0)
            @foreach($myStories as $story)
            <div class="story-item" data-story-id="{{ $story->id }}" onclick="viewStory('{{ auth()->user()->name }}', {{ $story->id }})">
                <div class="story-avatar">
                    @if(auth()->user()->profile && auth()->user()->profile->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="avatar-placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="story-preview">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="Your story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-info">
                    <span class="story-user">Your story</span>
                </div>
            </div>
            @endforeach
        @else
        <div class="story-item create-story" onclick="window.location.href='{{ route('stories.create') }}'">
            <div class="story-avatar">
                @if(auth()->user()->profile && auth()->user()->profile->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="avatar-placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
                @endif
                <div class="add-story-icon">
                    <i class="fas fa-plus"></i>
                </div>
            </div>
            <div class="story-info">
                <span class="story-user">Create story</span>
            </div>
        </div>
        @endif

        <!-- Friends' Stories -->
        @foreach($followedUsersWithStories as $user)
            @foreach($user->activeStories as $story)
            <div class="story-item" data-story-id="{{ $story->id }}" onclick="viewStory('{{ $user->name }}', {{ $story->id }})">
                <div class="story-avatar">
                    @if($user->profile && $user->profile->avatar)
                        <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="{{ "@" . $user->name }}">
                    @else
                        <div class="avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="story-preview">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
                <div class="story-info">
                    <span class="story-user">{{ "@" . $user->name }}</span>
                </div>
            </div>
            @endforeach
        @endforeach
    </div>
</div>
@endif

<h2>All Posts</h2>

<div class="post-form-container">
    <div class="form-group">
        <textarea id="post-content" placeholder="What's happening?" maxlength="280"></textarea>
    </div>
    <div class="form-group">
        <label for="media" style="cursor: pointer; display: inline-block; padding: 10px 16px; background: var(--card-bg); border-radius: 8px; border: 2px solid var(--border-color); transition: all 0.3s ease; color: var(--twitter-gray); font-weight: 500;" onmouseover="this.style.borderColor='var(--twitter-blue)'; this.style.background='var(--hover-bg)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--card-bg)'; this.style.transform='translateY(0)';">
            <i class="fas fa-image" style="margin-right: 8px;"></i> Add Image/Video
        </label>
        <input type="file" name="media[]" id="media" accept="image/*,video/*" multiple style="display: none;" onchange="previewMedia(this)">
        <span id="file-name" style="margin-left: 12px; font-size: 14px; color: var(--twitter-gray);"></span>
    </div>
    <div class="form-group">
        <button type="button" id="privacy-toggle" class="privacy-toggle-btn" onclick="togglePrivacy()">
            <i class="fas fa-globe" id="privacy-icon"></i>
            <span id="privacy-text">Public</span>
        </button>
        <input type="hidden" id="is-private" name="is_private" value="0">
        <small style="color: #666; font-size: 12px; display: block; margin-top: 8px;">
            <i class="fas fa-info-circle"></i> Private posts are only visible to your followers
        </small>
    </div>
    <div id="media-preview" style="margin: 10px 0; display: none;">
        <div id="media-previews" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
        <button type="button" onclick="removeMedia()" style="margin-top: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">Remove All Media</button>
    </div>
    <button type="button" class="btn" onclick="submitPost()">Post</button>
    <div id="post-errors" style="color: red; margin-top: 5px; display: none;"></div>
</div>

<div id="posts-container">
    @foreach($posts as $post)
        @include('partials.post', ['post' => $post])
    @endforeach
</div>

<!-- Loading indicator for infinite scroll -->
<div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
    <i class="fas fa-spinner fa-spin"></i> Loading more posts...
</div>

<!-- End of content indicator -->
<div id="end-of-content" style="display: none; text-align: center; padding: 20px; color: #666;">
    <i class="fas fa-check-circle"></i> You've seen all posts!
</div>
<style>
.stories-section {
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 20px;
}

.stories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.stories-header h3 {
    margin: 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.view-all-stories {
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.view-all-stories:hover {
    color: var(--twitter-dark);
}

.stories-container {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 10px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.stories-container::-webkit-scrollbar {
    display: none;
}

.story-item {
    flex-shrink: 0;
    width: 70px;
    cursor: pointer;
    position: relative;
}

.story-item:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.story-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid var(--twitter-blue);
    overflow: hidden;
    margin-bottom: 8px;
}

.story-avatar img {
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
    font-weight: bold;
    font-size: 24px;
}

.story-preview {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.3);
}

.story-preview img,
.story-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.create-story .story-avatar {
    border-color: var(--border-color);
    position: relative;
}

.add-story-icon {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 24px;
    height: 24px;
    background: var(--twitter-blue);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid white;
}

.story-info {
    text-align: center;
}

.story-user {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--twitter-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70px;
}

@media (max-width: 480px) {
    .stories-container {
        gap: 10px;
    }

    .story-item {
        width: 60px;
    }

    .story-avatar {
        width: 60px;
        height: 60px;
    }

    .story-preview {
        width: 50px;
        height: 50px;
        top: 5px;
        left: 5px;
    }
}

/* Privacy toggle button */
.privacy-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    position: relative;
    overflow: hidden;
}

.privacy-toggle-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.privacy-toggle-btn:hover::before {
    left: 100%;
}

.privacy-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
}

.privacy-toggle-btn.private {
    background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
    box-shadow: 0 2px 8px rgba(156, 39, 176, 0.3);
}

.privacy-toggle-btn.private:hover {
    box-shadow: 0 4px 15px rgba(156, 39, 176, 0.4);
}

.privacy-toggle-btn i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

.privacy-toggle-btn.private i {
    transform: rotate(180deg);
}

/* Post Styling for Dark Theme */
.post {
    margin-bottom: 20px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 16px;
    border: 2px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.post:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.post .user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.post .user a {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 600;
}

.post .user a:hover {
    text-decoration: underline;
}

.post .user small {
    color: var(--twitter-gray);
    font-size: 12px;
}

.privacy-badge.private {
    background: var(--error-color);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
    margin-left: 4px;
}

.post .content {
    margin: 12px 0;
    line-height: 1.5;
    font-size: 16px;
    color: var(--twitter-dark);
}

.post .btn {
    padding: 8px 14px;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 6px;
    margin-bottom: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.like-btn {
    background: var(--twitter-blue);
    color: white;
}

.like-btn:hover {
    background: #1A91DA;
    transform: translateY(-1px);
}

.like-btn.liked {
    background: var(--error-color);
}

.like-btn.liked:hover {
    background: #E0245E;
}

.save-btn {
    background: #6c757d;
    color: white;
}

.save-btn:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.save-btn.saved {
    background: #17a2b8;
}

.save-btn.saved:hover {
    background: #138496;
}

.post hr {
    border: none;
    border-top: 1px solid var(--border-color);
    margin: 16px 0;
}

.post h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.comment-form-container {
    margin: 16px 0;
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.comment-form-container textarea {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    min-height: 60px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    transition: all 0.3s ease;
}

.comment-form-container textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
    transform: translateY(-1px);
}

.comment-form-container .btn {
    padding: 8px 14px;
    font-size: 12px;
    background: var(--twitter-blue);
    color: white;
}

.comment-form-container .btn:hover {
    background: #1A91DA;
    transform: translateY(-1px);
}

/* Video Container Styling */
.video-container {
    position: relative;
    display: inline-block;
    width: 100%;
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
    background: rgba(0, 0, 0, 0.4);
}

.play-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.play-button:hover {
    transform: scale(1.1);
    background: white;
}

.play-button i {
    color: var(--twitter-blue);
    font-size: 20px;
    margin-left: 2px;
}

.video-container.playing .video-overlay {
    display: none;
}

/* Media Grid */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px;
}

.media-item img,
.media-item video {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    display: block;
}

/* Loading and End Content */
#loading-indicator,
#end-of-content {
    text-align: center;
    padding: 20px;
    color: var(--twitter-gray);
    font-size: 14px;
}

#loading-indicator i {
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .post {
        padding: 12px;
        margin-bottom: 16px;
    }

    .post .user {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .post .content {
        font-size: 15px;
    }

    .comment-form-container {
        flex-direction: column;
        gap: 8px;
    }

    .comment-form-container textarea {
        font-size: 13px;
        min-height: 50px;
    }

    .media-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 6px;
    }

    .media-item img,
    .media-item video {
        height: 150px;
    }
}

/* Clean Comment Design */
.comment {
    margin-bottom: 8px;
    padding: 8px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.comment.main-comment {
    border-left: 2px solid var(--twitter-blue);
    padding-left: 10px;
}

/* Twitter-like Comments Design */
.comment {
    margin-bottom: 12px;
    padding: 12px 16px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    transition: all 0.2s ease;
    position: relative;
}

.comment:hover {
    background: var(--hover-bg);
    border-color: rgba(29, 161, 242, 0.2);
}

.comment.main-comment {
    border-left: 3px solid var(--twitter-blue);
    padding-left: 20px;
}

.comment.nested-comment {
    margin-left: 24px;
    background: rgba(29, 161, 242, 0.02);
    border: 1px solid rgba(29, 161, 242, 0.1);
}

.comment.nested-comment::before {
    content: '';
    position: absolute;
    left: -28px;
    top: 20px;
    width: 20px;
    height: 2px;
    background: var(--border-color);
}

.comment.level-1 {
    margin-left: 28px;
}

.comment.level-2 {
    margin-left: 52px;
}

.comment.level-3 {
    margin-left: 76px;
}

/* Comment Avatar */
.comment-avatar {
    position: absolute;
    top: 16px;
    left: 16px;
}

.comment-user-avatar,
.comment-user-avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid var(--border-color);
}

.comment-user-avatar {
    object-fit: cover;
}

.comment-user-avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 14px;
}

.main-comment .comment-avatar {
    left: 20px;
}

/* Comment Content Wrapper */
.comment-content-wrapper {
    margin-left: 52px;
}

/* Comment Header */
.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.comment-user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.comment-user-name {
    font-weight: 600;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.comment-user-name:hover {
    color: var(--twitter-dark);
    text-decoration: underline;
}

.comment-time {
    font-size: 12px;
    color: var(--twitter-gray);
}

.comment-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.comment:hover .comment-actions {
    opacity: 1;
}

.comment-delete-btn {
    background: none;
    border: none;
    color: var(--error-color);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 12px;
}

.comment-delete-btn:hover {
    background: rgba(244, 33, 46, 0.1);
    transform: scale(1.1);
}

/* Comment Body */
.comment-body {
    margin-bottom: 12px;
}

.comment-text {
    line-height: 1.5;
    color: var(--twitter-dark);
    font-size: 14px;
    word-wrap: break-word;
}

/* Comment Footer */
.comment-footer {
    border-top: 1px solid var(--border-color);
    padding-top: 8px;
}

.comment-interactions {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* Like Button */
.comment-like-btn {
    background: none;
    border: none;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s ease;
    position: relative;
}

.comment-like-btn i {
    font-size: 14px;
    transition: all 0.2s ease;
}

.comment-like-count {
    font-size: 12px;
    font-weight: 600;
}

/* Reply Button */
.comment-reply-btn {
    background: none;
    border: none;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}

.comment-reply-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    color: var(--twitter-blue);
}

.comment-reply-btn i {
    font-size: 11px;
}

/* Reply Form */
.comment-reply-form {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
}

.reply-form-container {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.reply-avatar {
    flex-shrink: 0;
}

.reply-avatar img,
.reply-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid var(--border-color);
}

.reply-avatar img {
    object-fit: cover;
}

.reply-avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 12px;
}

.reply-input-container {
    flex: 1;
    min-width: 0;
}

.reply-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 16px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    min-height: 60px;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.reply-textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
    transform: translateY(-1px);
}

.reply-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.reply-submit-btn,
.reply-cancel-btn {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.reply-submit-btn {
    background: var(--twitter-blue);
    color: white;
    border: none;
}

.reply-submit-btn:hover {
    background: #1A91DA;
    transform: translateY(-1px);
}

.reply-cancel-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
}

.reply-cancel-btn:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

/* Comment Replies */
.comment-replies {
    margin-top: 12px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
}

/* Mobile Responsive Comments - Enhanced */
@media (max-width: 768px) {
    .comment {
        padding: 12px;
        margin-bottom: 14px;
        border-radius: 10px;
    }

    .comment.main-comment {
        padding-left: 16px;
    }

.comment.nested-comment {
    margin-left: 24px;
    margin-bottom: 6px;
    padding: 8px 10px;
    border-left: 2px solid var(--border-color);
    background: rgba(255, 255, 255, 0.02);
    border-radius: 4px;
}

.comment.level-1 {
    border-left-color: rgba(29, 161, 242, 0.3);
}

.comment.level-2 {
    margin-left: 36px;
    border-left-color: rgba(29, 161, 242, 0.25);
}

.comment.level-3 {
    margin-left: 48px;
    border-left-color: rgba(29, 161, 242, 0.2);
}

    .comment-content-wrapper {
        margin-left: 48px;
    }

    .comment-header {
        margin-bottom: 6px;
        flex-wrap: wrap;
        gap: 4px;
    }

    .comment-user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }

    .comment-user-name {
        font-size: 13px;
        font-weight: 600;
    }

    .comment-time {
        font-size: 11px;
        opacity: 0.8;
    }

    .comment-actions {
        margin-left: auto;
        margin-top: -2px;
    }

    .comment-delete-btn {
        padding: 4px;
        font-size: 12px;
    }

    .comment-body {
        margin-bottom: 10px;
    }

    .comment-text {
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
    }

    .comment-footer {
        padding-top: 10px;
    }

    .comment-interactions {
        gap: 12px;
        flex-wrap: wrap;
    }

    .comment-like-btn,
    .comment-reply-btn {
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 18px;
        min-height: 32px;
    }

    .comment-like-btn i,
    .comment-reply-btn i {
        font-size: 11px;
    }

    .reply-form-container {
        gap: 10px;
        flex-direction: column;
    }

    .reply-avatar img,
    .reply-avatar-placeholder {
        width: 30px;
        height: 30px;
    }

    .reply-input-container {
        flex: 1;
    }

    .reply-textarea {
        font-size: 14px;
        min-height: 60px;
        padding: 12px 14px;
        border-radius: 18px;
    }

    .reply-actions {
        gap: 8px;
        flex-direction: row;
        justify-content: flex-end;
    }

    .reply-submit-btn,
    .reply-cancel-btn {
        padding: 8px 14px;
        font-size: 12px;
        border-radius: 16px;
        min-height: 36px;
    }

    .comment-replies {
        padding-left: 16px;
        margin-top: 12px;
    }

    .comment-replies::before {
        left: 16px;
    }

    /* Show More/Hide Buttons - Mobile */
    .show-more-comments-container,
    .hide-comments-container {
        margin: 10px 0;
    }

    .show-more-comments-btn,
    .hide-comments-btn {
        padding: 10px 16px;
        font-size: 13px;
        border-radius: 20px;
        width: 100%;
        justify-content: center;
    }

    .show-more-replies-container,
    .hide-replies-container {
        margin: 8px 0 8px 20px;
    }

    .show-more-replies-btn,
    .hide-replies-btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 16px;
    }
}

@media (max-width: 480px) {
    .comment {
        padding: 10px;
        margin-bottom: 12px;
    }

    .comment.nested-comment {
        margin-left: 20px;
        padding: 8px 10px;
    }

    .comment-avatar {
        top: 10px;
        left: 10px;
    }

    .comment-user-avatar,
    .comment-user-avatar-placeholder {
        width: 32px;
        height: 32px;
    }

    .main-comment .comment-avatar {
        left: 14px;
    }

    .comment-content-wrapper {
        margin-left: 44px;
    }

    .comment-user-name {
        font-size: 12px;
    }

    .comment-time {
        font-size: 10px;
    }

    .comment-text {
        font-size: 12px;
        line-height: 1.4;
    }

    .comment-interactions {
        gap: 8px;
    }

    .comment-like-btn,
    .comment-reply-btn {
        padding: 5px 8px;
        font-size: 11px;
        min-height: 28px;
    }

    .comment-like-count {
        font-size: 11px;
    }

    .reply-form-container {
        gap: 8px;
    }

    .reply-avatar img,
    .reply-avatar-placeholder {
        width: 28px;
        height: 28px;
    }

    .reply-textarea {
        font-size: 13px;
        min-height: 50px;
        padding: 10px 12px;
    }

    .reply-submit-btn,
    .reply-cancel-btn {
        padding: 6px 12px;
        font-size: 11px;
        min-height: 32px;
    }

    .comment-replies {
        padding-left: 12px;
        margin-top: 10px;
    }

    .comment-replies::before {
        left: 12px;
    }

    /* Better touch targets for mobile */
    .comment-like-btn,
    .comment-reply-btn,
    .reply-submit-btn,
    .reply-cancel-btn,
    .comment-delete-btn {
        min-height: 32px;
        min-width: 32px;
    }

    /* Full width buttons on very small screens */
    .show-more-comments-btn,
    .hide-comments-btn {
        padding: 12px 16px;
        font-size: 14px;
    }
}

/* Tablet specific adjustments */
@media (min-width: 481px) and (max-width: 768px) {
    .comment {
        padding: 14px;
    }

    .comment.nested-comment {
        margin-left: 32px;
    }

    .comment-content-wrapper {
        margin-left: 52px;
    }

    .comment-interactions {
        gap: 16px;
    }
}

/* Landscape mobile adjustments */
@media (max-height: 500px) and (orientation: landscape) {
    .comment {
        padding: 8px;
        margin-bottom: 10px;
    }

    .comment-text {
        font-size: 12px;
        line-height: 1.3;
        max-height: 60px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
}

/* Show More Comments Styling */
.show-more-comments-container {
    text-align: center;
    margin: 12px 0;
    padding: 8px 0;
}

.show-more-comments-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-blue);
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    margin: 0 auto;
}

.show-more-comments-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    transform: translateY(-1px);
}

.show-more-comments-btn i {
    font-size: 11px;
    transition: transform 0.2s ease;
}

.show-more-comments-btn:hover i {
    transform: translateY(1px);
}

.comments-hidden {
    animation: fadeInComments 0.3s ease-in-out;
}

@keyframes fadeInComments {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Show More Replies Styling */
.show-more-replies-container {
    margin: 8px 0 8px 24px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
}

.show-more-replies-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
    padding: 6px 12px;
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    margin: 0;
}

.show-more-replies-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    color: var(--twitter-blue);
    transform: translateY(-1px);
}

.show-more-replies-btn i {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.show-more-replies-btn:hover i {
    transform: translateY(1px);
}

.replies-hidden {
    animation: fadeInReplies 0.3s ease-in-out;
}

@keyframes fadeInReplies {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hide Replies Styling */
.hide-replies-container {
    margin: 8px 0 8px 24px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
}

.hide-replies-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
    padding: 6px 12px;
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    margin: 0;
}

.hide-replies-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    color: var(--twitter-blue);
    transform: translateY(-1px);
}

.hide-replies-btn i {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.hide-replies-btn:hover i {
    transform: translateY(-1px);
}

/* Hide Comments Styling */
.hide-comments-container {
    text-align: center;
    margin: 12px 0;
    padding: 8px 0;
}

.hide-comments-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    margin: 0 auto;
}

.hide-comments-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    color: var(--twitter-blue);
    transform: translateY(-1px);
}

.hide-comments-btn i {
    font-size: 11px;
    transition: transform 0.2s ease;
}

.hide-comments-btn:hover i {
    transform: translateY(-1px);
}

/* Show More Nested Replies Styling */
.show-more-nested-replies-container {
    margin: 10px 0 10px 20px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
    position: relative;
}

.show-more-nested-replies-container::before {
    content: '';
    position: absolute;
    left: -18px;
    top: 15px;
    width: 12px;
    height: 2px;
    background: var(--border-color);
    opacity: 0.4;
}

.show-more-nested-replies-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
    padding: 6px 12px;
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    margin: 0;
}

.show-more-nested-replies-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    color: var(--twitter-blue);
    transform: translateY(-1px);
}

.show-more-nested-replies-btn i {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.show-more-nested-replies-btn:hover i {
    transform: translateY(1px);
}

/* Hide Nested Replies Styling */
.hide-nested-replies-container {
    margin: 10px 0 10px 20px;
    padding-left: 16px;
    border-left: 2px solid var(--border-color);
    position: relative;
}

.hide-nested-replies-container::before {
    content: '';
    position: absolute;
    left: -18px;
    top: 15px;
    width: 12px;
    height: 2px;
    background: var(--border-color);
    opacity: 0.4;
}

.hide-nested-replies-btn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--twitter-gray);
    padding: 6px 12px;
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    margin: 0;
}

.hide-nested-replies-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    border-color: var(--twitter-blue);
    color: var(--twitter-blue);
    transform: translateY(-1px);
}

.hide-nested-replies-btn i {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.hide-nested-replies-btn:hover i {
    transform: translateY(-1px);
}
</style>

<script>
function viewStory(userId, storyId) {
    window.location.href = '{{ url("/stories") }}/' + userId + '?story=' + storyId;
}

function previewMedia(input) {
    if (input.files && input.files.length > 0) {
        const previewDiv = document.getElementById('media-preview');
        const mediaPreviews = document.getElementById('media-previews');

        // Clear previous previews
        mediaPreviews.innerHTML = '';

        // Show file count
        const fileCount = input.files.length;
        document.getElementById('file-name').textContent = `${fileCount} file${fileCount > 1 ? 's' : ''} selected`;

        // Create previews for each file
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.createElement('div');
                previewContainer.style.cssText = 'position: relative; display: inline-block; margin: 5px;';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #E1E8ED;';
                    img.alt = `Preview ${index + 1}`;
                    previewContainer.appendChild(img);
                } else if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = e.target.result;
                    video.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #E1E8ED;';
                    video.muted = true;
                    video.preload = 'metadata';
                    previewContainer.appendChild(video);
                }

                // Add remove button for individual files
                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '×';
                removeBtn.style.cssText = 'position: absolute; top: -5px; right: -5px; background: rgba(220, 53, 69, 0.9); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 1;';
                removeBtn.onclick = function() {
                    previewContainer.remove();
                    updateFileCount();
                };
                previewContainer.appendChild(removeBtn);

                mediaPreviews.appendChild(previewContainer);
            };
            reader.readAsDataURL(file);
        });

        previewDiv.style.display = 'block';
    }
}

function removeMedia() {
    const input = document.getElementById('media');
    const previewDiv = document.getElementById('media-preview');

    // Clear the input
    input.value = '';

    // Hide previews
    previewDiv.style.display = 'none';

    // Clear file name
    document.getElementById('file-name').textContent = '';
}

function updateFileCount() {
    const mediaPreviews = document.getElementById('media-previews');
    const remainingPreviews = mediaPreviews.children.length;

    if (remainingPreviews === 0) {
        document.getElementById('media-preview').style.display = 'none';
        document.getElementById('file-name').textContent = '';
        document.getElementById('media').value = '';
    } else {
        document.getElementById('file-name').textContent = `${remainingPreviews} file${remainingPreviews > 1 ? 's' : ''} selected`;
    }
}

function togglePrivacy() {
    const button = document.getElementById('privacy-toggle');
    const icon = document.getElementById('privacy-icon');
    const text = document.getElementById('privacy-text');
    const hiddenInput = document.getElementById('is-private');

    if (button.classList.contains('private')) {
        // Switch to public
        button.classList.remove('private');
        icon.className = 'fas fa-globe';
        text.textContent = 'Public';
        hiddenInput.value = '0';
    } else {
        // Switch to private
        button.classList.add('private');
        icon.className = 'fas fa-lock';
        text.textContent = 'Private';
        hiddenInput.value = '1';
    }
}

// Submit a new post via AJAX
function submitPost() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const contentTextarea = document.getElementById('post-content');
    const mediaInput = document.getElementById('media');
    const isPrivateInput = document.getElementById('is-private');
    const submitButton = document.querySelector('.post-form-container .btn');
    const errorDiv = document.getElementById('post-errors');

    const content = contentTextarea.value.trim();
    const isPrivate = isPrivateInput.value;

    // Validation
    if (!content && (!mediaInput.files || mediaInput.files.length === 0)) {
        errorDiv.textContent = 'Please provide either text content or media.';
        errorDiv.style.display = 'block';
        return;
    }

    // Disable form during submission
    contentTextarea.disabled = true;
    mediaInput.disabled = true;
    submitButton.disabled = true;
    submitButton.textContent = 'Posting...';
    errorDiv.style.display = 'none';

    // Prepare form data
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('content', content);
    formData.append('is_private', isPrivate);

    // Add media files
    if (mediaInput.files && mediaInput.files.length > 0) {
        Array.from(mediaInput.files).forEach((file, index) => {
            formData.append(`media[${index}]`, file);
        });
    }

    fetch('/posts', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Post created successfully');

            // Clear the form
            contentTextarea.value = '';
            mediaInput.value = '';
            // Reset privacy button to public
            const privacyButton = document.getElementById('privacy-toggle');
            const privacyIcon = document.getElementById('privacy-icon');
            const privacyText = document.getElementById('privacy-text');
            const isPrivateInput = document.getElementById('is-private');
            privacyButton.classList.remove('private');
            privacyIcon.className = 'fas fa-globe';
            privacyText.textContent = 'Public';
            isPrivateInput.value = '0';
            removeMedia(); // Clear media previews

            // Real-time events will handle adding the post to the UI
            // For now, we'll reload to show the new post
            window.location.reload();

        } else {
            console.error('Post creation failed');
            errorDiv.textContent = data.message || 'Failed to create post.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error creating post:', error);
        errorDiv.textContent = 'An error occurred while creating the post.';
        errorDiv.style.display = 'block';
    })
    .finally(() => {
        // Re-enable form
        contentTextarea.disabled = false;
        mediaInput.disabled = false;
        submitButton.disabled = false;
        submitButton.textContent = 'Post';
    });
}

// Video play functionality
function playVideo(overlay) {
    const container = overlay.parentElement;
    const video = container.querySelector('video');

    if (video) {
        // Remove the overlay
        overlay.style.display = 'none';
        container.classList.add('playing');

        // Play the video
        video.play().catch(function(error) {
            console.log('Video play failed:', error);
        });

        // Add event listener to show overlay when video ends
        video.addEventListener('ended', function() {
            overlay.style.display = 'flex';
            container.classList.remove('playing');
        });

        // Add event listener to show overlay when video is paused
        video.addEventListener('pause', function() {
            if (!video.seeking) {
                overlay.style.display = 'flex';
                container.classList.remove('playing');
            }
        });

        // Add event listener to hide overlay when video plays
        video.addEventListener('play', function() {
            overlay.style.display = 'none';
            container.classList.add('playing');
        });
    }
}

// Infinite scroll functionality
let currentPage = {{ $posts->currentPage() }};
let isLoading = false;
let hasMorePosts = {{ $posts->hasMorePages() ? 'true' : 'false' }};
const postsPerPage = {{ $posts->perPage() }};

function loadMorePosts() {
    if (isLoading || !hasMorePosts) return;

    isLoading = true;
    currentPage++;

    // Show loading indicator
    document.getElementById('loading-indicator').style.display = 'block';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(`/api/posts?page=${currentPage}&per_page=${postsPerPage}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${window.Laravel.apiToken || ''}`,
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.data && data.data.length > 0) {
            // Append new posts
            const postsContainer = document.getElementById('posts-container');

            data.data.forEach(post => {
                // Create a temporary div to hold the rendered post
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = renderPostHTML(post);
                postsContainer.appendChild(tempDiv.firstElementChild);
            });

            // Initialize video overlays for new posts
            initializeVideoOverlays();

            // Check if there are more posts
            if (data.current_page >= data.last_page) {
                hasMorePosts = false;
                document.getElementById('end-of-content').style.display = 'block';
            }
        } else {
            hasMorePosts = false;
            document.getElementById('end-of-content').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading more posts:', error);
        hasMorePosts = false;
    })
    .finally(() => {
        isLoading = false;
        document.getElementById('loading-indicator').style.display = 'none';
    });
}

function renderPostHTML(post) {
    let html = `
        <div class="post" data-post-id="${post.id}">
            <div class="user">
                <a href="/users/${post.user.id}">${post.user.name}</a>
                ${post.is_private ? '<span class="privacy-badge private" style="font-size: 10px; padding: 1px 4px;"><i class="fas fa-lock"></i> Private</span>' : ''}
                <small>${new Date(post.created_at).toLocaleString()}</small>
            </div>
    `;

    if (post.content) {
        html += `<div class="content">${post.content}</div>`;
    }

    if (post.media && post.media.length > 0) {
        html += '<div class="post-media" style="margin: 10px 0;">';

        if (post.media.length === 1) {
            const media = post.media[0];
            if (media.media_type === 'image') {
                html += `<img src="/storage/${media.media_path}" alt="Post image" loading="lazy" style="width: 100%; height: auto; border-radius: 12px; display: block;">`;
            } else if (media.media_type === 'video') {
                html += `
                    <div class="video-container">
                        <video controls preload="metadata" poster="" loading="lazy" style="width: 100%; height: auto; display: block; border-radius: 12px;">
                            <source src="/storage/${media.media_path}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video-overlay" onclick="playVideo(this)" style="pointer-events: auto;">
                            <button class="play-button" type="button">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            html += '<div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">';
            post.media.forEach(media => {
                html += '<div class="media-item">';
                if (media.media_type === 'image') {
                    html += `<img src="/storage/${media.media_path}" alt="Post image" loading="lazy" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">`;
                } else if (media.media_type === 'video') {
                    html += `<video controls loading="lazy" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;"><source src="/storage/${media.media_path}" type="video/mp4"></video>`;
                }
                html += '</div>';
            });
            html += '</div>';
        }

        html += '</div>';
    }

    html += `
        <div>
            <button type="button" class="btn like-btn" onclick="toggleLike(${post.id}, this)" style="background: ${post.likes_count > 0 ? 'red' : 'var(--twitter-blue)'}">
                <i class="fas fa-heart"></i> <span class="like-count">${post.likes_count || 0}</span>
            </button>
            <button type="button" class="btn save-btn" onclick="toggleSave(${post.id}, this)" style="background: #6c757d; margin-left: 10px;">
                <i class="fas fa-bookmark"></i> <span class="save-text">Save</span>
            </button>
        </div>
        <hr>
        <h4>Comments</h4>
        <div class="comment-form-container">
            <textarea id="comment-content-${post.id}" placeholder="Add a comment..." maxlength="280" required></textarea>
            <button type="button" class="btn" style="font-size: 12px; padding: 5px 10px;" onclick="submitComment(${post.id})">Comment</button>
        </div>
    `;

    if (post.comments && post.comments.length > 0) {
        post.comments.forEach(comment => {
            html += `
                <div class="comment" style="border-left: 2px solid #E1E8ED; padding-left: 10px; margin: 10px 0;">
                    <strong>${comment.user.name}</strong>
                    <small>${new Date(comment.created_at).toLocaleString()}</small>
                    <p>${comment.content}</p>
                </div>
            `;
        });
    }

    html += '</div>';
    return html;
}

function initializeVideoOverlays() {
    const overlays = document.querySelectorAll('.video-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            playVideo(this);
        });
    });
}

// Intersection Observer for infinite scroll
let intersectionObserver;

function initializeInfiniteScroll() {
    const options = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    };

    intersectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMorePosts && !isLoading) {
                loadMorePosts();
            }
        });
    }, options);

    // Observe the loading indicator
    const loadingIndicator = document.getElementById('loading-indicator');
    if (loadingIndicator) {
        intersectionObserver.observe(loadingIndicator);
    }
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize video overlays for existing posts
    initializeVideoOverlays();

    // Initialize infinite scroll
    initializeInfiniteScroll();

    // Initialize real-time manager for story updates
    if (window.realTimeManager) {
        window.realTimeManager.init();
    }

    // Handle video loading states
    const videos = document.querySelectorAll('.video-container video');
    videos.forEach(video => {
        video.addEventListener('loadstart', function() {
            const container = this.parentElement;
            let loading = container.querySelector('.media-loading');
            if (!loading) {
                loading = document.createElement('div');
                loading.className = 'media-loading';
                loading.innerHTML = '<i class="fas fa-spinner"></i>';
                container.appendChild(loading);
            }
        });

        video.addEventListener('canplay', function() {
            const container = this.parentElement;
            const loading = container.querySelector('.media-loading');
            if (loading) {
                loading.remove();
            }
        });
    });
});

// Video play functionality
function playVideo(overlay) {
    const container = overlay.parentElement;
    const video = container.querySelector('video');

    if (video) {
        // Remove the overlay
        overlay.style.display = 'none';
        container.classList.add('playing');

        // Play the video
        video.play().catch(function(error) {
            console.log('Video play failed:', error);
        });

        // Add event listener to show overlay when video ends
        video.addEventListener('ended', function() {
            overlay.style.display = 'flex';
            container.classList.remove('playing');
        });

        // Add event listener to show overlay when video is paused
        video.addEventListener('pause', function() {
            if (!video.seeking) {
                overlay.style.display = 'flex';
                container.classList.remove('playing');
            }
        });

        // Add event listener to hide overlay when video plays
        video.addEventListener('play', function() {
            overlay.style.display = 'none';
            container.classList.add('playing');
        });
    }
}

// Like functionality for posts
function toggleLike(postId, button) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const likeCount = button.querySelector('.like-count');
    const currentCount = parseInt(likeCount.textContent) || 0;

    // Add loading state only
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Use web route instead of API route
    fetch(`/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: `_token=${csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update count with server response
            const newCount = data.likes_count || 0;
            likeCount.textContent = newCount;
        } else {
            // Show error but don't change appearance
            console.error('Like failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        // Don't change appearance on error
    })
    .finally(() => {
        // Restore original button content and enable it
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Like functionality for comments
function likeComment(commentId, button) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Find elements more reliably
    const likeCount = button.querySelector('.comment-like-count') || button.closest('.comment').querySelector('.comment-like-count');
    const heartIcon = button.querySelector('i');

    // Store original values
    const originalCount = likeCount ? parseInt(likeCount.textContent.trim()) || 0 : 0;
    const wasLiked = button.classList.contains('liked');

    // Optimistically update UI first (better UX)
    const newLikedState = !wasLiked;
    const optimisticCount = wasLiked ? Math.max(0, originalCount - 1) : originalCount + 1;

    // Update UI immediately
    if (newLikedState) {
        button.classList.add('liked');
    } else {
        button.classList.remove('liked');
    }
    if (likeCount) {
        likeCount.textContent = optimisticCount;
    }

    // Add loading state to icon only
    if (heartIcon) {
        heartIcon.className = 'fas fa-spinner fa-spin';
    }
    button.disabled = true;

    // Use web route instead of API route
    fetch(`/comments/${commentId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: `_token=${csrfToken}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update with actual server response
            const serverCount = parseInt(data.likes_count) || 0;
            if (likeCount) {
                likeCount.textContent = serverCount;
            }

            // Ensure liked state matches server response
            if (data.liked !== undefined) {
                if (data.liked) {
                    button.classList.add('liked');
                } else {
                    button.classList.remove('liked');
                }
            }

            console.log('Comment like updated successfully:', {
                commentId,
                liked: data.liked,
                count: serverCount,
                originalCount,
                optimisticCount
            });
        } else {
            throw new Error(data.message || 'Like failed');
        }
    })
    .catch(error => {
        console.error('Error toggling comment like:', error);
        // Revert optimistic updates on error
        if (likeCount) {
            likeCount.textContent = originalCount;
        }
        if (wasLiked) {
            button.classList.add('liked');
        } else {
            button.classList.remove('liked');
        }
        alert('Failed to update like. Please try again.');
    })
    .finally(() => {
        // Restore heart icon
        if (heartIcon) {
            heartIcon.className = 'fas fa-heart';
        }
        button.disabled = false;
    });
}

// Save functionality for posts
function toggleSave(postId, button) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const saveText = button.querySelector('.save-text');

    // Immediately update UI for better UX
    const isSaved = button.classList.contains('saved');
    if (isSaved) {
        // Unsave: remove saved class, change color and text
        button.classList.remove('saved');
        button.style.background = '#6c757d';
        saveText.textContent = 'Save';
    } else {
        // Save: add saved class, change color and text
        button.classList.add('saved');
        button.style.background = '#17a2b8';
        saveText.textContent = 'Saved';
    }

    // Add loading state
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/posts/${postId}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update with server response
            if (data.saved) {
                button.classList.add('saved');
                button.style.background = '#17a2b8';
                saveText.textContent = 'Saved';
            } else {
                button.classList.remove('saved');
                button.style.background = '#6c757d';
                saveText.textContent = 'Save';
            }
        } else {
            // Revert UI changes on error
            console.error('Save failed:', data.message);
            if (isSaved) {
                button.classList.add('saved');
                button.style.background = '#17a2b8';
                saveText.textContent = 'Saved';
            } else {
                button.classList.remove('saved');
                button.style.background = '#6c757d';
                saveText.textContent = 'Save';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling save:', error);
        // Revert UI changes on error
        if (isSaved) {
            button.classList.add('saved');
            button.style.background = '#17a2b8';
            saveText.textContent = 'Saved';
        } else {
            button.classList.remove('saved');
            button.style.background = '#6c757d';
            saveText.textContent = 'Save';
        }
    })
    .finally(() => {
        // Restore original button content and enable it
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Comment functionality
function submitComment(postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const textarea = document.getElementById(`comment-content-${postId}`);
    const content = textarea.value.trim();

    if (!content) {
        alert('Please enter a comment.');
        return;
    }

    // Disable form during submission
    const submitBtn = textarea.parentElement.querySelector('.btn');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Posting...';
    submitBtn.disabled = true;
    textarea.disabled = true;

    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Comment created successfully');
            textarea.value = '';
            // Real-time events will handle adding the comment to the UI
            // For now, we'll reload to show the new comment
            window.location.reload();
        } else {
            console.error('Comment creation failed');
            alert(data.message || 'Failed to post comment.');
        }
    })
    .catch(error => {
        console.error('Error creating comment:', error);
        alert('An error occurred while posting the comment.');
    })
    .finally(() => {
        // Re-enable form
        submitBtn.textContent = originalBtnText;
        submitBtn.disabled = false;
        textarea.disabled = false;
    });
}

// Reply functionality
function submitReply(commentId, postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const textarea = document.getElementById(`reply-content-${commentId}`);
    const content = textarea.value.trim();

    if (!content) {
        alert('Please enter a reply.');
        return;
    }

    // Disable form during submission
    const submitBtn = document.querySelector(`#reply-form-${commentId} .reply-submit-btn`);
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    submitBtn.disabled = true;
    textarea.disabled = true;

    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            post_id: postId,
            parent_id: commentId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Reply created successfully');
            textarea.value = '';
            toggleReplyForm(commentId); // Hide the reply form
            // Real-time events will handle adding the reply to the UI
            // For now, we'll reload to show the new reply
            window.location.reload();
        } else {
            console.error('Reply creation failed');
            alert(data.message || 'Failed to post reply.');
        }
    })
    .catch(error => {
        console.error('Error creating reply:', error);
        alert('An error occurred while posting the reply.');
    })
    .finally(() => {
        // Re-enable form
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
        textarea.disabled = false;
    });
}

// Delete functionality
function deletePost(postId, button) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const postElement = button.closest('.post');

    // Add loading state
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/posts/${postId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the post from UI
            postElement.remove();
            console.log('Post deleted successfully');
        } else {
            console.error('Post deletion failed');
            alert(data.message || 'Failed to delete post.');
        }
    })
    .catch(error => {
        console.error('Error deleting post:', error);
        alert('An error occurred while deleting the post.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function deleteComment(commentId, button) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const commentElement = button.closest('.comment');

    // Add loading state
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the comment from UI
            commentElement.remove();
            console.log('Comment deleted successfully');
        } else {
            console.error('Comment deletion failed');
            alert(data.message || 'Failed to delete comment.');
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
        alert('An error occurred while deleting the comment.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Copy post link functionality
function copyPostLink(postId) {
    const url = window.location.origin + '/posts/' + postId;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            // Show success feedback
            showNotification('Link copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(url);
        });
    } else {
        fallbackCopyTextToClipboard(url);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showNotification('Link copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy link. Please copy manually: ' + text, 'error');
    }

    textArea.remove();
}

// Toggle comments visibility functionality
function toggleComments(postId, show) {
    const hiddenComments = document.getElementById(`hidden-comments-${postId}`);
    const showMoreContainer = document.getElementById(`show-more-container-${postId}`);
    const hideCommentsContainer = document.getElementById(`hide-comments-container-${postId}`);

    if (hiddenComments && showMoreContainer && hideCommentsContainer) {
        if (show) {
            // Show hidden comments with animation
            hiddenComments.style.display = 'block';

            // Switch button visibility
            showMoreContainer.style.display = 'none';
            hideCommentsContainer.style.display = 'block';
        } else {
            // Hide comments
            hiddenComments.style.display = 'none';

            // Switch button visibility
            showMoreContainer.style.display = 'block';
            hideCommentsContainer.style.display = 'none';
        }
    }
}

// Toggle replies visibility functionality
function toggleReplies(commentId, show) {
    const hiddenReplies = document.getElementById(`hidden-replies-${commentId}`);
    const showMoreRepliesContainer = document.getElementById(`show-more-replies-container-${commentId}`);
    const hideRepliesContainer = document.getElementById(`hide-replies-container-${commentId}`);

    if (hiddenReplies && showMoreRepliesContainer && hideRepliesContainer) {
        if (show) {
            // Show hidden replies with animation
            hiddenReplies.style.display = 'block';

            // Switch button visibility
            showMoreRepliesContainer.style.display = 'none';
            hideRepliesContainer.style.display = 'block';
        } else {
            // Hide replies
            hiddenReplies.style.display = 'none';

            // Switch button visibility
            showMoreRepliesContainer.style.display = 'block';
            hideRepliesContainer.style.display = 'none';
        }
    }
}

// Toggle nested replies visibility functionality
function toggleNestedReplies(commentId, show) {
    const hiddenReplies = document.getElementById(`hidden-replies-${commentId}`);
    const showMoreNestedRepliesContainer = document.getElementById(`show-more-nested-replies-container-${commentId}`);
    const hideNestedRepliesContainer = document.getElementById(`hide-nested-replies-container-${commentId}`);

    if (hiddenReplies && showMoreNestedRepliesContainer && hideNestedRepliesContainer) {
        if (show) {
            // Show hidden nested replies with animation
            hiddenReplies.style.display = 'block';

            // Switch button visibility
            showMoreNestedRepliesContainer.style.display = 'none';
            hideNestedRepliesContainer.style.display = 'block';
        } else {
            // Hide nested replies
            hiddenReplies.style.display = 'none';

            // Switch button visibility
            showMoreNestedRepliesContainer.style.display = 'block';
            hideNestedRepliesContainer.style.display = 'none';
        }
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Show with animation
    setTimeout(() => notification.classList.add('show'), 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Follow functionality
function toggleFollow(button, userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const username = button.getAttribute('data-username') || 'user';

    // Immediately update UI for better UX
    const isFollowing = button.classList.contains('following');
    if (isFollowing) {
        // Unfollow: change text and style
        button.textContent = 'Follow';
        button.classList.remove('following');
        button.style.background = 'var(--twitter-blue)';
    } else {
        // Follow: change text and style
        button.textContent = 'Following';
        button.classList.add('following');
        button.style.background = '#28a745';
    }

    // Add loading state
    const originalText = button.textContent;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/users/${userId}/follow`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update with server response
            if (data.following) {
                button.textContent = 'Following';
                button.classList.add('following');
                button.style.background = '#28a745';
            } else {
                button.textContent = 'Follow';
                button.classList.remove('following');
                button.style.background = 'var(--twitter-blue)';
            }
        } else {
            // Revert UI changes on error
            console.error('Follow failed:', data.message);
            if (isFollowing) {
                button.textContent = 'Following';
                button.classList.add('following');
                button.style.background = '#28a745';
            } else {
                button.textContent = 'Follow';
                button.classList.remove('following');
                button.style.background = 'var(--twitter-blue)';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
        // Revert UI changes on error
        if (isFollowing) {
            button.textContent = 'Following';
            button.classList.add('following');
            button.style.background = '#28a745';
        } else {
            button.textContent = 'Follow';
            button.classList.remove('following');
            button.style.background = 'var(--twitter-blue)';
        }
    })
    .finally(() => {
        // Restore original button content and enable it
        button.disabled = false;
    });
}

// Initialize video overlays on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to video overlays
    const overlays = document.querySelectorAll('.video-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            playVideo(this);
        });
    });

    // Handle video loading states
    const videos = document.querySelectorAll('.video-container video');
    videos.forEach(video => {
        video.addEventListener('loadstart', function() {
            const container = this.parentElement;
            let loading = container.querySelector('.media-loading');
            if (!loading) {
                loading = document.createElement('div');
                loading.className = 'media-loading';
                loading.innerHTML = '<i class="fas fa-spinner"></i>';
                container.appendChild(loading);
            }
        });

        video.addEventListener('canplay', function() {
            const container = this.parentElement;
            const loading = container.querySelector('.media-loading');
            if (loading) {
                loading.remove();
            }
        });
    });

    // Add notification styles
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 10000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            color: var(--twitter-dark);
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-success {
            border-color: #28a745;
        }

        .notification-success i {
            color: #28a745;
        }

        .notification-error {
            border-color: var(--error-color);
        }

        .notification-error i {
            color: var(--error-color);
        }

        .notification-info {
            border-color: var(--twitter-blue);
        }

        .notification-info i {
            color: var(--twitter-blue);
        }
    `;
    document.head.appendChild(style);
});
</script>
    </div>
</div>
@endsection
