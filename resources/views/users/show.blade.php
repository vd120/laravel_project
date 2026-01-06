@extends('layouts.app')

@section('content')
<div class="user-profile">
    @if($user->profile && $user->profile->cover_image)
        <div class="cover-image">
            <img src="{{ asset('storage/' . $user->profile->cover_image) }}" alt="Cover Image" class="cover-img">
        </div>
    @endif

    <div class="profile-header">
        @if($user->profile && $user->profile->avatar)
            <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" class="avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="avatar-placeholder" style="display: none;">
                <i class="fas fa-user"></i>
            </div>
        @else
            <div class="avatar-placeholder">
                <i class="fas fa-user"></i>
            </div>
        @endif

        <div class="profile-info">
            <h2>{{ $user->name }}
                @if(auth()->id() !== $user->id && !auth()->user()->isBlocking($user) && !$user->isBlocking(auth()->user()))
                    <button type="button"
                            class="btn follow-btn {{ auth()->user()->isFollowing($user) ? 'following' : '' }}"
                            data-user-id="{{ $user->id }}"
                            data-username="{{ $user->name }}"
                            onclick="toggleFollow(this, {{ $user->id }})"
                            style="font-size: 12px; padding: 4px 10px; margin-left: 12px; background: {{ auth()->user()->isFollowing($user) ? '#28a745' : 'var(--twitter-blue)' }};">
                        {{ auth()->user()->isFollowing($user) ? 'Following' : 'Follow' }}
                    </button>
                @endif
                @if($user->is_suspended)
                    <span class="suspension-badge">
                        <i class="fas fa-exclamation-triangle"></i> Suspended
                    </span>
                @endif
                @if($user->profile && $user->profile->is_private)
                    <span class="privacy-badge private">
                        <i class="fas fa-lock"></i> Private
                    </span>
                @else
                    <span class="privacy-badge public">
                        <i class="fas fa-globe"></i> Public
                    </span>
                @endif
                @if(auth()->user()->isBlocking($user))
                    <span class="block-indicator blocked-by-you">
                        <i class="fas fa-ban"></i> Blocked
                    </span>
                @elseif($user->isBlocking(auth()->user()))
                    <span class="block-indicator blocked-you">
                        <i class="fas fa-user-slash"></i> Blocking you
                    </span>
                @endif
            </h2>

            @if(auth()->id() === $user->id)
                <div class="user-details" style="margin: 10px 0;">
                    <div class="user-email" style="padding: 14px 18px; background: var(--card-bg); border-radius: 12px; border-left: 4px solid var(--twitter-blue); border: 2px solid var(--border-color);">
                        <strong style="color: var(--twitter-blue); font-weight: 600;"><i class="fas fa-envelope"></i> Email:</strong>
                        <span style="margin-left: 8px; color: var(--twitter-dark);">{{ $user->email }}</span>
                    </div>
                </div>
            @endif

            @if($user->profile)
                @if($user->profile->bio)
                    <p class="bio">{{ $user->profile->bio }}</p>
                @endif

                <div class="profile-details">
                    @if($user->profile->location)
                        <span><i class="fas fa-map-marker-alt"></i> {{ $user->profile->location }}</span>
                    @endif

                    @if($user->profile->website)
                        <span><i class="fas fa-link"></i> <a href="{{ $user->profile->website }}" target="_blank">{{ $user->profile->website }}</a></span>
                    @endif

                    @if($user->profile->occupation)
                        <span><i class="fas fa-briefcase"></i> {{ $user->profile->occupation }}</span>
                    @endif

                    @if($user->profile->birth_date)
                        <span><i class="fas fa-birthday-cake"></i> {{ \Carbon\Carbon::parse($user->profile->birth_date)->format('M d, Y') }}</span>
                    @endif

                    @if($user->profile->gender)
                        <span><i class="fas fa-venus-mars"></i> {{ ucfirst($user->profile->gender) }}</span>
                    @endif
                </div>

                @if($user->profile->about)
                    <div class="about-section">
                        <h3>About</h3>
                        <p>{{ $user->profile->about }}</p>
                    </div>
                @endif

                @if($user->profile->social_links && count($user->profile->social_links) > 0)
                    <div class="social-links">
                        <h3>Social Links</h3>
                        @foreach($user->profile->social_links as $platform => $url)
                            @if($url)
                                <a href="{{ $url }}" target="_blank" class="social-link">
                                    <i class="fab fa-{{ strtolower($platform) }}"></i> {{ ucfirst($platform) }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif

            <p class="stats">
                <a href="{{ route('users.followers', $user) }}">Followers: <span data-user-followers="{{ $user->id }}">{{ $user->followers->count() }}</span></a> |
                <a href="{{ route('users.following', $user) }}">Following: <span data-user-following="{{ $user->id }}">{{ $user->follows->count() }}</span></a>
            </p>
        </div>
    </div>

    <div style="margin: 15px 0;">
        @if(auth()->id() === $user->id)
            <a href="{{ route('profile.edit', $user) }}" class="btn">Edit Profile</a>
        @else
            @if(auth()->user()->isBlocking($user))
                
                <button type="button" class="btn unblock-btn" data-user-id="{{ $user->id }}" data-username="{{ $user->name }}" onclick="toggleBlock(this)">Unblock</button>
                <p style="color: #6c757d; font-size: 14px; margin: 5px 0;">You've blocked this user. They can't see your posts or interact with you.</p>
            @elseif($user->isBlocking(auth()->user()))
                <p style="color: #856404; font-size: 14px; margin: 5px 0; background: #fff3cd; padding: 8px; border-radius: 4px; border: 1px solid #ffeaa7;">
                    <i class="fas fa-info-circle"></i> This user has blocked you. You can't interact with them.
                </p>
            @else
                <button type="button" class="btn" data-user-id="{{ $user->id }}" data-username="{{ $user->name }}" style="background: #dc3545;" onclick="toggleBlock(this)">Block</button>
            @endif
        @endif
    </div>

    @if($blocked && $blocked->count() > 0)
    <div class="blocked-users-section" style="margin: 20px 0; padding: 16px; background: var(--card-bg); border-radius: 16px; border: 2px solid var(--border-color); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: var(--twitter-dark);">Blocked Users</h3>
        <div class="blocked-users-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
            @foreach($blocked as $block)
                <div class="blocked-user-card" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--hover-bg); border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.2s ease;" onmouseover="this.style.background='var(--card-bg)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='var(--hover-bg)'; this.style.transform='translateY(0)';">
                    @if($block->blocked->profile && $block->blocked->profile->avatar)
                        <img src="{{ asset('storage/' . $block->blocked->profile->avatar) }}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                    @else
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--twitter-blue); display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 2px solid var(--border-color);">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif

                    <div style="flex: 1; min-width: 0;">
                        <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: var(--twitter-dark);">
                            <a href="{{ route('users.show', $block->blocked) }}" style="color: var(--twitter-dark); text-decoration: none;">{{ $block->blocked->name }}</a>
                        </h4>
                        @if($block->blocked->profile && $block->blocked->profile->bio)
                            <p style="margin: 0; font-size: 12px; color: var(--twitter-gray); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ Str::limit($block->blocked->profile->bio, 80) }}
                            </p>
                        @endif
                        <small style="color: #6c757d; font-size: 11px;">Blocked {{ $block->created_at->diffForHumans() }}</small>
                    </div>

                    <button type="button" class="btn" data-user-id="{{ $block->blocked->id }}" data-username="{{ $block->blocked->name }}" style="background: #ffc107; color: #212529; border: none; border-radius: 16px; padding: 6px 12px; font-size: 12px; font-weight: 500; cursor: pointer;" onclick="toggleBlock(this)">Unblock</button>
                </div>
            @endforeach
        </div>
    </div>
    @endif

