/* Posts Functions - External File */

(function() {
    'use strict';

    if (typeof window.postFunctionsInitialized === 'undefined') {
        window.postFunctionsInitialized = true;

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        }

        function getTranslations() {
            const el = document.getElementById('post-translations');
            try {
                return JSON.parse(el?.textContent || '{}');
            } catch {
                return {};
            }
        }

        window.deletePost = function(slug, btn) {
            const t = getTranslations();
            if (!confirm(t.delete_post_confirm || 'Delete this post?')) return;

            const postCard = btn.closest('.post-card');

            fetch(`/posts/${slug}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    if (postCard) postCard.remove();
                    showToast(t.post_deleted || 'Post deleted', 'success');
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
                showToast(t.failed_to_delete_post || 'Failed to delete post', 'error');
                window.location.reload();
            });
        };

        window.toggleLike = function(slug, btn) {
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
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likersBtn = btn.closest('.post-actions')?.querySelector('.likers-btn');
                    if (likersBtn) {
                        const likersCountSpan = likersBtn.querySelector('.likers-count');
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
        };

        window.toggleSave = function(slug, btn) {
            fetch(`/posts/${slug}/save`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.saved) {
                    btn.classList.add('saved');
                    btn.querySelector('span').textContent = window.chatTranslations?.saved_post || 'Saved';
                    showToast(window.chatTranslations?.post_saved_success || 'Post saved', 'success');
                } else {
                    btn.classList.remove('saved');
                    btn.querySelector('span').textContent = window.chatTranslations?.save_post || 'Save';
                    showToast(window.chatTranslations?.post_removed_from_saved || 'Removed from saved', 'info');
                }
            })
            .catch(error => console.error('Error:', error));
        };

        window.copyPostLink = function(slug) {
            const url = window.location.origin + '/posts/' + slug;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast(window.chatTranslations?.post_link_copied || 'Link copied', 'success');
                }).catch(() => {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        };

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
                    showToast(window.chatTranslations?.post_link_copied || 'Link copied', 'success');
                } else {
                    showToast(window.chatTranslations?.failed_to_copy_link || 'Failed to copy', 'error');
                }
            } catch (e) {
                showToast(window.chatTranslations?.failed_to_copy_link || 'Failed to copy', 'error');
            }
        }

        window.showLikers = function(slug) {
            fetch(`/posts/${slug}/likers`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.likers && data.likers.length > 0) {
                    showLikersModal(data.likers);
                } else {
                    showToast(window.chatTranslations?.no_likes_yet || 'No likes yet', 'info');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(window.chatTranslations?.could_not_load_likers || 'Could not load likers', 'error');
            });
        };

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
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);';

            const content = document.createElement('div');
            content.style.cssText = 'background:var(--surface,#161616);border:1px solid var(--border,#2a2a2a);border-radius:16px;width:90%;max-width:400px;max-height:80vh;overflow-y:auto;padding:20px;';

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border,#2a2a2a);';
            header.innerHTML = '<h3 style="margin:0;font-size:18px;font-weight:700;color:var(--text);">' + (window.chatTranslations?.likes || 'Likes') + ' (' + likers.length + ')</h3><button onclick="document.getElementById(\'likers-modal\').remove()" style="background:none;border:none;color:var(--text-muted,#86868b);font-size:24px;cursor:pointer;padding:0;line-height:1;">&times;</button>';

            const list = document.createElement('div');
            list.style.cssText = 'display:flex;flex-direction:column;gap:8px;';

            likers.forEach(liker => {
                const avatar = liker.avatar || null;
                const displayName = liker.username || liker.name || 'User';
                const initial = displayName ? displayName.charAt(0).toUpperCase() : '?';

                const item = document.createElement('a');
                item.href = '/users/' + liker.username;
                item.style.cssText = 'display:flex;align-items:center;gap:12px;padding:10px;border-radius:12px;text-decoration:none;color:inherit;transition:background 0.2s;';
                item.onmouseover = () => item.style.background = 'var(--surface-hover,#1c1c1e)';
                item.onmouseout = () => item.style.background = 'transparent';

                item.innerHTML = (avatar
                    ? '<img src="' + avatar + '" alt="' + displayName + '" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">'
                    : '<div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--primary,#5e60ce),var(--secondary,#4ea8de));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:white;">' + initial + '</div>')
                    + '<div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:14px;direction:ltr;text-align:left;">@' + escapeHtml(displayName) + '</div>'
                    + (liker.name ? '<div style="font-size:12px;color:var(--text-muted,#86868b);">' + escapeHtml(liker.name) + '</div>' : '')
                    + '</div>'
                    + (liker.is_verified ? '<i class="fas fa-check-circle" style="color:#22c55e;font-size:16px;flex-shrink:0;"></i>' : '');

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

        window.toggleComments = function(postId, show) {
            const hiddenComments = document.getElementById('hidden-comments-' + postId);
            const showMoreBtn = document.querySelector('#post-' + postId + ' .show-more-comments');

            if (hiddenComments) {
                hiddenComments.style.display = show ? 'block' : 'none';
            }

            if (showMoreBtn) {
                showMoreBtn.style.display = show ? 'none' : 'block';
            }
        };

        window.likeComment = function(commentId, btn) {
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
                    'X-CSRF-TOKEN': getCsrfToken(),
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
        };

        window.deleteComment = function(commentId, btn) {
            const t = getTranslations();
            if (!confirm(t.delete_comment_confirm || 'Delete this comment?')) return;

            fetch(`/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
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
        };

        window.togglePostContent = function(btn) {
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
        };

        window.openMediaModal = function(postId, index) {
            const mediaContainer = document.querySelector('.post-media[data-post-id="' + postId + '"]');
            if (!mediaContainer) return;

            const mediaData = JSON.parse(mediaContainer.getAttribute('data-media-list'));
            const mediaCount = parseInt(mediaContainer.getAttribute('data-media-count'));

            window.currentMediaList = mediaData;
            window.currentMediaIndex = parseInt(index);

            const modal = document.getElementById('media-modal');
            updateMediaModal();

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        function updateMediaModal() {
            const modal = document.getElementById('media-modal');
            const mediaContent = modal.querySelector('.media-modal-content');

            if (!window.currentMediaList || window.currentMediaList.length === 0) return;

            const currentItem = window.currentMediaList[window.currentMediaIndex];
            if (!currentItem) return;

            mediaContent.innerHTML = '<button class="media-modal-close" onclick="closeMediaModal()" title="Close"><i class="fas fa-times"></i></button>'
                + (window.currentMediaIndex > 0 ? '<button class="media-modal-nav media-modal-prev" onclick="navigateMedia(-1)" title="Previous"><i class="fas fa-chevron-left"></i></button>' : '')
                + (window.currentMediaIndex < window.currentMediaList.length - 1 ? '<button class="media-modal-nav media-modal-next" onclick="navigateMedia(1)" title="Next"><i class="fas fa-chevron-right"></i></button>' : '')
                + '<div class="media-modal-counter">' + (window.currentMediaIndex + 1) + ' / ' + window.currentMediaList.length + '</div>';

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

        window.closeMediaModal = function(event) {
            if (event && event.target !== event.currentTarget) return;
            const modal = document.getElementById('media-modal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            window.currentMediaList = null;
            window.currentMediaIndex = null;
        };

        window.navigateMedia = function(direction) {
            if (!window.currentMediaList) return;
            const newIndex = window.currentMediaIndex + direction;
            if (newIndex >= 0 && newIndex < window.currentMediaList.length) {
                window.currentMediaIndex = newIndex;
                updateMediaModal();
            }
        };

        window.quickFollow = function(username, btn) {
            const isFollowing = btn.getAttribute('data-following') === 'true';
            const span = btn.querySelector('span');

            fetch('/users/' + username + '/follow', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isFollowing) {
                        btn.classList.remove('following');
                        btn.setAttribute('data-following', 'false');
                        span.textContent = window.chatTranslations?.follow || 'Follow';
                    } else {
                        btn.classList.add('following');
                        btn.setAttribute('data-following', 'true');
                        span.textContent = window.chatTranslations?.following || 'Following';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        };

        // Submit comment function
        window.submitComment = function(postSlug, postId) {
            const textarea = document.getElementById('comment-content-' + postSlug);
            const content = textarea?.value.trim();

            if (!content) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Please write a comment', 'error');
                }
                return;
            }

            fetch('/comments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content: content, post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.comment) {
                    if (textarea) textarea.value = '';

                    const commentsList = document.querySelector('#post-' + postId + ' .comments-list');
                    if (commentsList && data.comment) {
                        const user = data.comment.user || { username: 'user', name: 'User', avatar_url: null };
                        const avatarUrl = user.avatar_url || null;
                        const userName = user.username || 'user';

                        const commentHtml = 
                            '<div class="comment-item level-0" data-comment-id="' + data.comment.id + '">' +
                            '<div class="comment-header">' +
                            '<div class="comment-author">' +
                            (avatarUrl 
                                ? '<img src="' + avatarUrl + '" alt="Avatar" class="comment-avatar">'
                                : '<div class="comment-avatar-placeholder">?</div>'
                            ) +
                            '<div class="comment-author-info">' +
                            '<a href="/users/' + userName + '" class="comment-name">' + userName + '</a>' +
                            '<span class="comment-time">Just now</span>' +
                            '</div></div>' +
                            '<button type="button" class="delete-comment-btn" onclick="deleteComment(' + data.comment.id + ', this)" title="Delete">' +
                            '<i class="fas fa-trash-alt"></i></button></div>' +
                            '<div class="comment-content"><p>' + data.comment.content + '</p></div>' +
                            '<div class="comment-actions-bar">' +
                            '<button type="button" class="comment-action-btn" onclick="likeComment(' + data.comment.id + ', this)">' +
                            '<i class="fas fa-heart"></i><span>0</span></button>' +
                            '<button type="button" class="comment-action-btn" onclick="toggleReplyForm(' + data.comment.id + ')">' +
                            '<i class="fas fa-reply"></i><span>Reply</span></button>' +
                            '</div>' +
                            '<div class="reply-form" id="reply-form-' + data.comment.id + '" style="display: none;">' +
                            '<div class="reply-input-wrapper">' +
                            '<textarea id="reply-content-' + data.comment.id + '" placeholder="Write a reply" maxlength="5000"></textarea>' +
                            '<button type="button" onclick="submitReply(' + data.comment.id + ', ' + postId + ')">' +
                            '<i class="fas fa-paper-plane"></i></button></div>' +
                            '<button type="button" class="cancel-reply" onclick="toggleReplyForm(' + data.comment.id + ')">Cancel</button>' +
                            '</div></div>';

                        commentsList.insertAdjacentHTML('afterbegin', commentHtml);

                        const commentCount = document.querySelector('#post-' + postId + ' .post-comments-section h4');
                        if (commentCount) {
                            const currentCount = parseInt(commentCount.textContent.match(/\d+/)) || 0;
                            commentCount.textContent = 'Comments (' + (currentCount + 1) + ')';
                        }

                        if (typeof window.showToast === 'function') {
                            window.showToast('Comment posted', 'success');
                        }
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Failed to post comment', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Error posting comment', 'error');
                }
            });
        };

        // Submit reply function
        window.submitReply = function(commentId, postId) {
            const textarea = document.getElementById('reply-content-' + commentId);
            const content = textarea?.value.trim();

            if (!content) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Please write a reply', 'error');
                }
                return;
            }

            fetch('/comments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content: content, post_id: postId, parent_id: commentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.comment) {
                    if (textarea) textarea.value = '';
                    const replyForm = document.getElementById('reply-form-' + commentId);
                    if (replyForm) replyForm.style.display = 'none';

                    const parentComment = document.querySelector('[data-comment-id="' + commentId + '"]');
                    if (parentComment) {
                        let repliesContainer = parentComment.querySelector('.replies-container');
                        let showRepliesAlways = parentComment.querySelector('.show-replies-always');

                        if (!repliesContainer) {
                            repliesContainer = document.createElement('div');
                            repliesContainer.className = 'replies-container';
                            parentComment.appendChild(repliesContainer);
                        }

                        // Hide the "Show replies" button since we're adding a reply dynamically
                        // and the replies are now visible
                        if (showRepliesAlways) {
                            showRepliesAlways.style.display = 'none';
                        }

                        const user = data.comment.user || { username: 'user', avatar_url: null };
                        const userName = user.username || 'user';
                        const avatarUrl = user.avatar_url || null;
                        const initial = userName.charAt(0).toUpperCase();

                        // Determine the level for this reply (parent level + 1, max 5)
                        const parentLevel = parseInt(parentComment.classList.contains('level-0') ? 0 : 
                            parentComment.classList.contains('level-1') ? 1 :
                            parentComment.classList.contains('level-2') ? 2 :
                            parentComment.classList.contains('level-3') ? 3 :
                            parentComment.classList.contains('level-4') ? 4 : 5);
                        const newLevel = Math.min(parentLevel + 1, 5);

                        const replyHtml =
                            '<div class="comment-item nested level-' + newLevel + '" data-comment-id="' + data.comment.id + '">' +
                            '<div class="comment-header">' +
                            '<div class="comment-author">' +
                            (avatarUrl
                                ? '<img src="' + avatarUrl + '" alt="Avatar" class="comment-avatar">'
                                : '<div class="comment-avatar-placeholder">' + initial + '</div>'
                            ) +
                            '<div class="comment-author-info">' +
                            '<a href="/users/' + userName + '" class="comment-name">' + userName + '</a>' +
                            '<span class="comment-time">Just now</span>' +
                            '</div></div>' +
                            '<button type="button" class="delete-comment-btn" onclick="deleteComment(' + data.comment.id + ', this)" title="Delete">' +
                            '<i class="fas fa-trash-alt"></i></button></div>' +
                            '<div class="comment-content"><p>' + data.comment.content + '</p></div>' +
                            '<div class="comment-actions-bar">' +
                            '<button type="button" class="comment-action-btn" onclick="likeComment(' + data.comment.id + ', this)">' +
                            '<i class="fas fa-heart"></i><span>0</span></button>' +
                            (newLevel < 4 ? '<button type="button" class="comment-action-btn" onclick="toggleReplyForm(' + data.comment.id + ')">' +
                            '<i class="fas fa-reply"></i><span>Reply</span></button>' : '') +
                            '</div></div>';

                        repliesContainer.insertAdjacentHTML('afterbegin', replyHtml);

                        if (typeof window.showToast === 'function') {
                            window.showToast('Reply posted', 'success');
                        }
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Failed to post reply', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Error posting reply', 'error');
                }
            });
        };

        // Report Modal Functions
        let currentPostSlug = null;

        window.togglePostMenu = function(postId) {
            // Close all other menus first
            document.querySelectorAll('.post-menu-dropdown').forEach(menu => {
                if (menu.id !== 'post-menu-' + postId) {
                    menu.style.display = 'none';
                }
            });
            
            const menu = document.getElementById('post-menu-' + postId);
            if (menu) {
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
            }
        };

        // Close menus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.post-header-actions')) {
                document.querySelectorAll('.post-menu-dropdown').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });

        window.openReportModal = function(slug, postId) {
            // Close the menu first
            document.querySelectorAll('.post-menu-dropdown').forEach(menu => {
                menu.style.display = 'none';
            });
            
            currentPostSlug = slug;
            const modal = document.getElementById('report-modal');
            const form = document.getElementById('report-form');

            if (modal && form) {
                form.action = '/posts/' + slug + '/report';
                form.reset();
                document.getElementById('other-reason-group').style.display = 'none';
                document.getElementById('submit-report-btn').disabled = true;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeReportModal = function() {
            const modal = document.getElementById('report-modal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                currentPostSlug = null;
            }
        };

        window.toggleOtherReason = function() {
            const reasonSelect = document.getElementById('report-reason');
            const otherGroup = document.getElementById('other-reason-group');
            const submitBtn = document.getElementById('submit-report-btn');
            
            if (reasonSelect && otherGroup) {
                if (reasonSelect.value === 'other') {
                    otherGroup.style.display = 'block';
                } else {
                    otherGroup.style.display = 'none';
                }
            }
            
            if (submitBtn && reasonSelect) {
                submitBtn.disabled = !reasonSelect.value;
            }
        };

        // Character count for report content
        document.addEventListener('DOMContentLoaded', function() {
            const contentTextarea = document.getElementById('report-content');
            const charCount = document.getElementById('char-count');
            
            if (contentTextarea && charCount) {
                contentTextarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Close modal on outside click
            const modal = document.getElementById('report-modal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeReportModal();
                    }
                });
            }

            // Handle form submission
            const reportForm = document.getElementById('report-form');
            if (reportForm) {
                reportForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = document.getElementById('submit-report-btn');

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                    }

                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { 
                                throw err; 
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            closeReportModal();
                            if (typeof window.showToast === 'function') {
                                window.showToast('Report submitted successfully', 'success');
                            }
                        } else {
                            const message = data.message || data.error || 'Failed to submit report';
                            if (typeof window.showToast === 'function') {
                                window.showToast(message, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        let errorMessage = 'Failed to submit report';
                        
                        // Handle validation errors
                        if (error.errors) {
                            const firstError = Object.values(error.errors)[0];
                            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                        } else if (error.message) {
                            errorMessage = error.message;
                        } else if (error.error) {
                            errorMessage = error.error;
                        }
                        
                        if (typeof window.showToast === 'function') {
                            window.showToast(errorMessage, 'error');
                        }
                    })
                    .finally(() => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
                        }
                    });
                });
            }
        });
    }
})();
