/* Chat Index Page JavaScript */

function showUserSearch() {
    document.getElementById('userSearchModal').style.display = 'flex';
    setTimeout(() => document.getElementById('userSearch').focus(), 100);
}

function hideUserSearch() {
    document.getElementById('userSearchModal').style.display = 'none';
}

function filterSidebarConversations(query) {
    const items = document.querySelectorAll('#sidebarConvList .conversation-item');
    const q = query.toLowerCase();
    items.forEach(item => {
        const name = item.getAttribute('data-name')?.toLowerCase() || '';
        item.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

(function() {
    const userSearchInput = document.getElementById('userSearch');
    if (!userSearchInput) return;

    userSearchInput.addEventListener('input', function() {
        const query = this.value.trim();
        const resultsDiv = document.getElementById('userResults');
        if (query.length < 2) { resultsDiv.innerHTML = ''; return; }

        fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                resultsDiv.innerHTML = data.users.map(u => `
                    <div class="result-user" onclick="startChat(${u.id})">
                        <img src="${escapeHtml(u.avatar_url)}">
                        <div class="result-user-info">
                            <div class="result-user-name">${escapeHtml(u.username)}</div>
                            ${u.name && u.name !== u.username ? `<div class="result-user-fullname">${escapeHtml(u.name)}</div>` : ''}
                        </div>
                    </div>
                `).join('');
            }
        });
    });
})();

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function startChat(userId) { window.location.href = '/chat/start/' + userId; }
function startChatWithUser(userId) { window.location.href = '/chat/start/' + userId; }

document.addEventListener('DOMContentLoaded', () => {
    // Realtime.js will auto-initialize
});