<style>
.user-profile {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px;
}

/* Cover Image */
.cover-image {
    margin-bottom: 20px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.cover-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

/* Profile Header */
.profile-header {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    position: relative;
}

.avatar,
.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: var(--shadow);
    flex-shrink: 0;
}

.avatar {
    object-fit: cover;
}

.avatar-placeholder {
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
}

.profile-info {
    flex: 1;
    min-width: 0;
}

.profile-info h2 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.privacy-badge {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.privacy-badge.private {
    background: #dc3545;
    color: white;
}

.privacy-badge.public {
    background: var(--twitter-blue);
    color: white;
}

.suspension-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    }
    50% {
        box-shadow: 0 4px 8px rgba(255, 107, 107, 0.5);
    }
    100% {
        box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    }
}

.block-indicator {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
}

.block-indicator.blocked-by-you {
    background: #dc3545;
    color: white;
}

.block-indicator.blocked-you {
    background: #ffc107;
    color: #212529;
}

.bio {
    margin: 12px 0;
    font-size: 16px;
    line-height: 1.5;
    color: var(--twitter-dark);
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 16px 0;
    font-size: 14px;
    color: var(--twitter-gray);
}

.profile-details span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.profile-details a {
    color: var(--twitter-blue);
    text-decoration: none;
}

.profile-details a:hover {
    text-decoration: underline;
}

