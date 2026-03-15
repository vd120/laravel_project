/* Groups Show Page JavaScript */

function copyInviteLink() {
    const input = document.getElementById('inviteLink');
    input.select();
    document.execCommand('copy');

    // Show feedback
    const btn = document.querySelector('.copy-btn');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        btn.innerHTML = originalHtml;
    }, 2000);
}

function showAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'flex';
}

function hideAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'none';
}

document.getElementById('addMemberModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddMemberModal();
    }
});

function searchFriends(query) {
    const options = document.querySelectorAll('#friendsList .friend-option');
    const q = query.toLowerCase();

    options.forEach(option => {
        const name = option.querySelector('.friend-info span').textContent.toLowerCase();
        option.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

// Quick Invite Modal Functions
function showQuickInviteModal() {
    document.getElementById('quickInviteModal').style.display = 'flex';
}

function hideQuickInviteModal() {
    document.getElementById('quickInviteModal').style.display = 'none';
}

document.getElementById('quickInviteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideQuickInviteModal();
    }
});

function searchInviteFriends(query) {
    const options = document.querySelectorAll('#inviteFriendsList .friend-option');
    const q = query.toLowerCase();

    options.forEach(option => {
        const name = option.querySelector('.friend-info span').textContent.toLowerCase();
        option.style.display = name.includes(q) ? 'flex' : 'none';
    });
}
