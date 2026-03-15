/* Groups Edit Page JavaScript */

let searchTimeout;

function searchUsers(query) {
    clearTimeout(searchTimeout);
    const results = document.getElementById('searchResults');

    if (query.length < 2) {
        results.innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`/search?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.users && data.users.length > 0) {
                results.innerHTML = data.users.map(user => `
                    <div class="search-result-item" onclick="addMember(${window.currentGroupId}, ${user.id})">
                        <span>${user.name}</span>
                    </div>
                `).join('');
            } else {
                results.innerHTML = '<div class="search-result-item">No users found</div>';
            }
        })
        .catch(() => {
            results.innerHTML = '';
        });
    }, 300);
}

function addMember(groupId, userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/groups/${groupId}/members`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast(window.chatTranslations?.failed_to_add_member || window.groupTranslations?.failed_to_add_member || 'Failed to add member', 'error');
        }
    })
    .catch(() => {
        showToast(window.chatTranslations?.error_adding_member || window.groupTranslations?.error_adding_member || 'Error adding member', 'error');
    });
}

function removeMember(groupId, userId) {
    if (!confirm('Remove this member from the group?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/groups/${groupId}/members/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast(window.chatTranslations?.failed_to_remove_member || window.groupTranslations?.failed_to_remove_member || 'Failed to remove member', 'error');
        }
    })
    .catch(() => {
        showToast(window.chatTranslations?.error_removing_member || window.groupTranslations?.error_removing_member || 'Error removing member', 'error');
    });
}
