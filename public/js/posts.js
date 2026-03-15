/* Post Partial JavaScript */

if (typeof window.postFunctionsInitialized === 'undefined') {
    window.postFunctionsInitialized = true;

    function deletePost(slug, btn) {
        if (!confirm(window.postTranslations.delete_post_confirm)) return;

        const postCard = btn.closest('.post-card');

        fetch(`/posts/${slug}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                if (postCard) postCard.remove();
                showToast(window.postTranslations.post_deleted, 'success');
                return { success: true };
            }
            return response.json().catch(() => {
                window.location.reload();
            });
        })
        .then(data => {
            if (data && data.success && postCard) {
                postCard.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(window.postTranslations.failed_to_delete_post, 'error');
            window.location.reload();
        });
    }

    function toggleLike(slug, btn) {
        const count = btn.querySelector('.count');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = count ? parseInt(count.textContent) || 0 : 0;

        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (count) count.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (count) count.textContent = currentCount + 1;
        }

        fetch(`/posts/${slug}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var likersBtn = btn.closest('.post-actions').querySelector('.likers-btn');
                if (likersBtn) {
                    var likersCountSpan = likersBtn.querySelector('.likers-count');
                    if (likersCountSpan) {
                        likersCountSpan.textContent = data.likes_count > 0 ? data.likes_count : '';
                    }
                }
            } else {
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (count) count.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (count) count.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (count) count.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (count) count.textContent = currentCount;
            }
        });
    }

    function toggleSave(slug, btn) {
        fetch(`/posts/${slug}/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.saved) {
                btn.classList.add('saved');
                btn.querySelector('span').textContent = window.chatTranslations.saved_post;
                showToast(window.chatTranslations.post_saved_success, 'success');
            } else {
                btn.classList.remove('saved');
                btn.querySelector('span').textContent = window.chatTranslations.save_post;
                showToast(window.chatTranslations.post_removed_from_saved, 'info');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function copyPostLink(slug) {
        const url = window.location.origin + '/posts/' + slug;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast(window.chatTranslations.post_link_copied, 'success');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(ta);
            if (ok) {
                showToast(window.chatTranslations.post_link_copied, 'success');
            } else {
                showToast(window.chatTranslations.failed_to_copy_link, 'error');
            }
        } catch (e) {
            showToast(window.chatTranslations.failed_to_copy_link, 'error');
        }
    }

    function showLikers(slug) {
        fetch(`/posts/${slug}/likers`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.likers && data.likers.length > 0) {
                showLikersModal(data.likers);
            } else {
                showToast(window.chatTranslations.no_likes_yet, 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(window.chatTranslations.could_not_load_likers, 'error');
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showLikersModal(likers) {
        const existingModal = document.getElementById('likers-modal');
        if (existingModal) existingModal.remove();

        const modal = document.createElement('div');
        modal.id = 'likers-modal';
        modal.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            background: var(--surface, #161616);
            border: 1px solid var(--border, #2a2a2a);
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border, #2a2a2a);
        `;
        header.innerHTML = `
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text);">${window.chatTranslations.likes} (${likers.length})</h3>
            <button onclick="document.getElementById('likers-modal').remove()" style="background: none; border: none; color: var(--text-muted, #86868b); font-size: 24px; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        `;

        const list = document.createElement('div');
        list.style.cssText = 'display: flex; flex-direction: column; gap: 8px;';

        likers.forEach(liker => {
            const avatar = liker.avatar || null;
            const displayName = liker.username || liker.name || 'User';
            const initial = displayName ? displayName.charAt(0).toUpperCase() : '?';

            const item = document.createElement('a');
            item.href = `/users/${liker.username}`;
            item.style.cssText = `
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px;
                border-radius: 12px;
                text-decoration: none;
                color: inherit;
                transition: background 0.2s;
            `;
            item.onmouseover = () => item.style.background = 'var(--surface-hover, #1c1c1e)';
            item.onmouseout = () => item.style.background = 'transparent';

            item.innerHTML = `
                ${avatar
                    ? `<img src="${avatar}" alt="${displayName}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">`
                    : `<div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary, #5e60ce), var(--secondary, #4ea8de)); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: white;">${initial}</div>`
                }
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 14px; direction: ltr; text-align: left;">@${escapeHtml(displayName)}</div>
                    ${liker.name ? `<div style="font-size: 12px; color: var(--text-muted, #86868b);">${escapeHtml(liker.name)}</div>` : ''}
                </div>
                ${liker.is_verified ? '<i class="fas fa-check-circle" style="color: #22c55e; font-size: 16px; flex-shrink: 0;"></i>' : ''}
            `;

            list.appendChild(item);
        });

        content.appendChild(header);
        content.appendChild(list);
        modal.appendChild(content);
        document.body.appendChild(modal);

        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
    }

    function toggleComments(postId, show) {
        const hiddenComments = document.getElementById(`hidden-comments-${postId}`);
        const showMoreBtn = document.querySelector(`#post-${postId} .show-more-comments`);

        if (hiddenComments) {
            hiddenComments.style.display = show ? 'block' : 'none';
        }

        if (showMoreBtn) {
            showMoreBtn.style.display = show ? 'none' : 'block';
        }
    }

    function likeComment(commentId, btn) {
        const countSpan = btn.querySelector('span');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = countSpan ? parseInt(countSpan.textContent) || 0 : 0;

        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (countSpan) countSpan.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (countSpan) countSpan.textContent = currentCount + 1;
        }

        fetch(`/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (countSpan) countSpan.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (countSpan) countSpan.textContent = currentCount;
            }
        });
    }

    function deleteComment(commentId, btn) {
        if (!confirm(window.postTranslations.delete_comment_confirm)) return;

        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentItem = btn.closest('.comment-item');
                if (commentItem) commentItem.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function togglePostContent(btn) {
        const postText = btn.previousElementSibling;
        const showMoreText = btn.querySelector('.show-more-text');
        const showLessText = btn.querySelector('.show-less-text');

        if (postText.classList.contains('truncated')) {
            postText.classList.remove('truncated');
            postText.classList.add('expanded');
            if (showMoreText) showMoreText.style.display = 'none';
            if (showLessText) showLessText.style.display = 'inline';
        } else {
            postText.classList.remove('expanded');
            postText.classList.add('truncated');
            if (showMoreText) showMoreText.style.display = 'inline';
            if (showLessText) showLessText.style.display = 'none';
        }
    }

    function openMediaModal(postId, index) {
        const mediaContainer = document.querySelector(`.post-media[data-post-id="${postId}"]`);
        if (!mediaContainer) return;

        const mediaData = JSON.parse(mediaContainer.getAttribute('data-media-list'));
        const mediaCount = parseInt(mediaContainer.getAttribute('data-media-count'));

        window.currentMediaList = mediaData;
        window.currentMediaIndex = parseInt(index);

        const modal = document.getElementById('media-modal');
        const mediaContent = modal.querySelector('.media-modal-content');
        const counter = modal.querySelector('.media-modal-counter');

        updateMediaModal();

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function updateMediaModal() {
        const modal = document.getElementById('media-modal');
        const mediaContent = modal.querySelector('.media-modal-content');
        const counter = modal.querySelector('.media-modal-counter');
        const prevBtn = modal.querySelector('.media-modal-prev');
        const nextBtn = modal.querySelector('.media-modal-next');

        if (!window.currentMediaList || window.currentMediaList.length === 0) return;

        const currentItem = window.currentMediaList[window.currentMediaIndex];
        if (!currentItem) return;

        mediaContent.innerHTML = `
            <button class="media-modal-close" onclick="closeMediaModal()" title="Close">
                <i class="fas fa-times"></i>
            </button>
            ${window.currentMediaIndex > 0 ? `<button class="media-modal-nav media-modal-prev" onclick="navigateMedia(-1)" title="Previous"><i class="fas fa-chevron-left"></i></button>` : ''}
            ${window.currentMediaIndex < window.currentMediaList.length - 1 ? `<button class="media-modal-nav media-modal-next" onclick="navigateMedia(1)" title="Next"><i class="fas fa-chevron-right"></i></button>` : ''}
            <div class="media-modal-counter">${window.currentMediaIndex + 1} / ${window.currentMediaList.length}</div>
        `;

        if (currentItem.type === 'image') {
            const img = document.createElement('img');
            img.src = currentItem.src;
            img.alt = 'Media';
            img.onclick = (e) => e.stopPropagation();
            mediaContent.appendChild(img);
        } else if (currentItem.type === 'video') {
            const video = document.createElement('video');
            video.src = currentItem.src;
            video.controls = true;
            video.autoplay = true;
            video.onclick = (e) => e.stopPropagation();
            mediaContent.appendChild(video);
        }
    }

    function closeMediaModal(event) {
        if (event && event.target !== event.currentTarget) return;

        const modal = document.getElementById('media-modal');
        modal.classList.remove('active');
        document.body.style.overflow = '';

        window.currentMediaList = null;
        window.currentMediaIndex = null;
    }

    function navigateMedia(direction) {
        if (!window.currentMediaList) return;

        const newIndex = window.currentMediaIndex + direction;
        if (newIndex >= 0 && newIndex < window.currentMediaList.length) {
            window.currentMediaIndex = newIndex;
            updateMediaModal();
        }
    }

    function quickFollow(username, btn) {
        const isFollowing = btn.getAttribute('data-following') === 'true';
        const span = btn.querySelector('span');

        fetch(`/users/${username}/follow`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isFollowing) {
                    btn.classList.remove('following');
                    btn.setAttribute('data-following', 'false');
                    span.textContent = window.chatTranslations.follow;
                } else {
                    btn.classList.add('following');
                    btn.setAttribute('data-following', 'true');
                    span.textContent = window.chatTranslations.following;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
