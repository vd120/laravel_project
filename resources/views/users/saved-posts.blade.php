@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
    <h2 class="text-2xl font-bold text-white text-center mb-6">
        <i class="fas fa-bookmark mr-2"></i>Saved Posts
    </h2>

    @if($savedPosts->count() > 0)
        <div id="posts-container">
            @foreach($savedPosts as $savedPost)
                @include('partials.post', ['post' => $savedPost->post])
            @endforeach
        </div>

        <div class="mt-6">
            {{ $savedPosts->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-bookmark"></i>
            <h3>No Saved Posts Yet</h3>
            <p>You haven't saved any posts yet. Click the bookmark icon on posts you want to save for later!</p>
            <a href="{{ route('home') }}" class="btn">Browse Posts</a>
        </div>
    @endif
</div>

<style>
.saved-posts-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px;
}

.saved-posts-header h2 {
    margin: 0 0 24px 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.post {
    margin-bottom: 20px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
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
    width: 100%;
    height: auto;
}

.media-grid {
    display: grid;
    gap: 12px;
    margin-top: 16px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.media-item img,
.media-item video {
    border-radius: 10px;
    width: 100%;
    height: 200px;
    object-fit: cover;
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

.post-actions {
    margin-top: 15px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.post .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: white;
}

.like-btn {
    background: var(--twitter-blue);
}

.like-btn.liked {
    background: #dc3545;
}

.save-btn {
    background: #6c757d;
}

.save-btn.saved {
    background: #17a2b8;
}

hr {
    border: none;
    border-top: 1px solid var(--border-color);
    margin: 24px 0;
}

.comment-form-container {
    margin: 16px 0;
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

.empty-state {
    text-align: center;
    padding: 48px 16px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 48px;
    opacity: 0.5;
    margin-bottom: 16px;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.empty-state p {
    margin: 0 0 16px 0;
    font-size: 14px;
}

.empty-state .btn {
    background: var(--twitter-blue);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.empty-state .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Pagination */
.pagination {
    margin-top: 24px;
    display: flex;
    justify-content: center;
    gap: 4px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    text-decoration: none;
    color: var(--twitter-gray);
    font-size: 14px;
    transition: all 0.2s ease;
}

.pagination .page-link {
    background: var(--card-bg);
}

.pagination a:hover,
.pagination .active span {
    background: var(--twitter-blue);
    color: white;
    border-color: var(--twitter-blue);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .saved-posts-page {
        padding: 12px;
    }

    .saved-posts-header h2 {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .post {
        padding: 12px;
        margin-bottom: 16px;
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

    .post-actions {
        gap: 6px;
    }

    .post .btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .comment-form-container {
        padding: 12px;
    }

    .comment-form-container textarea {
        font-size: 13px;
        min-height: 60px;
    }

    .empty-state {
        padding: 32px 12px;
    }

    .empty-state i {
        font-size: 36px;
    }

    .empty-state h3 {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .saved-posts-page {
        padding: 8px;
    }

    .saved-posts-header h2 {
        font-size: 18px;
        margin-bottom: 16px;
    }

    .post {
        padding: 10px;
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

    .post-media {
        margin: 14px 0;
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

    .post-actions {
        flex-direction: column;
        gap: 8px;
    }

    .post .btn {
        width: 100%;
        justify-content: center;
        padding: 8px 12px;
        font-size: 14px;
    }

    hr {
        margin: 20px 0;
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
        width: 100%;
    }

    .pagination a,
    .pagination span {
        padding: 6px 8px;
        font-size: 12px;
    }

    .empty-state {
        padding: 24px 8px;
    }

    .empty-state h3 {
        font-size: 14px;
    }

    .empty-state p {
        font-size: 12px;
    }
}
</style>
@endsection
