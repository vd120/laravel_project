@extends('layouts.app')

@section('title', 'Manage Comments - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>Manage Comments</h1>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search comments by content..." class="search-input">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.comments') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    
    <div class="data-table-container">
        <div class="table-header">
            <h2>Comments ({{ $comments->total() }})</h2>
        </div>

        @if($comments->count() > 0)
        <div class="comments-list">
            @foreach($comments as $comment)
            <div class="comment-item">
                <div class="comment-header">
                    <div class="comment-user">
                        @if($comment->user->profile && $comment->user->profile->avatar)
                            <img src="{{ asset('storage/' . $comment->user->profile->avatar) }}" alt="Avatar" class="user-avatar-small">
                        @else
                            <div class="user-avatar-placeholder-small">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="user-info">
                            <span class="username">{{ $comment->user->name }}</span>
                            <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="comment-actions">
                        <form method="POST" action="{{ route('admin.comments.delete', $comment) }}" class="inline-form" onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action delete-btn" title="Delete Comment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="comment-content">
                    <p>{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content) !!}</p>
                </div>

                <div class="comment-post-info">
                    <span>On post by {{ $comment->post->user->name }}</span>
                    <a href="{{ route('posts.show', $comment->post) }}" class="post-link" target="_blank">
                        View Post <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>

                <div class="comment-stats">
                    <span><i class="fas fa-heart"></i> {{ $comment->likes->count() }}</span>
                </div>
            </div>
            @endforeach
        </div>

        
        <div class="pagination-container">
            {{ $comments->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>No comments found</h3>
            <p>No comments match your current search.</p>
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

/* Comments List */
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

.comments-list {
    max-height: 70vh;
    overflow-y: auto;
}

.comment-item {
    border-bottom: 1px solid var(--border-color);
    padding: 20px;
    transition: background-color 0.2s ease;
}

.comment-item:hover {
    background: var(--twitter-light);
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.comment-user {
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

.comment-date {
    font-size: 12px;
    color: var(--twitter-gray);
}

.comment-actions {
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

.comment-content {
    margin-bottom: 12px;
}

.comment-content p {
    margin: 0;
    line-height: 1.5;
    color: var(--twitter-dark);
    background: var(--twitter-light);
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid var(--twitter-blue);
}

.comment-post-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    color: var(--twitter-gray);
    margin-bottom: 8px;
}

.post-link {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.post-link:hover {
    color: #1991DB;
}

.comment-stats {
    font-size: 14px;
    color: var(--twitter-gray);
}

.comment-stats span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
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

    .comment-item {
        padding: 16px;
    }

    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .comment-actions {
        align-self: flex-end;
    }

    .comment-post-info {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
}
</style>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this comment? This action cannot be undone.');
}
</script>
@endsection
