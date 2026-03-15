/* Chat Index Functions */

(function() {
    'use strict';

    window.showUserSearch = function() {
        const modal = document.getElementById('userSearchModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                const input = document.getElementById('userSearch');
                if (input) input.focus();
            }, 100);
        }
    };

    window.hideUserSearch = function() {
        const modal = document.getElementById('userSearchModal');
        if (modal) modal.style.display = 'none';
    };

    window.filterSidebarConversations = function(query) {
        const items = document.querySelectorAll('#sidebarConvList .conversation-item');
        const q = query.toLowerCase();
        items.forEach(item => {
            const name = item.getAttribute('data-name')?.toLowerCase() || '';
            item.style.display = name.includes(q) ? 'flex' : 'none';
        });
    };

    window.escapeHtml = function(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    };

    window.startChat = function(userId) {
        window.location.href = '/chat/start/' + userId;
    };

    window.startChatWithUser = function(userId) {
        window.location.href = '/chat/start/' + userId;
    };

    // User search
    (function() {
        const userSearchInput = document.getElementById('userSearch');
        if (!userSearchInput) return;

        userSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            const resultsDiv = document.getElementById('userResults');
            if (query.length < 2) {
                if (resultsDiv) resultsDiv.innerHTML = '';
                return;
            }

            fetch('/api/search-users?q=' + encodeURIComponent(query), {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (resultsDiv) {
                        resultsDiv.innerHTML = data.users.map(u =>
                            '<div class="result-user" onclick="startChat(' + u.id + ')">' +
                            '<img src="' + window.escapeHtml(u.avatar_url) + '">' +
                            '<div class="result-user-info">' +
                            '<div class="result-user-name">' + window.escapeHtml(u.username) + '</div>' +
                            (u.name && u.name !== u.username ? '<div class="result-user-fullname">' + window.escapeHtml(u.name) + '</div>' : '') +
                            '</div></div>'
                        ).join('');
                    }
                }
            });
        });
    })();
})();
