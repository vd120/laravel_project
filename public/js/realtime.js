/**
 * Real-time Social Media Updates with Laravel Echo
 * Handles all broadcasting events for likes, comments, follows, and story reactions
 */

class RealTimeManager {
    constructor() {
        this.echo = window.Echo;
        this.currentUserId = window.currentUserId;
        this.initialized = false;
    }

    init() {
        if (!this.echo || this.initialized) return;

        console.log('🔄 Initializing real-time manager...');
        console.log('📡 Echo instance:', this.echo);
        console.log('👤 Current user ID:', this.currentUserId);

        try {
            this.initializePostListeners();
            this.initializeStoryListeners();
            this.initializeUserListeners();
            this.initialized = true;

            console.log('✅ Real-time manager initialized successfully');

            // Test connection
            this.echo.connector.pusher.connection.bind('connected', () => {
                console.log('🔗 WebSocket connected successfully');
            });

            this.echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('❌ WebSocket disconnected');
            });

            this.echo.connector.pusher.connection.bind('error', (error) => {
                console.error('🚨 WebSocket connection error:', error);
            });

        } catch (error) {
            console.error('❌ Failed to initialize real-time manager:', error);
        }
    }

    // ==================== POST LISTENERS ====================
    initializePostListeners() {
        if (!this.echo) return;

        // Listen for post likes/unlikes
        this.echo.private('post.*')
            .listen('.post.liked', (e) => this.handlePostLiked(e))
            .listen('.post.unliked', (e) => this.handlePostUnliked(e))
            .listen('.comment.created', (e) => this.handleCommentCreated(e))
            .listen('.comment.deleted', (e) => this.handleCommentDeleted(e))
            .listen('.comment.liked', (e) => this.handleCommentLiked(e))
            .listen('.comment.unliked', (e) => this.handleCommentUnliked(e));
    }

    handlePostLiked(event) {
        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) return;

        const likeButton = postElement.querySelector('.action-btn.like-btn');
        const countSpan = likeButton ? likeButton.querySelector('.count') : null;

        // Blade uses fas fa-heart always, just toggle 'liked' class
        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.add('liked');
        }

        if (countSpan) {
            countSpan.textContent = event.likes_count;
        }

        // Update likers button count
        const likersBtn = postElement.querySelector('.likers-btn .likers-count');
        if (likersBtn) {
            likersBtn.textContent = event.likes_count;
        }

        // Show notification if not own post
        if (event.user_id !== this.currentUserId) {
            this.showNotification(`${event.user_name} liked a post`);
        }
    }

    handlePostUnliked(event) {
        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) return;

        const likeButton = postElement.querySelector('.action-btn.like-btn');
        const countSpan = likeButton ? likeButton.querySelector('.count') : null;

        // Blade uses fas fa-heart always, just toggle 'liked' class
        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.remove('liked');
        }

        if (countSpan) {
            countSpan.textContent = event.likes_count;
        }

        // Update likers button count
        const likersBtn = postElement.querySelector('.likers-btn .likers-count');
        if (likersBtn) {
            likersBtn.textContent = event.likes_count;
        }
    }

    handleCommentCreated(event) {
        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) return;

        // Match blade template: .comments-list inside .post-comments-section
        const commentsList = postElement.querySelector('.comments-list');
        if (!commentsList) return;

        // Add the new comment at the beginning
        const commentHtml = this.createCommentHtml(event.comment);
        commentsList.insertAdjacentHTML('afterbegin', commentHtml);

        // Update comment counter
        this.updateCommentCount(event.post_id, 1);

        // Show notification if not own comment
        if (event.comment.user_id !== this.currentUserId) {
            this.showNotification(`${event.comment.user.name} commented on a post`);
        }
    }

    handleCommentDeleted(event) {
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (commentElement) {
            // Get the post element before removing the comment
            const postElement = commentElement.closest('.post-card');
            
            commentElement.remove();
            
            // Update comment counter
            if (postElement && event.post_id) {
                this.updateCommentCount(event.post_id, -1);
            }
        }
    }

    updateCommentCount(postId, delta) {
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (!postElement) return;

        // Find the h4 element in the comments section: "Comments (X)"
        const commentHeader = postElement.querySelector('.post-comments-section h4');
        if (commentHeader) {
            const currentText = commentHeader.textContent;
            const match = currentText.match(/\((\d+)\)/);
            if (match) {
                const currentCount = parseInt(match[1]) || 0;
                const newCount = Math.max(0, currentCount + delta);
                commentHeader.textContent = `Comments (${newCount})`;
            }
        }
    }

    handleCommentLiked(event) {
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (!commentElement) return;

        // Match blade template: .comment-action-btn with heart icon (like button), not reply button
        const likeButton = commentElement.querySelector('.comment-action-btn[onclick*="likeComment"]');
        const countSpan = likeButton ? likeButton.querySelector('span') : null;
        
        if (countSpan) {
            countSpan.textContent = event.likes_count;
        }

        // Blade uses fas fa-heart always, just toggle 'liked' class
        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.add('liked');
        }
    }

    handleCommentUnliked(event) {
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (!commentElement) return;

        // Match blade template: .comment-action-btn with heart icon (like button), not reply button
        const likeButton = commentElement.querySelector('.comment-action-btn[onclick*="likeComment"]');
        const countSpan = likeButton ? likeButton.querySelector('span') : null;
        
        if (countSpan) {
            countSpan.textContent = event.likes_count;
        }

        // Blade uses fas fa-heart always, just toggle 'liked' class
        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.remove('liked');
        }
    }

    // ==================== STORY LISTENERS ====================
    initializeStoryListeners() {
        if (!this.echo) return;

        // Listen for story reactions
        this.echo.private('story.*')
            .listen('.story.reacted', (e) => this.handleStoryReacted(e))
            .listen('.story.unreacted', (e) => this.handleStoryUnreacted(e));
    }

    handleStoryReacted(event) {
        const storyElement = document.querySelector(`[data-story-id="${event.story_id}"]`);
        if (!storyElement) return;

        const reactionsCount = storyElement.querySelector('.reactions-count');
        if (reactionsCount) {
            reactionsCount.textContent = event.reactions_count;
        }

        // Update user's reaction if viewing
        if (event.user_id === this.currentUserId) {
            const reactionButtons = storyElement.querySelectorAll('.reaction-btn');
            reactionButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.reaction === event.reaction_type) {
                    btn.classList.add('active');
                }
            });
        }

        // Show notification if not own story
        if (event.user_id !== this.currentUserId) {
            this.showNotification(`${event.user_name} reacted to your story`);
        }
    }

    handleStoryUnreacted(event) {
        const storyElement = document.querySelector(`[data-story-id="${event.story_id}"]`);
        if (!storyElement) return;

        const reactionsCount = storyElement.querySelector('.reactions-count');
        if (reactionsCount) {
            reactionsCount.textContent = event.reactions_count;
        }

        // Remove user's reaction if viewing
        if (event.user_id === this.currentUserId) {
            const reactionButtons = storyElement.querySelectorAll('.reaction-btn');
            reactionButtons.forEach(btn => btn.classList.remove('active'));
        }
    }

    // ==================== USER LISTENERS ====================
    initializeUserListeners() {
        if (!this.echo) return;

        // Listen for user follow/unfollow events
        this.echo.private(`user.${this.currentUserId}`)
            .listen('.user.followed', (e) => this.handleUserFollowed(e))
            .listen('.user.unfollowed', (e) => this.handleUserUnfollowed(e));
    }

    // ==================== CHAT LISTENERS ====================
    initializeChatListeners() {
        if (!this.echo) return;

        // Listen for message sent events in conversations
        // This will be dynamically initialized when opening chat conversations
    }

    listenToConversation(conversationId) {
        if (!this.echo) return;

        console.log('🎧 Listening to conversation:', conversationId);

        return this.echo.private(`conversation.${conversationId}`)
            .listen('.message.sent', (e) => this.handleMessageSent(e));
    }

    handleMessageSent(event) {
        console.log('📨 Received message:', event);

        // Only add message if it's not from current user (to avoid duplicates)
        if (event.user.id !== this.currentUserId) {
            this.addMessageToChat(event);
        }
    }

    addMessageToChat(messageData) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${messageData.user.id === this.currentUserId ? 'own' : 'other'}`;
        messageDiv.setAttribute('data-message-id', messageData.id);

        messageDiv.innerHTML = `
            <div class="message-content">${this.escapeHtml(messageData.content)}</div>
            <div class="message-time">${new Date(messageData.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
        `;

        chatMessages.appendChild(messageDiv);
        this.scrollChatToBottom();
    }

    scrollChatToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    handleUserFollowed(event) {
        // Update follower counts of the followed user
        this.updateFollowerCounts(event.followed_id, event.followers_count);

        // Update following counts of the follower
        this.updateFollowingCounts(event.follower_id, event.following_count);

        // Update follow button if on profile page
        if (event.follower_id === this.currentUserId) {
            const followButton = document.querySelector('.follow-btn');
            if (followButton) {
                followButton.textContent = 'Following';
                followButton.classList.add('following');
            }
        }

        // Show notification
        this.showNotification(`${event.follower_name} started following you`);
    }

    handleUserUnfollowed(event) {
        // Update follower counts of the followed user
        this.updateFollowerCounts(event.followed_id, event.followers_count);

        // Update following counts of the follower
        this.updateFollowingCounts(event.follower_id, event.following_count);

        // Update follow button if on profile page
        if (event.follower_id === this.currentUserId) {
            const followButton = document.querySelector('.follow-btn');
            if (followButton) {
                followButton.textContent = 'Follow';
                followButton.classList.remove('following');
            }
        }
    }

    updateFollowerCounts(userId, count) {
        const followerElements = document.querySelectorAll(`[data-user-followers="${userId}"]`);
        followerElements.forEach(el => {
            el.textContent = count;
        });
    }

    updateFollowingCounts(userId, count) {
        const followingElements = document.querySelectorAll(`[data-user-following="${userId}"]`);
        followingElements.forEach(el => {
            el.textContent = count;
        });
    }

    // ==================== UTILITY METHODS ====================
    createCommentHtml(comment, parentLevel = null) {
        const currentUserId = this.currentUserId;
        const isOwner = comment.user_id === currentUserId;
        
        // Get user data
        const userName = comment.user && comment.user.name ? comment.user.name : 'User';
        const userProfile = comment.user && comment.user.profile ? comment.user.profile : null;
        const avatarUrl = userProfile && userProfile.avatar ? '/storage/' + userProfile.avatar : null;
        const initial = userName.charAt(0).toUpperCase();

        let deleteButtonHtml = '';
        if (isOwner) {
            deleteButtonHtml = `
                <button type="button" class="delete-comment-btn" onclick="deleteComment(${comment.id}, this)" title="Delete comment">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
        }

        // Calculate level for nested replies
        // parentLevel = null means top-level comment (level-0)
        // parentLevel = 0 means reply to level-0 comment (level-1)
        const isTopLevel = parentLevel === null;
        const newLevel = isTopLevel ? 0 : parentLevel + 1;
        const isNested = !isTopLevel;
        const maxLevel = 4;
        const showReplyBtn = newLevel < maxLevel;

        // Blade template uses fas fa-heart always for like button, just toggle 'liked' class
        return `
            <div class="comment-item ${isNested ? 'nested' : ''} level-${newLevel}" data-comment-id="${comment.id}">
                <div class="comment-header">
                    <div class="comment-author">
                        ${avatarUrl 
                            ? `<img src="${avatarUrl}" alt="Avatar" class="comment-avatar">`
                            : `<div class="comment-avatar-placeholder">${initial}</div>`
                        }
                        <div class="comment-author-info">
                            <a href="/users/${userName}" class="comment-name">${userName}</a>
                            <span class="comment-time">Just now</span>
                        </div>
                    </div>
                    ${deleteButtonHtml}
                </div>

                <div class="comment-content">
                    <p>${this.escapeHtml(comment.content)}</p>
                </div>

                <div class="comment-actions-bar">
                    <button type="button" class="comment-action-btn" onclick="likeComment(${comment.id}, this)">
                        <i class="fas fa-heart"></i>
                        <span>0</span>
                    </button>
                    ${showReplyBtn ? `
                    <button type="button" class="comment-action-btn" onclick="toggleReplyForm(${comment.id})">
                        <i class="fas fa-reply"></i>
                        <span>Reply</span>
                    </button>
                    ` : ''}
                </div>

                ${showReplyBtn ? `
                <div class="reply-form" id="reply-form-${comment.id}" style="display: none;">
                    <div class="reply-input-wrapper">
                        <textarea id="reply-content-${comment.id}" placeholder="Write a reply..." maxlength="280"></textarea>
                        <button type="button" onclick="submitReply(${comment.id}, ${comment.post_id})">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <button type="button" class="cancel-reply" onclick="toggleReplyForm(${comment.id})">Cancel</button>
                </div>
                ` : ''}
            </div>
        `;
    }

    showNotification(message, isError = false) {
        // Skip showing notifications to avoid conflicts
        console.log('RealTime notification:', message, isError ? 'error' : 'info');
        // Notifications are handled by the main application
    }

    // Cleanup method
    destroy() {
        if (this.echo) {
            this.echo.disconnect();
        }
        this.initialized = false;
    }
}

