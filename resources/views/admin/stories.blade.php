@extends('layouts.app')

@section('title', 'Manage Stories - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>Manage Stories</h1>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stories by username..." class="search-input">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.stories') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <!-- Stories Table -->
    <div class="data-table-container">
        <div class="table-header">
            <h2>Stories ({{ $stories->total() }})</h2>
        </div>

        @if($stories->count() > 0)
        <div class="stories-grid">
            @foreach($stories as $story)
            <div class="story-item">
                <div class="story-header">
                    <div class="story-user">
                        @if($story->user->profile && $story->user->profile->avatar)
                            <img src="{{ asset('storage/' . $story->user->profile->avatar) }}" alt="Avatar" class="user-avatar-small">
                        @else
                            <div class="user-avatar-placeholder-small">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="user-info">
                            <span class="username">{{ $story->user->name }}</span>
                            <span class="story-date">{{ $story->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="story-actions">
                        <form method="POST" action="{{ route('admin.stories.delete', $story) }}" class="inline-form" onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action delete-btn" title="Delete Story">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="story-media">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="story-preview">
                    @elseif($story->media_type === 'video')
                        <video class="story-preview" muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                </div>

                <div class="story-content">
                    @if($story->content)
                        <p>{{ Str::limit($story->content, 100) }}</p>
                    @endif
                </div>

                <div class="story-stats">
                    <span><i class="fas fa-eye"></i> {{ $story->storyViews->count() }}</span>
                    <span><i class="fas fa-heart"></i> {{ $story->reactions->count() }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $stories->appends(request()->query())->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-camera"></i>
            <h3>No stories found</h3>
            <p>No stories match your current search.</p>
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

/* Stories Grid */
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

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px;
}

.story-item {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    background: var(--card-bg);
    box-shadow: var(--shadow);
    transition: all 0.2s ease;
}

.story-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.story-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.story-user {
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

.story-date {
    font-size: 12px;
    color: var(--twitter-gray);
}

.story-actions {
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

.story-media {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #000;
}

.story-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.story-content {
    padding: 16px;
}

.story-content p {
    margin: 0;
    line-height: 1.5;
    color: var(--twitter-dark);
    font-size: 14px;
}

.story-stats {
    padding: 12px 16px;
    background: var(--twitter-light);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 16px;
    font-size: 14px;
    color: var(--twitter-gray);
}

.story-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Pagination */
.pagination-container {
    padding: 20px;
    background: var(--twitter-light);
    border-top: 1px solid var(--border-color);
    grid-column: 1 / -1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--twitter-gray);
    grid-column: 1 / -1;
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

    .stories-grid {
        grid-template-columns: 1fr;
        padding: 16px;
    }

    .story-item {
        margin-bottom: 16px;
    }
}
</style>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this story? This action cannot be undone.');
}
</script>
@endsection
