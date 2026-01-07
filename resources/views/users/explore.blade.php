@extends('layouts.app')

@section('content')
<div class="explore-page">
    <div class="page-header">
        <h1>Explore Users</h1>
        <a href="{{ route('home') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to feed</span>
        </a>
    </div>

    
    <div class="search-section">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       id="user-search"
                       class="search-input"
                       placeholder="Search users by name or username..."
                       autocomplete="off">
                <button type="button" class="search-clear" id="search-clear" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-results" id="search-results" style="display: none;">
                <div class="search-loading" id="search-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Searching...</span>
                </div>
                <div class="search-empty" id="search-empty" style="display: none;">
                    <i class="fas fa-search"></i>
                    <span>No users found</span>
                </div>
                <div class="search-list" id="search-list"></div>
            </div>
        </div>
    </div>



    @if($users->count() > 0)
        <div class="users-list">
            @foreach($users as $user)
                <div class="user-card">
                    <div class="user-avatar-section">
                        @if($user->profile && $user->profile->avatar)
                            <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" class="user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="user-avatar-placeholder" style="display: none;">
                                <i class="fas fa-user"></i>
                            </div>
                        @else
                            <div class="user-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>

                    <div class="user-content">
                        <div class="user-header">
                            <h3 class="user-name">
                                <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                                @if($user->is_suspended)
                                    <span class="suspension-badge">
                                        <i class="fas fa-exclamation-triangle"></i> Suspended
                                    </span>
                                @endif
                                @if($user->profile && $user->profile->is_private)
                                    <span class="privacy-badge private">
                                        <i class="fas fa-lock"></i> Private
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
                            </h3>
                        </div>

                        @if($user->profile && $user->profile->bio)
                            <p class="user-bio">{{ $user->profile->bio }}</p>
                        @endif

                        <div class="user-stats">
                            <span class="stat-item"><strong data-user-followers="{{ $user->id }}">{{ $user->followers_count ?? 0 }}</strong> Followers</span>
                            <span class="stat-item"><strong data-user-following="{{ $user->id }}">{{ $user->follows_count ?? 0 }}</strong> Following</span>
                        </div>
                    </div>

                    <div class="user-actions">
                        @if(in_array($user->id, $blockedByCurrentUser))
                            <div class="action-buttons">
                                <button type="button" class="btn unblock-btn" data-user-id="{{ $user->id }}" data-username="{{ $user->name }}" onclick="toggleBlock(this)">Unblock</button>
                            </div>
                        @elseif(in_array($user->id, $blockedCurrentUser))
                            <div class="cannot-interact">
                                <i class="fas fa-ban"></i>
                                <span>Blocked</span>
                            </div>
                        @else
                            <div class="action-buttons">
                                <button type="button"
                                        class="btn follow-btn {{ auth()->user()->isFollowing($user) ? 'following' : '' }}"
                                        data-user-id="{{ $user->id }}"
                                        data-username="{{ $user->name }}"
                                        onclick="toggleFollow(this, {{ $user->id }})">
                                    {{ auth()->user()->isFollowing($user) ? 'Following' : 'Follow' }}
                                </button>
                                <button type="button" class="btn block-btn" data-user-id="{{ $user->id }}" data-username="{{ $user->name }}" onclick="toggleBlock(this)">Block</button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($users->hasPages())
            <div style="text-align: center; margin-top: 20px;">
                {{ $users->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>No users to explore</h3>
            <p>Check back later for new users!</p>
        </div>
    @endif
</div>

<style>
.explore-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 16px;
}

.page-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}



.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--twitter-dark);
}

.back-link i {
    font-size: 12px;
}

/* ===== USER SEARCH STYLES ===== */
.search-section {
    margin-bottom: 24px;
}

.search-container {
    position: relative;
    max-width: 100%;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--twitter-gray);
    font-size: 16px;
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 14px 50px 14px 48px;
    border: 2px solid var(--border-color);
    border-radius: 24px;
    font-size: 16px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    outline: none;
    transition: all 0.3s ease;
    font-family: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-input:focus {
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.search-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
}

.search-clear:hover {
    background: var(--twitter-light);
    color: var(--twitter-dark);
}

.search-results {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 4px 16px rgba(29, 161, 242, 0.1);
    max-height: 320px;
    overflow-y: auto;
    z-index: 10;
    backdrop-filter: blur(8px);
}

.search-loading,
.search-empty {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    color: var(--twitter-gray);
    font-size: 14px;
}

.search-loading i {
    animation: spin 1s linear infinite;
}

.search-list {
    max-height: 280px;
    overflow-y: auto;
}

.search-user-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

.search-user-item:last-child {
    border-bottom: none;
}

.search-user-item:hover {
    background: var(--twitter-light);
}

.search-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--border-color);
    flex-shrink: 0;
}

.search-user-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--twitter-light);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 12px;
    flex-shrink: 0;
}

.search-user-info {
    flex: 1;
    min-width: 0;
}

.search-user-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--twitter-dark);
    margin-bottom: 2px;
    display: block;
}

.search-user-username {
    font-size: 12px;
    color: var(--twitter-gray);
    display: block;
}

.search-user-bio {
    font-size: 12px;
    color: var(--twitter-gray);
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.users-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 16px;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-card:hover {
    box-shadow: 0 4px 16px rgba(29, 161, 242, 0.1), 0 8px 32px rgba(0,0,0,0.3);
    transform: translateY(-2px);
    border-color: var(--focus-border);
}

.user-avatar-section {
    flex-shrink: 0;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.user-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--twitter-light);
    border: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 18px;
}