// ==================== AJAX FUNCTIONS FOR POST INTERACTIONS ====================

// Toggle like on a post
function toggleLike(postSlug, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Match blade template: .action-btn.like-btn with .count span
    // Blade uses fas fa-heart always, just toggle 'liked' class
    const countSpan = buttonElement.querySelector('.count');
    const isCurrentlyLiked = buttonElement.classList.contains('liked');

    // Get current count
    const currentCount = countSpan ? parseInt(countSpan.textContent) || 0 : 0;

    // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
    if (isCurrentlyLiked) {
        buttonElement.classList.remove('liked');
        if (countSpan) {
            countSpan.textContent = currentCount - 1;
        }
    } else {
        buttonElement.classList.add('liked');
        if (countSpan) {
            countSpan.textContent = currentCount + 1;
        }
    }

    fetch(`/posts/${postSlug}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update likers button count
            const postActions = buttonElement.closest('.post-actions');
            if (postActions) {
                const likersCount = postActions.querySelector('.likers-btn .likers-count');
                if (likersCount) {
                    likersCount.textContent = data.likes_count > 0 ? data.likes_count : '';
                }
            }
        } else {
            // Revert on failure
            if (isCurrentlyLiked) {
                buttonElement.classList.add('liked');
                if (countSpan) {
                    countSpan.textContent = currentCount;
                }
            } else {
                buttonElement.classList.remove('liked');
                if (countSpan) {
                    countSpan.textContent = currentCount;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        // Revert on error
        if (isCurrentlyLiked) {
            buttonElement.classList.add('liked');
            if (countSpan) {
                countSpan.textContent = currentCount;
            }
        } else {
            buttonElement.classList.remove('liked');
            if (countSpan) {
                countSpan.textContent = currentCount;
            }
        }
    });
}

// Toggle save on a post
function toggleSave(postSlug, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Match blade template: .action-btn.save-btn with span for text
    const spanElement = buttonElement.querySelector('span');
    const isCurrentlySaved = buttonElement.classList.contains('saved');

    // Optimistic update - toggle class and text
    if (isCurrentlySaved) {
        buttonElement.classList.remove('saved');
        if (spanElement) {
            spanElement.textContent = 'Save';
        }
    } else {
        buttonElement.classList.add('saved');
        if (spanElement) {
            spanElement.textContent = 'Saved';
        }
    }

    fetch(`/posts/${postSlug}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert on failure
            if (isCurrentlySaved) {
                buttonElement.classList.add('saved');
                if (spanElement) {
                    spanElement.textContent = 'Saved';
                }
            } else {
                buttonElement.classList.remove('saved');
                if (spanElement) {
                    spanElement.textContent = 'Save';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error toggling save:', error);
        // Revert on error
        if (isCurrentlySaved) {
            buttonElement.classList.add('saved');
            if (spanElement) {
                spanElement.textContent = 'Saved';
            }
        } else {
            buttonElement.classList.remove('saved');
            if (spanElement) {
                spanElement.textContent = 'Save';
            }
        }
    });
}

// Like a comment
function likeComment(commentId, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Match blade template: .comment-action-btn with span for count
    // Blade uses fas fa-heart always, just toggle 'liked' class
    const countSpan = buttonElement.querySelector('span');
    const isCurrentlyLiked = buttonElement.classList.contains('liked');

    // Get current count
    const currentCount = countSpan ? parseInt(countSpan.textContent) || 0 : 0;

    // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
    if (isCurrentlyLiked) {
        buttonElement.classList.remove('liked');
        if (countSpan) {
            countSpan.textContent = currentCount - 1;
        }
    } else {
        buttonElement.classList.add('liked');
        if (countSpan) {
            countSpan.textContent = currentCount + 1;
        }
    }

    fetch(`/comments/${commentId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert on failure
            if (isCurrentlyLiked) {
                buttonElement.classList.add('liked');
                if (countSpan) {
                    countSpan.textContent = currentCount;
                }
            } else {
                buttonElement.classList.remove('liked');
                if (countSpan) {
                    countSpan.textContent = currentCount;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error toggling comment like:', error);
        // Revert on error
        if (isCurrentlyLiked) {
            buttonElement.classList.add('liked');
            if (countSpan) {
                countSpan.textContent = currentCount;
            }
        } else {
            buttonElement.classList.remove('liked');
            if (countSpan) {
                countSpan.textContent = currentCount;
            }
        }
    });
}

// Submit a new comment
function submitComment(postSlug, postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const textarea = document.getElementById(`comment-content-${postSlug}`);
    const content = textarea.value.trim();

    if (!content) return;

    // Disable the textarea and button during submission
    textarea.disabled = true;
    const submitButton = textarea.nextElementSibling;
    submitButton.disabled = true;

    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.comment) {
            // Clear the textarea
            textarea.value = '';

            // Add comment to UI - match blade template: .comments-list inside .post-card
            const postElement = document.querySelector(`[data-post-id="${postId}"]`);
            if (postElement) {
                const commentsList = postElement.querySelector('.comments-list');
                if (commentsList) {
                    const commentHtml = window.realTimeManager.createCommentHtml(data.comment);
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                }
                
                // Update comment counter
                if (window.realTimeManager) {
                    window.realTimeManager.updateCommentCount(postId, 1);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
    })
    .finally(() => {
        // Re-enable the textarea and button
        textarea.disabled = false;
        submitButton.disabled = false;
    });
}

// Submit a reply to a comment
function submitReply(commentId, postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const textarea = document.getElementById(`reply-content-${commentId}`);
    const content = textarea.value.trim();

    if (!content) {
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Please enter a reply', true);
        }
        return;
    }

    // Disable the textarea and button during submission
    textarea.disabled = true;
    const submitButton = textarea.nextElementSibling;
    const originalButtonText = submitButton.textContent;
    submitButton.textContent = 'Posting...';
    submitButton.disabled = true;

    // Get parent comment's level for proper nesting
    const parentCommentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
    let parentLevel = null; // null = not found, will default to 0 (top-level reply)
    if (parentCommentElement) {
        // Check for level classes (level-0, level-1, level-2, etc.)
        for (let i = 0; i <= 5; i++) {
            if (parentCommentElement.classList.contains(`level-${i}`)) {
                parentLevel = i;
                break;
            }
        }
    }

    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            post_id: postId,
            parent_id: commentId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear the textarea and hide the reply form
            textarea.value = '';
            toggleReplyForm(commentId);

            // Add reply to UI immediately as fallback
            if (data.comment && parentCommentElement) {
                // Create or find the replies container
                let repliesContainer = parentCommentElement.querySelector('.replies-container');
                if (!repliesContainer) {
                    repliesContainer = document.createElement('div');
                    repliesContainer.className = 'replies-container';
                    parentCommentElement.appendChild(repliesContainer);
                }

                const replyHtml = window.realTimeManager.createCommentHtml(data.comment, parentLevel);
                repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
            }
        } else {
            if (window.realTimeManager) {
                window.realTimeManager.showNotification('Failed to post reply', true);
            }
        }
    })
    .catch(error => {
        console.error('Error posting reply:', error);
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to post reply', true);
        }
    })
    .finally(() => {
        // Re-enable the textarea and button
        textarea.disabled = false;
        submitButton.textContent = originalButtonText;
        submitButton.disabled = false;
    });
}



// ==================== VIDEO PLAYBACK FUNCTIONS ====================

function playVideo(overlayElement) {
    const videoContainer = overlayElement.closest('.video-container');
    const video = videoContainer.querySelector('video');
    const overlay = videoContainer.querySelector('.video-overlay');

    if (video && overlay) {
        video.play();
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';

        // Hide overlay when video ends
        video.addEventListener('ended', function() {
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
        }, { once: true });
    }
}

// Toggle follow/unfollow for a user - matches blade template signature
function toggleFollow(buttonElement, userName) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const isCurrentlyFollowing = buttonElement.getAttribute('data-following') === 'true';

    // Optimistic update - toggle button state
    buttonElement.disabled = true;

    fetch(`/users/${encodeURIComponent(userName)}/follow`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.following) {
            buttonElement.innerHTML = '<i class="fas fa-user-check"></i> <span>Following</span>';
            buttonElement.setAttribute('data-following', 'true');
        } else {
            buttonElement.innerHTML = '<i class="fas fa-user-plus"></i> <span>Follow</span>';
            buttonElement.setAttribute('data-following', 'false');
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
    })
    .finally(() => {
        buttonElement.disabled = false;
    });
}

// Block a user - matches blade template
function blockUser(userName) {
    if (!confirm(`Block ${userName}?`)) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error blocking user');
        }
    })
    .catch(() => alert('Error blocking user'));
}

// Unblock a user - matches blade template
function unblockUser(userName) {
    if (!confirm(`Unblock ${userName}?`)) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/users/${encodeURIComponent(userName)}/block`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error unblocking user');
        }
    })
    .catch(() => alert('Error unblocking user'));
}

