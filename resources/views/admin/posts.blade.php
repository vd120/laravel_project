@extends('layouts.app')

@section('title', 'Manage Posts - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>Posts</h1>
                <p>Manage and moderate user posts</p>
            </div>
        </div>
        <div class="header-stats">
            <span class="total-badge">{{ $posts->total() }} Total</span>
        </div>
    </div>

    {{-- Search --}}
    <div class="search-section">
        <div class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Search posts..." autocomplete="off">
            </div>
            @if(request('search'))
            <a href="{{ route('admin.posts') }}" class="clear-btn">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </div>
    </div>

    {{-- Posts List --}}
    @if($posts->count() > 0)
    <div class="posts-section">
        @foreach($posts as $post)
        <div class="post-card">
            <div class="post-main">
                <div class="post-user">
                    <div class="user-avatar">
                        <img src="{{ $post->user->avatar_url }}" alt="">
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ $post->user->username }}</span>
                        <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                    @if($post->is_private)
                    <span class="private-badge"><i class="fas fa-lock"></i></span>
                    @endif
                </div>
                
                <div class="post-content">
                    @if($post->content)
                        <p>{{ Str::limit($post->content, 250) }}</p>
                    @endif
                </div>

                @if($post->media->count() > 0)
                <div class="post-media">
                    @if($post->media->count() === 1)
                        @php $media = $post->media->first(); @endphp
                        @if($media->media_type === 'image')
                            <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post media" class="single-media">
                        @elseif($media->media_type === 'video')
                            <video class="single-media" controls muted>
                                <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                            </video>
                        @endif
                    @else
                        <div class="media-grid">
                            @foreach($post->media->take(4) as $media)
                                @if($media->media_type === 'image')
                                    <img src="{{ asset('storage/' . $media->media_path) }}" alt="" class="media-thumb">
                                @endif
                            @endforeach
                            @if($post->media->count() > 4)
                                <div class="media-more">+{{ $post->media->count() - 4 }}</div>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                <div class="post-stats">
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span>{{ $post->likes->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-comment"></i>
                        <span>{{ $post->comments->count() }}</span>
                    </div>
                    <a href="/posts/{{ $post->slug }}" target="_blank" class="view-link">
                        <i class="fas fa-external-link-alt"></i> View
                    </a>
                </div>
            </div>

            <div class="post-actions">
                <form method="POST" action="{{ route('admin.posts.delete', $post) }}" onsubmit="return confirm('Delete this post?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper">
        {{ $posts->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-images"></i>
        </div>
        <h3>No posts found</h3>
        <p>No posts match your search criteria.</p>
    </div>
    @endif
</div>

<style>
.admin-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 16px 40px;
}

.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0 -16px 24px;
    padding: 20px 16px;
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    border-radius: 16px 16px 20px 20px;
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
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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

.posts-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.post-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    gap: 16px;
    transition: all 0.2s ease;
}

.post-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.post-main {
    flex: 1;
    min-width: 0;
}

.post-user {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
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
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.post-time {
    font-size: 12px;
    color: var(--text-muted);
}

.private-badge {
    margin-left: auto;
    padding: 4px 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-radius: 12px;
    font-size: 12px;
}

.post-content {
    background: var(--bg);
    padding: 14px 16px;
    border-radius: 10px;
    margin-bottom: 14px;
}

.post-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text);
}

.post-media {
    margin-bottom: 14px;
}

.single-media {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 10px;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    border-radius: 10px;
    overflow: hidden;
}

.media-thumb {
    width: 100%;
    height: 80px;
    object-fit: cover;
}

.media-more {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.6);
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.post-stats {
    display: flex;
    align-items: center;
    gap: 20px;
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

.view-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #8b5cf6;
    text-decoration: none;
    font-weight: 500;
    margin-left: auto;
    transition: all 0.2s ease;
}

.view-link:hover {
    opacity: 0.8;
}

.post-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.delete-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}

.delete-btn:hover {
    background: #ef4444;
    color: white;
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
    background: rgba(139, 92, 246, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon i {
    font-size: 32px;
    color: #8b5cf6;
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

    .post-card {
        flex-direction: column;
    }

    .post-actions {
        flex-direction: row;
        justify-content: flex-end;
    }

    .post-stats {
        flex-wrap: wrap;
        gap: 12px;
    }

    .media-grid {
        grid-template-columns: repeat(2, 1fr);
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
                    window.location.href = '{{ route("admin.posts") }}';
                }
                return;
            }

            searchTimeout = setTimeout(function() {
                window.location.href = '{{ route("admin.posts") }}?search=' + encodeURIComponent(query);
            }, 500);
        });
    }
});
</script>
@endsection
