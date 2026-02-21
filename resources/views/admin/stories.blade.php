@extends('layouts.app')

@section('title', 'Manage Stories - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>Stories</h1>
                <p>Manage and moderate user stories</p>
            </div>
        </div>
        <div class="header-stats">
            <span class="total-badge">{{ $stories->total() }} Total</span>
        </div>
    </div>

    {{-- Search --}}
    <div class="search-section">
        <div class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Search stories by username..." autocomplete="off">
            </div>
            @if(request('search'))
            <a href="{{ route('admin.stories') }}" class="clear-btn">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </div>
    </div>

    {{-- Stories Grid --}}
    @if($stories->count() > 0)
    <div class="stories-grid">
        @foreach($stories as $story)
        <div class="story-card">
            <div class="story-media">
                @if($story->media_type === 'image')
                    <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="story-img">
                @elseif($story->media_type === 'video')
                    <video class="story-video" muted>
                        <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                    </video>
                @endif
                <div class="story-overlay">
                    <form method="POST" action="{{ route('admin.stories.delete', $story) }}" onsubmit="return confirm('Delete this story?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="story-info">
                <div class="story-user">
                    <div class="user-avatar">
                        @if($story->user->profile && $story->user->profile->avatar)
                            <img src="{{ asset('storage/' . $story->user->profile->avatar) }}" alt="">
                        @else
                            <div class="avatar-placeholder">{{ substr($story->user->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ $story->user->name }}</span>
                        <span class="story-time">{{ $story->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                @if($story->content)
                <div class="story-content">
                    <p>{{ Str::limit($story->content, 80) }}</p>
                </div>
                @endif

                <div class="story-stats">
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span>{{ $story->storyViews->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span>{{ $story->reactions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper">
        {{ $stories->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-circle-notch"></i>
        </div>
        <h3>No stories found</h3>
        <p>No stories match your search criteria.</p>
    </div>
    @endif
</div>

<style>
.admin-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 16px 40px;
}

.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: -16px -16px 24px;
    padding: 24px 16px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 0 0 20px 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.back-btn:hover {
    background: rgba(255,255,255,0.3);
}

.admin-header h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: white;
}

.admin-header p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
}

.total-badge {
    background: rgba(255,255,255,0.25);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.search-section {
    margin-bottom: 24px;
}

.search-form {
    display: flex;
    gap: 12px;
    align-items: center;
}

.search-box {
    flex: 1;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.search-box input {
    width: 100%;
    padding: 14px 16px 14px 46px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background: var(--card-bg);
    color: var(--text);
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.clear-btn {
    padding: 14px 20px;
    background: var(--bg);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.clear-btn:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

.story-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.story-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.story-media {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: #000;
}

.story-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-overlay {
    position: absolute;
    top: 12px;
    right: 12px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.story-card:hover .story-overlay {
    opacity: 1;
}

.delete-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}

.delete-btn:hover {
    background: #ef4444;
    transform: scale(1.1);
}

.story-info {
    padding: 16px;
}

.story-user {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.story-time {
    font-size: 12px;
    color: var(--text-muted);
}

.story-content {
    background: var(--bg);
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 12px;
}

.story-content p {
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
    color: var(--text);
}

.story-stats {
    display: flex;
    gap: 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-muted);
}

.stat-item i {
    font-size: 12px;
}

.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: rgba(245, 158, 11, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 32px;
    color: #f59e0b;
}

.empty-state h3 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
}

.empty-state p {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .header-left {
        flex-direction: column;
    }

    .search-form {
        flex-direction: column;
    }

    .search-box input {
        width: 100%;
    }

    .clear-btn {
        width: 100%;
        text-align: center;
    }

    .stories-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .story-media {
        height: 140px;
    }
}

@media (max-width: 480px) {
    .stories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                if (query.length === 0) {
                    window.location.href = '{{ route("admin.stories") }}';
                }
                return;
            }

            searchTimeout = setTimeout(function() {
                window.location.href = '{{ route("admin.stories") }}?search=' + encodeURIComponent(query);
            }, 500);
        });
    }
});
</script>
@endsection
