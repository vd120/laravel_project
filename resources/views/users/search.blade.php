@extends('layouts.app')

@section('content')
<div class="search-page">
    <div class="page-header">
        <h1>Search Users</h1>
        <a href="{{ route('home') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to feed</span>
        </a>
    </div>

    <!-- Enhanced Search Section -->
    <div class="search-section">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       id="user-search"
                       class="search-input"
                       placeholder="Search users by username..."
                       autocomplete="off">
                <button type="button" class="search-clear" id="search-clear" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-results" id="search-results" style="display: none;">
                <div class="search-loading" id="search-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Searching users...</span>
                </div>
                <div class="search-empty" id="search-empty" style="display: none;">
                    <i class="fas fa-search"></i>
                    <span>No users found matching your search</span>
                </div>
                <div class="search-list" id="search-list"></div>
            </div>
        </div>


    </div>
</div>

<style>
.search-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 16px;
}

.page-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
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

/* ===== ENHANCED SEARCH STYLES ===== */
.search-section {
    margin-bottom: 24px;
}

.search-container {
    position: relative;
    max-width: 100%;
    margin-bottom: 20px;
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
    padding: 14px 50px 14px 52px;
    border: 2px solid var(--border-color);
    border-radius: 28px;
    font-size: 16px;
    background: white;
    color: var(--twitter-dark);
    outline: none;
    transition: all 0.3s ease;
    font-family: inherit;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-input:focus {
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.15);
    transform: translateY(-1px);
}

.search-clear {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--twitter-light);
    border: none;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.search-clear:hover {
    background: var(--twitter-gray);
    color: white;
    transform: translateY(-50%) scale(1.1);
}

.search-results {
    position: absolute;
    top: calc(100% + 12px);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    max-height: 320px;
    overflow-y: auto;
    z-index: 20;
    animation: slideDown 0.3s ease-out;
}

.search-loading,
.search-empty {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    color: var(--twitter-gray);
    font-size: 15px;
    justify-content: center;
}

.search-loading i {
    animation: spin 1s linear infinite;
    font-size: 18px;
}

.search-empty i {
    font-size: 20px;
    opacity: 0.6;
}

.search-list {
    max-height: 300px;
    overflow-y: auto;
}

/* Enhanced Search User Items */
.search-user-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

.search-user-item:last-child {
    border-bottom: none;
}

.search-user-item:hover {
    background: linear-gradient(135deg, var(--twitter-light), rgba(29, 161, 242, 0.05));
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(29, 161, 242, 0.1);
}

.search-user-item:hover::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--twitter-blue);
    border-radius: 0 2px 2px 0;
}

.search-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
    flex-shrink: 0;
    transition: border-color 0.2s ease;
}

.search-user-item:hover .search-user-avatar {
    border-color: var(--twitter-blue);
    transform: scale(1.05);
}

.search-user-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-light), #e1e8ed);
    border: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 16px;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.search-user-item:hover .search-user-avatar-placeholder {
    border-color: var(--twitter-blue);
    background: linear-gradient(135deg, var(--twitter-blue), var(--twitter-light));
    color: white;
    transform: scale(1.05);
}

.search-user-info {
    flex: 1;
    min-width: 0;
}

.search-user-name {
    font-weight: 700;
    font-size: 16px;
    color: var(--twitter-dark);
    margin-bottom: 4px;
    display: block;
    transition: color 0.2s ease;
}

.search-user-item:hover .search-user-name {
    color: var(--twitter-blue);
}

.search-user-username {
    font-size: 14px;
    color: var(--twitter-blue);
    display: block;
    font-weight: 500;
    transition: color 0.2s ease;
}

.search-user-item:hover .search-user-username {
    color: var(--twitter-dark);
}

.search-user-bio {
    font-size: 13px;
    color: var(--twitter-gray);
    margin-top: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.3;
}

.privacy-badge {
    background: #dc3545;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
    margin-left: 6px;
}

/* Search Tips */
.search-tips {
    background: linear-gradient(135deg, var(--twitter-light), white);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.search-tips h4 {
    margin: 0 0 12px 0;
    color: var(--twitter-dark);
    font-size: 16px;
    font-weight: 600;
}

.search-tips ul {
    margin: 0;
    padding-left: 20px;
}

.search-tips li {
    margin-bottom: 6px;
    color: var(--twitter-gray);
    font-size: 14px;
    line-height: 1.4;
}

.search-tips li:last-child {
    margin-bottom: 0;
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .search-page {
        padding: 12px;
    }

    .page-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
    }

    .page-header h1 {
        font-size: 20px;
    }

    .back-link {
        font-size: 13px;
    }

    .search-input {
        padding: 12px 45px 12px 44px;
        font-size: 16px; /* Prevent zoom on iOS */
    }

    .search-icon {
        left: 14px;
        font-size: 14px;
    }

    .search-clear {
        right: 12px;
    }

    .search-results {
        top: calc(100% + 10px);
        max-height: 280px;
    }

    .search-user-item {
        padding: 12px 14px;
        gap: 12px;
    }

    .search-user-avatar,
    .search-user-avatar-placeholder {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }

    .search-user-name {
        font-size: 15px;
    }

    .search-user-username {
        font-size: 13px;
    }

    .search-user-bio {
        font-size: 12px;
    }

    .search-tips {
        padding: 16px;
        margin-top: 16px;
    }

    .search-tips h4 {
        font-size: 15px;
    }

    .search-tips li {
        font-size: 13px;
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
            searchEmpty.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Search error - please try again</span>';
        });
    }

    // Display search results
    function displaySearchResults(users) {
        searchList.innerHTML = '';

        users.forEach(user => {
            const userItem = document.createElement('div');
            userItem.className = 'search-user-item';
            userItem.onclick = () => {
                window.location.href = `/users/${user.username}`;
            };

            const avatarHtml = user.profile && user.profile.avatar
                ? `<img src="/storage/${user.profile.avatar}" alt="Avatar" class="search-user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                   <div class="search-user-avatar-placeholder" style="display: none;"><i class="fas fa-user"></i></div>`
                : `<div class="search-user-avatar-placeholder"><i class="fas fa-user"></i></div>`;

            const bioHtml = user.profile && user.profile.bio
                ? `<span class="search-user-bio">${user.profile.bio}</span>`
                : '';

            const privateBadge = user.profile && user.profile.is_private
                ? '<span class="privacy-badge">Private</span>'
                : '';

            userItem.innerHTML = `
                ${avatarHtml}
                <div class="search-user-info">
                    <span class="search-user-name">${user.name} ${privateBadge}</span>
                    <span class="search-user-username">@${user.username}</span>
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