// Quick follow for posts - used in post partial
function quickFollow(userName, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const isCurrentlyFollowing = buttonElement.classList.contains('following');

    // Optimistic update
    if (isCurrentlyFollowing) {
        buttonElement.classList.remove('following');
        buttonElement.innerHTML = '<i class="fas fa-user-plus"></i> <span>Follow</span>';
    } else {
        buttonElement.classList.add('following');
        buttonElement.innerHTML = '<i class="fas fa-user-minus"></i> <span>Following</span>';
    }

    fetch(`/users/${encodeURIComponent(userName)}/follow`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success && !data.following) {
            // Revert on failure
            if (isCurrentlyFollowing) {
                buttonElement.classList.add('following');
                buttonElement.innerHTML = '<i class="fas fa-user-minus"></i> <span>Following</span>';
            } else {
                buttonElement.classList.remove('following');
                buttonElement.innerHTML = '<i class="fas fa-user-plus"></i> <span>Follow</span>';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
        // Revert on error
        if (isCurrentlyFollowing) {
            buttonElement.classList.add('following');
            buttonElement.innerHTML = '<i class="fas fa-user-minus"></i> <span>Following</span>';
        } else {
            buttonElement.classList.remove('following');
            buttonElement.innerHTML = '<i class="fas fa-user-plus"></i> <span>Follow</span>';
        }
    });
}

