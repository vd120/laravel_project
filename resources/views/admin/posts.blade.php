@extends('layouts.app')

@section('title', 'Manage Posts - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>Manage Posts</h1>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts by content..." class="search-input">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.posts') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    
    <div class="data-table-container">
        <div class="table-header">
            <h2>Posts ({{ $posts->total() }})</h2>
        </div>

        @if($posts->count() > 0)
        <div class="posts-list">
            @foreach($posts as $post)
            <div class="post-item">
                <div class="post-header">
                    <div class="post-user">
                        @if($post->user->profile && $post->user->profile->avatar)
                            <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="Avatar" class="user-avatar-small">
                        @else
                            <div class="user-avatar-placeholder-small">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="user-info">
                            <span class="username">{{ $post->user->name }}</span>
                            <span class="post-date">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="post-actions">
                        <a href="{{ route('posts.show', $post) }}" class="btn-action view-btn" title="View Post" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.posts.delete', $post) }}" class="inline-form" onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action delete-btn" title="Delete Post">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="post-content">
                    @if($post->content)
                        <p>{{ Str::limit($post->content, 200) }}</p>
                    @endif

                    @if($post->media->count() > 0)
                        <div class="post-media-preview">
                            @if($post->media->count() === 1)
                                @php $media = $post->media->first(); @endphp
                                @if($media->media_type === 'image')
                                    <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post media" class="media-preview">
                                @elseif($media->media_type === 'video')
                                    <video class="media-preview" muted>
                                        <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                                    </video>
                                @endif
                            @else
                                <div class="media-grid">
                                    @foreach($post->media->take(4) as $media)
                                        @if($media->media_type === 'image')
                                            <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post media" class="media-item">
                                        @endif
                                    @endforeach
                                    @if($post->media->count() > 4)
                                        <div class="media-more">+{{ $post->media->count() - 4 }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="post-stats">
                    <span><i class="fas fa-heart"></i> {{ $post->likes->count() }}</span>
                    <span><i class="fas fa-comment"></i> {{ $post->comments->count() }}</span>
                    @if($post->is_private)
                        <span class="private-indicator"><i class="fas fa-lock"></i> Private</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        
        <div class="pagination-container">
            {{ $posts->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-image"></i>
            <h3>No posts found</h3>
            <p>No posts match your current search.</p>
        </div>
        @endif
    </div>
</div>

<style>
.admin-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.header-actions {
    display: flex;
    gap: 12px;
}

/* Filters Section */
.filters-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-form {
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.search-group {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--twitter-gray);
}

.search-input {
    width: 100%;
    padding: 10px 16px 10px 40px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
}

/* Posts List */
.data-table-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--twitter-light);
}

.table-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.posts-list {
    max-height: 70vh;
    overflow-y: auto;
}

.post-item {
    border-bottom: 1px solid var(--border-color);
    padding: 20px;
    transition: background-color 0.2s ease;
}

.post-item:hover {
    background: var(--twitter-light);
}

.post-item:last-child {
    border-bottom: none;
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.post-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.user-avatar-placeholder-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
}

.username {
    font-weight: 600;
    color: var(--twitter-dark);
    display: block;
}

.post-date {
    font-size: 12px;
    color: var(--twitter-gray);
}

.post-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 14px;
}

.view-btn {
    background: var(--twitter-blue);
    color: white;
}

.view-btn:hover {
    background: #1991DB;
}

.delete-btn {
    background: #dc3545;
    color: white;
}

.delete-btn:hover {
    background: #c82333;
}

.inline-form {
    display: inline;
}

.post-content {
    margin-bottom: 12px;
}

.post-content p {
    margin: 0 0 12px 0;
    line-height: 1.5;
    color: var(--twitter-dark);
}

.post-media-preview {
    margin-top: 8px;
}

.media-preview {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    object-fit: cover;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
    margin-top: 8px;
}

.media-item {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 4px;
}

.media-more {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.6);
    color: white;
    font-weight: 600;
    border-radius: 4px;
}

.post-stats {
    display: flex;
    gap: 16px;
    font-size: 14px;
    color: var(--twitter-gray);
}

.post-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.private-indicator {
    color: #6c757d !important;
}

/* Pagination */
.pagination-container {
    padding: 20px;
    background: var(--twitter-light);
    border-top: 1px solid var(--border-color);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
}

.empty-state p {
    margin: 0;
}

/* Button Styles */
.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #1991DB;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--card-bg);
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--hover-bg);
    border-color: var(--twitter-blue);
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-page {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-group {
        min-width: auto;
    }

    .post-item {
        padding: 16px;
    }

    .post-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .post-actions {
        align-self: flex-end;
    }

    .post-stats {
        flex-wrap: wrap;
        gap: 12px;
    }

    .media-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this post? This action cannot be undone.');
}
</script>
@endsection
