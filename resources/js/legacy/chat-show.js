/* Chat Show Functions */

(function() {
    'use strict';

    window.toggleSidebar = function() {
        const sidebar = document.getElementById('chatSidebar');
        if (sidebar) sidebar.classList.toggle('active');
    };

    window.filterSidebarConversations = function(q) {
        const items = document.querySelectorAll('#sidebarConvList .conversation-item');
        const query = q.toLowerCase();
        items.forEach(item => {
            const name = item.getAttribute('data-name')?.toLowerCase() || '';
            item.style.display = name.includes(query) ? 'flex' : 'none';
        });
    };

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

    window.startChat = function(id) {
        window.location.href = '/chat/start/' + id;
    };

    window.escapeHtml = function(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    };

    // User search
    (function() {
        const userSearchInput = document.getElementById('userSearch');
        if (!userSearchInput) return;

        userSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            const results = document.getElementById('userResults');
            if (query.length < 2) {
                if (results) results.innerHTML = '';
                return;
            }

            fetch('/api/search-users?q=' + encodeURIComponent(query), {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.users.length) {
                    if (results) {
                        results.innerHTML = data.users.map(u => 
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
