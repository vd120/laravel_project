@extends('layouts.app')

@section('title', 'User Details - Admin Panel')

@section('content')
<div class="admin-page">
    {{-- Header --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.users') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1>User Profile</h1>
                <p>View and manage user details</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    {{-- User Card --}}
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-avatar-large">
                @if($user->profile && $user->profile->avatar)
                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="">
                @else
                    <div class="avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                @endif
            </div>
            @if($user->is_suspended)
            <div class="suspended-badge" style="right: 130px;"><i class="fas fa-ban"></i> Suspended</div>
            @endif
            @if($user->is_admin)
            <div class="admin-badge"><i class="fas fa-crown"></i> Admin</div>
            @endif
        </div>
        
        <div class="user-card-body">
            <h2>{{ $user->name }}</h2>
            <div class="user-status">
                @if($user->profile && $user->profile->is_private)
                <span class="status-badge private"><i class="fas fa-lock"></i> Private</span>
                @else
                <span class="status-badge public"><i class="fas fa-globe"></i> Public</span>
                @endif
            </div>

            <div class="user-meta">
                <div class="meta-item">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $user->email }}</span>
                </div>
                @if($user->profile && $user->profile->location)
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $user->profile->location }}</span>
                </div>
                @endif
                @if($user->profile && $user->profile->website)
                <div class="meta-item">
                    <i class="fas fa-link"></i>
                    <a href="{{ $user->profile->website }}" target="_blank">{{ $user->profile->website }}</a>
                </div>
                @endif
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Joined {{ $user->created_at->format('M j, Y') }}</span>
                </div>
            </div>

            @if($user->profile && $user->profile->bio)
            <div class="user-bio">
                <p>{{ $user->profile->bio }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon-wrap posts">
                <i class="fas fa-pen-square"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">{{ $user->posts->count() }}</span>
                <span class="stat-label">Posts</span>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap followers">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">{{ $user->followers->count() }}</span>
                <span class="stat-label">Followers</span>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap following">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">{{ $user->follows->count() }}</span>
                <span class="stat-label">Following</span>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap stories">
                <i class="fas fa-circle-notch"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">{{ $user->stories->count() }}</span>
                <span class="stat-label">Stories</span>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="content-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="posts">Posts</button>
            <button class="tab-btn" data-tab="comments">Comments</button>
            <button class="tab-btn" data-tab="stories">Stories</button>
        </div>

        <div id="posts-tab" class="tab-content active">
            @if($user->posts->count() > 0)
            <div class="items-list">
                @foreach($user->posts->take(10) as $post)
                <div class="item-card">
                    <div class="item-content">
                        <p>{{ Str::limit($post->content ?? 'Media post', 150) }}</p>
                    </div>
                    <div class="item-meta">
                        <span><i class="fas fa-heart"></i> {{ $post->likes->count() }}</span>
                        <span><i class="fas fa-comment"></i> {{ $post->comments->count() }}</span>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                        <a href="/posts/{{ $post->slug }}" target="_blank" class="view-link">View <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-pen-square"></i>
                <p>No posts yet</p>
            </div>
            @endif
        </div>

        <div id="comments-tab" class="tab-content">
            @if($user->comments->count() > 0)
            <div class="items-list">
                @foreach($user->comments->take(10) as $comment)
                <div class="item-card">
                    <div class="item-content comment">
                        <p>{!! app(\App\Services\MentionService::class)->convertMentionsToLinks($comment->content) !!}</p>
                    </div>
                    <div class="item-meta">
                        <span><i class="fas fa-heart"></i> {{ $comment->likes->count() }}</span>
                        <span>On {{ $comment->post->user->name }}'s post</span>
                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                        <a href="/posts/{{ $comment->post->slug }}" target="_blank" class="view-link">View <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <p>No comments yet</p>
            </div>
            @endif
        </div>

        <div id="stories-tab" class="tab-content">
            @if($user->stories->count() > 0)
            <div class="stories-grid">
                @foreach($user->stories->take(12) as $story)
                <div class="story-thumb">
                    @if($story->media_type === 'image')
                        <img src="{{ asset('storage/' . $story->media_path) }}" alt="">
                    @else
                        <video muted>
                            <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                        </video>
                    @endif
                    <div class="story-overlay">
                        <span><i class="fas fa-eye"></i> {{ $story->views }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-circle-notch"></i>
                <p>No stories yet</p>
            </div>
            @endif
        </div>
    </div>
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
    margin: -16px -16px 24px;
    padding: 24px 16px;
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
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

.header-actions {
    display: flex;
    gap: 12px;
}

.action-btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.action-btn.edit {
    background: white;
    color: #ec4899;
}

.action-btn.edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.user-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}

.user-card-header {
    position: relative;
    height: 100px;
    background: linear-gradient(135deg, #ec4899, #db2777);
}

.user-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    position: absolute;
    bottom: -50px;
    left: 24px;
    border: 4px solid var(--card-bg);
    background: linear-gradient(135deg, var(--primary), var(--secondary));
}

.user-avatar-large img {
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
    font-size: 36px;
}

.admin-badge {
    position: absolute;
    bottom: -50px;
    right: 24px;
    background: linear-gradient(135deg, #f43f5e, #e11d48);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.suspended-badge {
    position: absolute;
    bottom: -50px;
    right: 24px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.user-card-body {
    padding: 60px 24px 24px;
}

.user-card-body h2 {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
    color: var(--text);
}

.user-status {
    margin-bottom: 20px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.private {
    background: rgba(107, 114, 128, 0.15);
    color: #6b7280;
}

.status-badge.public {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.user-meta {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--text-muted);
}

.meta-item i {
    width: 18px;
    color: #ec4899;
}

.meta-item a {
    color: #ec4899;
    text-decoration: none;
}

.meta-item a:hover {
    text-decoration: underline;
}

.user-bio {
    background: var(--bg);
    padding: 16px;
    border-radius: 10px;
}

.user-bio p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text);
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.stat-box {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.stat-icon-wrap.posts { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.stat-icon-wrap.followers { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
.stat-icon-wrap.following { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.stat-icon-wrap.stories { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
}

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
}

.content-tabs {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    border-bottom: 1px solid var(--border-color);
}

.tab-btn {
    flex: 1;
    padding: 16px;
    background: none;
    border: none;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    background: var(--hover-bg);
    color: var(--text);
}

.tab-btn.active {
    color: #ec4899;
    border-bottom-color: #ec4899;
    background: var(--bg);
}

.tab-content {
    display: none;
    padding: 20px;
}

.tab-content.active {
    display: block;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-card {
    background: var(--bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
}

.item-content {
    margin-bottom: 12px;
}

.item-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: var(--text);
}

.item-content.comment {
    background: var(--card-bg);
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid #ec4899;
}

.item-content a {
    color: #ec4899;
    font-weight: 500;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 12px;
    color: var(--text-muted);
}

.item-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.view-link {
    margin-left: auto;
    color: #ec4899;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.view-link:hover {
    text-decoration: underline;
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.story-thumb {
    position: relative;
    aspect-ratio: 9/16;
    border-radius: 8px;
    overflow: hidden;
    background: #000;
}

.story-thumb img,
.story-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.story-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 40px;
    margin-bottom: 12px;
    display: block;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
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

    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .tab-buttons {
        flex-direction: column;
    }

    .tab-btn {
        border-bottom: none;
        border-right: 3px solid transparent;
    }

    .tab-btn.active {
        border-right-color: #ec4899;
        border-bottom-color: transparent;
    }

    .stories-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Remove active from all buttons and contents
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});
</script>
@endsection