.user-content {
    flex: 1;
    min-width: 0;
}

.user-header {
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.user-name a {
    color: var(--twitter-dark);
    text-decoration: none;
    transition: color 0.2s ease;
}

.user-name a:hover {
    color: var(--twitter-blue);
}

.privacy-badge.private {
    background: #dc3545;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
}

.suspension-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
    text-transform: uppercase;
    box-shadow: 0 1px 3px rgba(255, 107, 107, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 1px 3px rgba(255, 107, 107, 0.3);
    }
    50% {
        box-shadow: 0 2px 4px rgba(255, 107, 107, 0.5);
    }
    100% {
        box-shadow: 0 1px 3px rgba(255, 107, 107, 0.3);
    }
}

.block-indicator {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
}

.block-indicator.blocked-by-you {
    background: #dc3545;
    color: white;
}

.block-indicator.blocked-you {
    background: #ffc107;
    color: #212529;
}

.user-bio {
    margin: 4px 0 8px 0;
    font-size: 14px;
    color: var(--twitter-gray);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.user-stats {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--twitter-gray);
}

.stat-item strong {
    color: var(--twitter-dark);
}

.user-actions {
    flex-shrink: 0;
    min-width: 120px;
}

.follow-btn,
.unblock-btn,
.block-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
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
    width: 100%;
}

.block-btn {
    background: #dc3545;
    color: white;
}

.follow-btn:hover,
.unblock-btn:hover,
.block-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
}

.action-buttons .follow-btn,
.action-buttons .block-btn {
    width: 100%;
}

.cannot-interact {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--twitter-gray);
    text-align: center;
    padding: 8px;
}

.cannot-interact i {
    font-size: 16px;
    opacity: 0.7;
}

.empty-state {
    text-align: center;
    padding: 48px 16px;
    color: var(--twitter-gray);
}

.empty-icon {
    margin-bottom: 16px;
}

.empty-icon i {
    font-size: 48px;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: var(--twitter-dark);
    font-size: 18px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .explore-page {
        padding: 12px;
    }

    .page-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
    }

.page-header h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
}



@media (max-width: 360px) {
    .user-card {
        flex-direction: column;
        align-items: stretch;
    }

    .user-avatar-section {
        align-self: center;
        margin-bottom: 8px;
    }

    .user-actions {
        margin-top: 12px;
        min-width: unset;
    }

    .action-buttons {
        flex-direction: row;
        gap: 8px;
    }

    .action-buttons .follow-btn,
    .action-buttons .block-btn {
        flex: 1;
    }

    .cannot-interact {
        flex-direction: row;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search');
    const searchResults = document.getElementById('search-results');
    const searchList = document.getElementById('search-list');
    const searchLoading = document.getElementById('search-loading');
    const searchEmpty = document.getElementById('search-empty');
    const searchClear = document.getElementById('search-clear');

    let searchTimeout = null;
    let currentSearchRequest = null;

    // Show/hide clear button based on input
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length > 0) {
            searchClear.style.display = 'flex';
            searchResults.style.display = 'block';

            // Debounce search
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        } else {
            searchClear.style.display = 'none';
            searchResults.style.display = 'none';
        }
    });

    // Clear search
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        searchResults.style.display = 'none';
        searchInput.focus();
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Search function
    function performSearch(query) {
        // Cancel previous request if still pending
        if (currentSearchRequest) {
            currentSearchRequest.abort();
        }

        // Show loading
        searchLoading.style.display = 'flex';
        searchEmpty.style.display = 'none';
        searchList.innerHTML = '';

        // Create new AbortController
        const controller = new AbortController();
        currentSearchRequest = controller;

        // Make API request
        fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            signal: controller.signal
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            searchLoading.style.display = 'none';

            if (data.success && data.users && data.users.length > 0) {
                displaySearchResults(data.users);
            } else {
                searchEmpty.style.display = 'flex';
            }
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                return; // Request was cancelled
            }

            console.error('Search error:', error);
            searchLoading.style.display = 'none';
            searchEmpty.style.display = 'flex';
            searchEmpty.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Search error</span>';
        });
    }

    // Display search results
    function displaySearchResults(users) {
        searchList.innerHTML = '';

        users.forEach(user => {
            const userItem = document.createElement('div');
            userItem.className = 'search-user-item';
            userItem.onclick = () => {
                window.location.href = `/users/${encodeURIComponent(user.name)}`;
            };

            const avatarHtml = user.profile && user.profile.avatar
                ? `<img src="/storage/${user.profile.avatar}" alt="Avatar" class="search-user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                   <div class="search-user-avatar-placeholder" style="display: none;"><i class="fas fa-user"></i></div>`
                : `<div class="search-user-avatar-placeholder"><i class="fas fa-user"></i></div>`;

            const bioHtml = user.profile && user.profile.bio
                ? `<span class="search-user-bio">${user.profile.bio}</span>`
                : '';

            userItem.innerHTML = `
                ${avatarHtml}
                <div class="search-user-info">
                    <span class="search-user-name">${user.name}</span>
                    <span class="search-user-username">@${user.username || user.name.toLowerCase().replace(/\s+/g, '')}</span>
                    ${bioHtml}
                </div>
            `;

            searchList.appendChild(userItem);
        });
    }

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchResults.style.display = 'none';
            searchInput.blur();
        }
    });
});
</script>
@endsection
