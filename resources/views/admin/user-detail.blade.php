@extends('layouts.app')

@section('title', 'User Details - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>User Details</h1>
        <div class="header-actions">
            <a href="{{ route('admin.users') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Users
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">
                <i class="fas fa-edit"></i>
                Edit User
            </a>
        </div>
    </div>

    <!-- User Profile Summary -->
    <div class="user-summary">
        <div class="user-avatar-section">
            @if($user->profile && $user->profile->avatar)
                <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" class="user-avatar-large">
            @else
                <div class="user-avatar-placeholder-large">
                    <i class="fas fa-user"></i>
                </div>
            @endif
            @if($user->is_admin)
                <div class="admin-badge-large">
                    <i class="fas fa-crown"></i>
                    <span>Admin</span>
                </div>
            @endif
        </div>

        <div class="user-info-section">
            <div class="user-header">
                <h2>{{ $user->name }}</h2>
                <div class="user-status">
                    @if($user->profile && $user->profile->is_private)
                        <span class="status-badge private">Private Profile</span>
                    @else
                        <span class="status-badge public">Public Profile</span>
                    @endif
                </div>
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
                    <i class="fas fa-calendar"></i>
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

    <!-- Statistics Cards -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="stat-number">{{ $user->posts->count() }}</div>
            <div class="stat-label">Posts</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="stat-number">{{ $user->followers->count() }}</div>
            <div class="stat-label">Followers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-number">{{ $user->follows->count() }}</div>
            <div class="stat-label">Following</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-camera"></i>
            </div>
            <div class="stat-number">{{ $user->stories->count() }}</div>
            <div class="stat-label">Stories</div>
        </div>
    </div>

    <!-- Recent Activity Tabs -->
    <div class="activity-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('posts')">Recent Posts</button>
            <button class="tab-btn" onclick="showTab('comments')">Recent Comments</button>
            <button class="tab-btn" onclick="showTab('stories')">Recent Stories</button>
        </div>

        <div id="posts-tab" class="tab-content active">
            @if($user->posts->count() > 0)
                <div class="posts-list">
                    @foreach($user->posts->take(10) as $post)
                    <div class="post-item">
                        <div class="post-content">
                            @if($post->content)
                                <p>{{ Str::limit($post->content, 150) }}</p>
                            @else
                                <p><em>No text content</em></p>
                            @endif
                        </div>
                        <div class="post-meta">
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            <span>{{ $post->likes->count() }} likes</span>
                            <a href="{{ route('posts.show', $post) }}" target="_blank" class="view-link">View Post</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-image"></i>
                    <h3>No posts yet</h3>
                    <p>This user hasn't created any posts.</p>
                </div>
            @endif
        </div>

        <div id="comments-tab" class="tab-content">
            @if($user->comments->count() > 0)
                <div class="comments-list">
                    @foreach($user->comments->take(10) as $comment)
                    <div class="comment-item">
                        <div class="comment-content">
                            <p>{{ $comment->content }}</p>
                        </div>
                        <div class="comment-meta">
                            <span>On post by {{ $comment->post->user->name }}</span>
                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                            <span>{{ $comment->likes->count() }} likes</span>
                            <a href="{{ route('posts.show', $comment->post) }}" target="_blank" class="view-link">View Post</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No comments yet</h3>
                    <p>This user hasn't posted any comments.</p>
                </div>
            @endif
        </div>

        <div id="stories-tab" class="tab-content">
            @if($user->stories->count() > 0)
                <div class="stories-grid">
                    @foreach($user->stories->take(12) as $story)
                    <div class="story-item">
                        @if($story->media_type === 'image')
                            <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="story-preview">
                        @elseif($story->media_type === 'video')
                            <video class="story-preview" muted>
                                <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                            </video>
                        @endif
                        <div class="story-overlay">
                            <div class="story-stats">
                                <span><i class="fas fa-eye"></i> {{ $story->views->count() }}</span>
                                <span><i class="fas fa-heart"></i> {{ $story->reactions->count() }}</span>
                            </div>
                            <div class="story-date">{{ $story->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-camera"></i>
                    <h3>No stories yet</h3>
                    <p>This user hasn't created any stories.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.admin-page {
    max-width: 1000px;
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

/* User Summary */
.user-summary {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 24px;
    display: flex;
    gap: 24px;
    box-shadow: var(--shadow);
}

.user-avatar-section {
    position: relative;
    flex-shrink: 0;
}

.user-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--border-color);
}

.user-avatar-placeholder-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 48px;
    border: 4px solid var(--border-color);
}

.admin-badge-large {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.user-info-section {
    flex: 1;
}

.user-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.user-header h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.private {
    background: #6c757d;
    color: white;
}

.status-badge.public {
    background: #28a745;
    color: white;
}

.user-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-gray);
    font-size: 14px;
}

.meta-item i {
    width: 16px;
    color: var(--twitter-blue);
}

.meta-item a {
    color: var(--twitter-blue);
    text-decoration: none;
}

.meta-item a:hover {
    text-decoration: underline;
}

.user-bio {
    margin-top: 16px;
}

.user-bio p {
    margin: 0;
    font-size: 16px;
    line-height: 1.5;
    color: var(--twitter-dark);
}

/* Statistics */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: var(--shadow);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin: 0 auto 12px auto;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    color: var(--twitter-gray);
    font-weight: 500;
}

/* Activity Tabs */
.activity-tabs {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.tab-buttons {
    display: flex;
    border-bottom: 1px solid var(--border-color);
}

.tab-btn {
    flex: 1;
    padding: 16px 20px;
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 600;
    color: var(--twitter-gray);
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn.active {
    color: var(--twitter-blue);
    border-bottom-color: var(--twitter-blue);
    background: var(--twitter-light);
}

.tab-btn:hover:not(.active) {
    background: var(--twitter-light);
    color: var(--twitter-dark);
}

.tab-content {
    display: none;
    padding: 20px;
    min-height: 200px;
}

.tab-content.active {
    display: block;
}

/* Posts List */
.posts-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.post-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 16px;
    background: var(--twitter-light);
}

.post-content p {
    margin: 0 0 12px 0;
    color: var(--twitter-dark);
}

.post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--twitter-gray);
}

.view-link {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 500;
}

.view-link:hover {
    text-decoration: underline;
}

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.comment-item {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 16px;
    background: var(--twitter-light);
}

.comment-content p {
    margin: 0 0 12px 0;
    color: var(--twitter-dark);
    background: var(--card-bg);
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid var(--twitter-blue);
}

.comment-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--twitter-gray);
    flex-wrap: wrap;
    gap: 8px;
}

/* Stories Grid */
.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
}

.story-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 9/16;
    background: #000;
    cursor: pointer;
}

.story-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.story-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 12px;
    color: white;
}

.story-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 4px;
}

.story-date {
    font-size: 10px;
    opacity: 0.8;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
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
    padding: 10px 16px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
    padding: 10px 16px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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

    .user-summary {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }

    .user-header {
        flex-direction: column;
        gap: 12px;
    }

    .stats-overview {
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
        border-right-color: var(--twitter-blue);
        border-bottom-color: transparent;
    }

    .stories-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => button.classList.remove('active'));

    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');

    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>
@endsection
