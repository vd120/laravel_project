/* Comments Partial JavaScript */

function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') {
            form.querySelector('textarea').focus();
        }
    }
}

function toggleNestedReplies(commentId, show) {
    const hiddenReplies = document.getElementById('hidden-replies-' + commentId);
    const parentComment = document.querySelector('[data-comment-id="' + commentId + '"]');
    if (!parentComment) return;

    const showMoreBtn = parentComment.querySelector('.show-more-replies');
    const showRepliesAlways = parentComment.querySelector('.show-replies-always');

    if (hiddenReplies) {
        hiddenReplies.style.display = show ? 'block' : 'none';
    }

    // Always hide both buttons when clicked
    if (showMoreBtn) {
        showMoreBtn.style.display = 'none';
    }

    if (showRepliesAlways) {
        showRepliesAlways.style.display = 'none';
    }
}
