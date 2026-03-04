@extends('layouts.app')

@section('title', 'Search')

@section('content')
<style>
.search-container { max-width: 680px; margin: 0 auto; }

.search-box {
    position: relative; margin-bottom: 32px;
}
.search-input {
    width: 100%; padding: 16px 20px 16px 56px; font-size: 16px;
    border: 1px solid var(--border); border-radius: var(--radius-lg);
    background: var(--surface); color: var(--text); transition: all var(--transition);
}
.search-input:focus {
    outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}
.search-icon {
    position: absolute; left: 20px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 18px;
}

.search-results { display: flex; flex-direction: column; gap: 12px; }
.user-card { 
    display: flex; align-items: center; gap: 16px; padding: 16px 20px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
    transition: all var(--transition);
}
.user-card:hover { border-color: var(--primary); }
.user-avatar { 
    width: 56px; height: 56px; border-radius: 50%; overflow: hidden;
    background: linear-gradient(135deg, var(--primary), var(--secondary)); flex-shrink: 0;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-avatar .placeholder { 
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; color: white;
}
.user-info { flex: 1; }
.user-info a { text-decoration: none; }
.user-name { font-size: 16px; font-weight: 600; color: var(--text); }
.user-name:hover { color: var(--primary); }
.user-meta { font-size: 13px; color: var(--text-muted); }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; opacity: 0.5; }

.recent-searches { margin-bottom: 32px; }
.recent-searches h3 { font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; }
.search-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.search-tag {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-full);
    color: var(--text); font-size: 14px; cursor: pointer; transition: all var(--transition);
}
.search-tag:hover { border-color: var(--primary); color: var(--primary); }
</style>

<div class="search-container">
    <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="search-input" class="search-input" placeholder="Search users..." autocomplete="off">
    </div>

    <div id="search-results" class="search-results">
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Search for users</h3>
            <p style="color: var(--text-muted);">Type a username to find people.</p>
        </div>
    </div>
</div>

<script>
let searchTimeout;
const searchInput = document.getElementById('search-input');
const resultsContainer = document.getElementById('search-results');

searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        resultsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Search for users</h3>
                <p style="color: var(--text-muted);">Type at least 2 characters.</p>
            </div>
        `;
        return;
    }
    
    resultsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Searching...</p></div>';
    
    searchTimeout = setTimeout(() => {
        fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.users && data.users.length > 0) {
                resultsContainer.innerHTML = data.users.map(user => `
                    <div class="user-card">
                        <a href="/users/${user.username}" class="user-avatar">
                            <img src="${escapeHtml(user.avatar_url)}" alt="${escapeHtml(user.username)}">
                        </a>
                        <div class="user-info">
                            <a href="/users/${user.username}">
                                <div class="user-name">${escapeHtml(user.name)}</div>
                            </a>
                            <div class="user-meta">@${escapeHtml(user.username)}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                resultsContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h3>No users found</h3>
                        <p style="color: var(--text-muted);">Try a different search term.</p>
                    </div>
                `;
            }
        })
        .catch(() => {
            resultsContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Search failed</h3>
                    <p style="color: var(--text-muted);">Please try again.</p>
                </div>
            `;
        });
    }, 300);
});

searchInput.focus();
</script>
@endsection