.about-section,
.social-links {
    margin: 20px 0;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 16px;
    border: 2px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.about-section h3,
.social-links h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.about-section p {
    margin: 0;
    line-height: 1.5;
    color: var(--twitter-dark);
}

.social-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.social-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: var(--twitter-blue);
    text-decoration: none;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.social-link:hover {
    background: var(--twitter-light);
    text-decoration: none;
    color: var(--twitter-blue);
}

.stats {
    margin: 16px 0;
    font-size: 14px;
    color: var(--twitter-gray);
}

.stats a {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 500;
}

.stats a:hover {
    text-decoration: underline;
}

/* Action Buttons */
.user-profile .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 8px;
    margin-bottom: 8px;
}

.follow-btn:not(.following) {
    background: var(--twitter-blue);
    color: white;
}

.follow-btn.following {
    background: #28a745;
    color: white;
}

.unblock-btn {
    background: #ffc107;
    color: #212529;
}

/* Posts */
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
    box-shadow: var(--shadow);
}

.post small {
    color: var(--twitter-gray);
    font-size: 12px;
}

.post .content {
    margin: 12px 0;
    line-height: 1.5;
    font-size: 16px;
}

.post .btn {
    margin-right: 8px;
    margin-bottom: 8px;
}

.like-btn {
    background: var(--twitter-blue);
    color: white;
}

.like-btn.liked {
    background: #dc3545;
}

.comment-form-container {
    margin: 16px 0;
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.comment-form-container textarea {
    flex: 1;
    padding: 12px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    min-height: 60px;
}

.comment-form-container textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
}

.private-post-placeholder {
    text-align: center;
    padding: 40px 20px;
    background: var(--hover-bg);
    border-radius: 16px;
    border: 2px dashed var(--border-color);
}

.private-post-placeholder i {
    font-size: 48px;
    color: var(--twitter-gray);
    margin-bottom: 16px;
}

.private-post-placeholder h4 {
    color: var(--twitter-gray);
    margin: 0 0 8px 0;
    font-size: 18px;
}

.private-post-placeholder p {
    color: var(--twitter-gray);
    margin: 0 0 16px 0;
    font-size: 14px;
}

.private-post-placeholder .btn {
    margin-top: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .user-profile {
        padding: 12px;
    }

    .cover-img {
        height: 150px;
    }

    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
    }

    .avatar,
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        border-width: 3px;
    }

    .profile-info {
        text-align: left;
    }

    .profile-info h2 {
        font-size: 24px;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .bio {
        font-size: 15px;
    }

    .profile-details {
        align-items: center;
    }

    .stats {
        text-align: center;
        font-size: 13px;
    }

    .about-section,
    .social-links {
        padding: 12px;
    }

    .social-links {
        gap: 6px;
    }

    .social-link {
        padding: 6px 10px;
        font-size: 14px;
    }

    .user-profile .btn {
        padding: 8px 16px;
        font-size: 13px;
        margin-right: 6px;
        margin-bottom: 6px;
    }
}

@media (max-width: 480px) {
    .cover-img {
        height: 120px;
        border-radius: 8px;
    }

    .profile-info h2 {
        font-size: 20px;
        flex-direction: column;
        gap: 6px;
    }

    .privacy-badge,
    .block-indicator {
        font-size: 10px;
        padding: 2px 6px;
    }

    .bio {
        font-size: 14px;
    }

    .profile-details {
        font-size: 13px;
        gap: 6px;
    }

    .profile-details span {
        justify-content: center;
    }

    .about-section h3,
    .social-links h3 {
        font-size: 16px;
    }

    .about-section p {
        font-size: 14px;
    }

    .stats {
        font-size: 12px;
    }

    .post {
        padding: 12px;
        margin-bottom: 16px;
    }

    .post .content {
        font-size: 15px;
    }

    .comment-form-container {
        flex-direction: column;
        gap: 6px;
    }

    .comment-form-container textarea {
        font-size: 13px;
        min-height: 50px;
    }

    .private-post-placeholder {
        padding: 30px 16px;
    }

    .private-post-placeholder i {
        font-size: 36px;
        margin-bottom: 12px;
    }

    .private-post-placeholder h4 {
        font-size: 16px;
    }

    .private-post-placeholder p {
        font-size: 13px;
    }
}

@media (max-width: 360px) {
    .user-profile {
        padding: 8px;
    }

    .avatar,
    .avatar-placeholder {
        width: 80px;
        height: 80px;
        border-width: 2px;
    }

    .profile-info h2 {
        font-size: 18px;
    }

    .bio {
        font-size: 13px;
    }

    .profile-details {
        font-size: 12px;
    }

    .user-profile .btn {
        padding: 6px 12px;
        font-size: 12px;
    }

    .post {
        padding: 8px;
    }

    .post .content {
        font-size: 14px;
    }
}
</style>
@endsection