function deletePost(postSlug, button) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    // Match blade template: .post-card with data-post-id
    const postElement = button.closest('.post-card');
    if (!postElement) {
        alert('Could not find the post to delete. Please refresh the page.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Add loading state
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`/posts/${postSlug}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            postElement.remove();
        } else {
            alert(data.message || 'Failed to delete post.');
        }
    })
    .catch(error => {
        alert('An error occurred while deleting the post.');
    })
    .finally(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

// Delete a comment
function deleteComment(commentId, buttonElement) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    if (!commentId) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Get post element before deletion for counter update
    const commentElement = buttonElement.closest('.comment-item');
    const postElement = buttonElement.closest('.post-card');
    let postId = null;
    if (postElement) {
        postId = postElement.getAttribute('data-post-id');
    }

    // Disable button during deletion
    const originalHTML = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    buttonElement.disabled = true;

    fetch(`/comments/${commentId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-HTTP-Method-Override': 'DELETE',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the comment from the UI - match blade: .comment-item
            if (commentElement) {
                commentElement.remove();
            }
            
            // Update comment counter
            if (postId && window.realTimeManager) {
                window.realTimeManager.updateCommentCount(postId, -1);
            }
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
    })
    .finally(() => {
        buttonElement.innerHTML = originalHTML;
        buttonElement.disabled = false;
    });
}

// ==================== COMMENT REPLY FUNCTIONS ====================

// Toggle reply form visibility
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') {
            form.querySelector('textarea').focus();
        }
    }
}

// ==================== INITIALIZATION ====================

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.realTimeManager = new RealTimeManager();
    window.realTimeManager.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.realTimeManager) {
        window.realTimeManager.destroy();
    }
});