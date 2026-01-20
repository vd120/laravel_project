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

        const likeButton = postElement.querySelector('.like-btn');
        const likeCount = postElement.querySelector('.like-count');

        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.add('liked');
            likeButton.innerHTML = '<i class="fas fa-heart"></i> Liked';
        }

        if (likeCount) {
            likeCount.textContent = event.likes_count;
        }

        // Show notification if not own post
        if (event.user_id !== this.currentUserId) {
            this.showNotification(`${event.user_name} liked a post`);
        }
    }

    handlePostUnliked(event) {
        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) return;

        const likeButton = postElement.querySelector('.like-btn');
        const likeCount = postElement.querySelector('.like-count');

        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.remove('liked');
            likeButton.innerHTML = '<i class="far fa-heart"></i> Like';
        }

        if (likeCount) {
            likeCount.textContent = event.likes_count;
        }
    }

    handleCommentCreated(event) {
        console.log('📨 Received comment.created event:', event);

        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) {
            console.log('❌ Post element not found for post ID:', event.post_id);
            return;
        }

        // Always add the new comment if it's the current user's comment (optimistic update)
        if (event.comment.user_id === this.currentUserId) {
            console.log('➕ Adding own comment to UI immediately');
            const commentsContainer = postElement.querySelector('.comments-container');
            if (commentsContainer) {
                const commentHtml = this.createCommentHtml(event.comment);
                commentsContainer.insertAdjacentHTML('beforeend', commentHtml);
                console.log('✅ Comment added to UI');
            } else {
                console.log('❌ Comments container not found');
            }
        } else {
            // For other users' comments, add if comments container is visible
            const commentsContainer = postElement.querySelector('.comments-container');
            if (commentsContainer && commentsContainer.style.display !== 'none') {
                console.log('➕ Adding other user comment to UI');
                const commentHtml = this.createCommentHtml(event.comment);
                commentsContainer.insertAdjacentHTML('beforeend', commentHtml);
            }
        }

        // Show notification if not own comment
        if (event.comment.user_id !== this.currentUserId) {
            this.showNotification(`${event.comment.user.name} commented on a post`);
        }
    }

    handleCommentDeleted(event) {
        console.log('📨 Received comment.deleted event:', event);

        const postElement = document.querySelector(`[data-post-id="${event.post_id}"]`);
        if (!postElement) {
            console.log('❌ Post element not found for post ID:', event.post_id);
            return;
        }

        const commentsCount = postElement.querySelector('.comments-count');
        if (commentsCount) {
            commentsCount.textContent = event.comments_count;
        }

        // Remove comment from UI if visible
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (commentElement) {
            console.log('🗑️ Removing comment from UI');
            commentElement.remove();
        } else {
            console.log('❌ Comment element not found for comment ID:', event.comment_id);
        }
    }

    handleCommentLiked(event) {
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (!commentElement) return;

        const likeCount = commentElement.querySelector('.comment-like-count');
        if (likeCount) {
            likeCount.textContent = event.likes_count;
        }

        const likeButton = commentElement.querySelector('.comment-like-btn');
        if (likeButton && event.user_id === this.currentUserId) {
            likeButton.classList.add('liked');
        }
    }

    handleCommentUnliked(event) {
        const commentElement = document.querySelector(`[data-comment-id="${event.comment_id}"]`);
        if (!commentElement) return;

        const likeCount = commentElement.querySelector('.comment-like-count');
        if (likeCount) {
            likeCount.textContent = event.likes_count;
        }

        const likeButton = commentElement.querySelector('.comment-like-btn');
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
    createCommentHtml(comment) {
        const currentUserId = this.currentUserId;
        const isOwner = comment.user_id === currentUserId;

        let deleteButtonHtml = '';
        if (isOwner) {
            deleteButtonHtml = `
                <button type="button" class="comment-delete-btn" onclick="deleteComment(${comment.id}, this)" title="Delete comment">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
        }

        return `
            <div class="comment nested-comment level-1" data-comment-id="${comment.id}">
    <div class="comment-avatar">
                    ${comment.user.profile && comment.user.profile.avatar
                        ? `<img src="/storage/${comment.user.profile.avatar}" alt="Avatar" class="comment-user-avatar">`
                        : `<div class="comment-user-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>`
                    }
            </div>

    <div class="comment-content-wrapper">
        <div class="comment-header">
            <div class="comment-user-info">
                <a href="/users/${comment.user.name}" class="comment-user-name">${comment.user.name}</a>
                <span class="comment-time">Just now</span>
            </div>
            <div class="comment-actions">
                                    ${deleteButtonHtml}
                            </div>
        </div>

        <div class="comment-body">
            <div class="comment-text">${comment.content}</div>
        </div>

        <div class="comment-footer">
            <div class="comment-interactions">
                <button type="button" class="comment-like-btn " onclick="likeComment(${comment.id}, this)">
                    <i class="fas fa-heart"></i>
                    <span class="comment-like-count">0</span>
                </button>
                                    <button type="button" class="comment-reply-btn" onclick="toggleReplyForm(${comment.id})">
                        <i class="fas fa-reply"></i>
                        Reply
                    </button>
                            </div>
        </div>

        <div id="reply-form-${comment.id}" class="comment-reply-form" style="display: none;">
            <div class="reply-form-container">
                <div class="reply-avatar">
                                            ${comment.user.profile && comment.user.profile.avatar
                                                ? `<img src="/storage/${comment.user.profile.avatar}" alt="Your avatar">`
                                                : `<div class="reply-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>`
                                            }
                                    </div>
                <div class="reply-input-container">
                    <textarea id="reply-content-${comment.id}" placeholder="Write a reply..." maxlength="280" required="" class="reply-textarea"></textarea>
                    <div class="reply-actions">
                        <button type="button" class="reply-submit-btn" onclick="submitReply(${comment.id}, ${comment.post_id})">
                            <i class="fas fa-paper-plane"></i>
                            Reply
                        </button>
                        <button type="button" class="reply-cancel-btn" onclick="toggleReplyForm(${comment.id})">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

<script>
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
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
function toggleLike(postId, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Find like count as sibling element (new layout)
    const likeContainer = buttonElement.parentElement;
    const likeCountElement = likeContainer ? likeContainer.querySelector('.like-count') : null;
    const isCurrentlyLiked = buttonElement.classList.contains('liked');

    // Get current count
    const currentCount = likeCountElement ? parseInt(likeCountElement.textContent) || 0 : 0;

    // Toggle button state immediately
    if (isCurrentlyLiked) {
        buttonElement.classList.remove('liked');
        buttonElement.style.background = 'var(--twitter-blue)';
        if (likeCountElement) {
            likeCountElement.textContent = currentCount - 1;
        }
    } else {
        buttonElement.classList.add('liked');
        buttonElement.style.background = 'red';
        if (likeCountElement) {
            likeCountElement.textContent = currentCount + 1;
        }
    }

    // Show/hide likers button based on new count
    const newCount = isCurrentlyLiked ? currentCount - 1 : currentCount + 1;
    const likersBtn = likeContainer ? likeContainer.querySelector('.likers-btn') : null;
    if (likersBtn) {
        likersBtn.style.display = newCount > 0 ? 'inline-flex' : 'none';
    }

    fetch(`/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Like response:', data);
        if (data.success) {
            // Real-time events will sync with other users
        } else {
            // Revert the optimistic update if request failed
            if (isCurrentlyLiked) {
                buttonElement.classList.add('liked');
                buttonElement.style.background = 'red';
                buttonElement.innerHTML = '<i class="fas fa-heart"></i> <span class="like-count">' + (parseInt(likeCountElement.textContent) + 1) + '</span>';
            } else {
                buttonElement.classList.remove('liked');
                buttonElement.style.background = 'var(--twitter-blue)';
                buttonElement.innerHTML = '<i class="fas fa-heart"></i> <span class="like-count">' + (parseInt(likeCountElement.textContent) - 1) + '</span>';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        // Revert the optimistic update on error
        if (isCurrentlyLiked) {
            buttonElement.classList.add('liked');
            buttonElement.style.background = 'red';
            buttonElement.innerHTML = '<i class="fas fa-heart"></i> <span class="like-count">' + (parseInt(likeCountElement.textContent) + 1) + '</span>';
        } else {
            buttonElement.classList.remove('liked');
            buttonElement.style.background = 'var(--twitter-blue)';
            buttonElement.innerHTML = '<i class="fas fa-heart"></i> <span class="like-count">' + (parseInt(likeCountElement.textContent) - 1) + '</span>';
        }
        // Show error notification
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to toggle like', true);
        }
    });
}

// Toggle save on a post
function toggleSave(postId, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Optimistically update UI immediately
    const saveTextElement = buttonElement.querySelector('.save-text');
    const isCurrentlySaved = buttonElement.classList.contains('saved');

    // Toggle button state immediately
    if (isCurrentlySaved) {
        buttonElement.classList.remove('saved');
        saveTextElement.textContent = 'Save';
        buttonElement.style.background = '#6c757d';
    } else {
        buttonElement.classList.add('saved');
        saveTextElement.textContent = 'Saved';
        buttonElement.style.background = '#17a2b8';
    }

    fetch(`/posts/${postId}/save`, {
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
            console.log('Save toggled successfully');
            // Real-time events will sync with other users
        } else {
            // Revert the optimistic update if request failed
            if (isCurrentlySaved) {
                buttonElement.classList.add('saved');
                saveTextElement.textContent = 'Saved';
                buttonElement.style.background = '#17a2b8';
            } else {
                buttonElement.classList.remove('saved');
                saveTextElement.textContent = 'Save';
                buttonElement.style.background = '#6c757d';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling save:', error);
        // Revert the optimistic update on error
        if (isCurrentlySaved) {
            buttonElement.classList.add('saved');
            saveTextElement.textContent = 'Saved';
            buttonElement.style.background = '#17a2b8';
        } else {
            buttonElement.classList.remove('saved');
            saveTextElement.textContent = 'Save';
            buttonElement.style.background = '#6c757d';
        }
        // Show error notification
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to toggle save', true);
        }
    });
}

// Like a comment
function likeComment(commentId, buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Optimistically update UI immediately
    const likeCountElement = buttonElement.querySelector('.comment-like-count');
    const isCurrentlyLiked = buttonElement.classList.contains('liked');

    // Toggle button state immediately
    if (isCurrentlyLiked) {
        buttonElement.classList.remove('liked');
        likeCountElement.textContent = parseInt(likeCountElement.textContent) - 1;
    } else {
        buttonElement.classList.add('liked');
        likeCountElement.textContent = parseInt(likeCountElement.textContent) + 1;
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
        if (data.success) {
            console.log('Comment like toggled successfully');
            // Real-time events will sync with other users
        } else {
            console.error('Comment like request failed');
            // Revert the optimistic update if request failed
            if (isCurrentlyLiked) {
                buttonElement.classList.add('liked');
                likeCountElement.textContent = parseInt(likeCountElement.textContent) + 1;
            } else {
                buttonElement.classList.remove('liked');
                likeCountElement.textContent = parseInt(likeCountElement.textContent) - 1;
            }
        }
    })
    .catch(error => {
        console.error('Error toggling comment like:', error);
        // Revert the optimistic update on error
        if (isCurrentlyLiked) {
            buttonElement.classList.add('liked');
            likeCountElement.textContent = parseInt(likeCountElement.textContent) + 1;
        } else {
            buttonElement.classList.remove('liked');
            likeCountElement.textContent = parseInt(likeCountElement.textContent) - 1;
        }
        // Show error notification
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to toggle comment like', true);
        }
    });
}

// Submit a new comment
function submitComment(postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const textarea = document.getElementById(`comment-content-${postId}`);
    const content = textarea.value.trim();

    // Disable the textarea and button during submission
    textarea.disabled = true;
    const submitButton = textarea.nextElementSibling;
    const originalButtonText = submitButton.textContent;
    submitButton.textContent = 'Posting...';
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
    .then(response => {
        console.log('Comment response status:', response.status);
        console.log('Comment response headers:', Object.fromEntries(response.headers.entries()));
        return response.json();
    })
    .then(data => {
        console.log('Comment response data:', data);

        if (data.success) {
            console.log('Comment posted successfully');
            // Clear the textarea
            textarea.value = '';

            // Add comment to UI immediately as fallback
            if (data.comment) {
                console.log('Comment data received:', data.comment);
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                console.log('Post element found:', postElement);

                if (postElement) {
                    // Debug: Log all elements inside post
                    console.log('Post element children:', postElement.children);
                    console.log('Post element innerHTML:', postElement.innerHTML.substring(0, 500) + '...');

                    const commentsContainer = postElement.querySelector('.comments-container');
                    console.log('Comments container found:', commentsContainer);

                    // Try different selectors
                    const commentsSection = postElement.querySelector('.comments-section');
                    console.log('Comments section found:', commentsSection);

                    if (commentsContainer) {
                        console.log('➕ Adding comment to UI as fallback');
                        const commentHtml = window.realTimeManager.createCommentHtml(data.comment);
                        console.log('Generated comment HTML length:', commentHtml.length);
                        commentsContainer.insertAdjacentHTML('beforeend', commentHtml);
                        console.log('✅ Comment added to UI via fallback');
                    } else {
                        console.log('❌ Comments container not found - trying to find it globally');
                        const globalContainer = document.querySelector('.comments-container');
                        console.log('Global comments container:', globalContainer);
                        if (globalContainer) {
                            const commentHtml = window.realTimeManager.createCommentHtml(data.comment);
                            globalContainer.insertAdjacentHTML('beforeend', commentHtml);
                            console.log('✅ Comment added to global comments container');
                        }
                    }
                } else {
                    console.log('❌ Post element not found for ID:', postId);
                    // Try finding any comments container on the page
                    const anyContainer = document.querySelector('.comments-container');
                    console.log('Any comments container on page:', anyContainer);
                }
            } else {
                console.log('❌ No comment data in response');
            }

            // Real-time events will handle syncing with other users
        } else {
            console.error('Comment submission failed');
            if (window.realTimeManager) {
                window.realTimeManager.showNotification('Failed to post comment', true);
            }
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to post comment', true);
        }
    })
    .finally(() => {
        // Re-enable the textarea and button
        textarea.disabled = false;
        submitButton.textContent = originalButtonText;
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
            console.log('Reply posted successfully');
            // Clear the textarea and hide the reply form
            textarea.value = '';
            toggleReplyForm(commentId);

            // Add reply to UI immediately as fallback
            if (data.comment) {
                const parentCommentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (parentCommentElement) {
                    // Create or find the replies container
                    let repliesContainer = parentCommentElement.querySelector('.replies-container');
                    if (!repliesContainer) {
                        repliesContainer = document.createElement('div');
                        repliesContainer.className = 'replies-container';
                        parentCommentElement.appendChild(repliesContainer);
                    }

                    console.log('➕ Adding reply to UI as fallback');
                    const replyHtml = window.realTimeManager.createCommentHtml(data.comment);
                    repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
                    console.log('✅ Reply added to UI via fallback');
                }
            }

            // Real-time events will handle syncing with other users
        } else {
            console.error('Reply submission failed');
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

// Toggle follow/unfollow for a user
function toggleFollow(buttonElement, userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // Get username from the button's data attribute or from a global variable
    const username = buttonElement.getAttribute('data-username') || buttonElement.closest('[data-username]')?.getAttribute('data-username');

    // Optimistically update UI immediately
    const isCurrentlyFollowing = buttonElement.classList.contains('following');

    // Toggle button state immediately
    if (isCurrentlyFollowing) {
        buttonElement.classList.remove('following');
        buttonElement.textContent = 'Follow';
        buttonElement.style.background = 'var(--twitter-blue)';
        // Update counts: decrease followed user's followers, decrease current user's following
        updateFollowerCount(userId, -1);
        updateFollowingCount(window.currentUserId, -1);
    } else {
        buttonElement.classList.add('following');
        buttonElement.textContent = 'Following';
        buttonElement.style.background = '#28a745';
        // Update counts: increase followed user's followers, increase current user's following
        updateFollowerCount(userId, 1);
        updateFollowingCount(window.currentUserId, 1);
    }

    fetch(`/users/${username}/follow`, {
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
            console.log('Follow toggled successfully');
            // Real-time events will sync with other users
        } else {
            console.error('Follow request failed');
            // Revert the optimistic update if request failed
            if (isCurrentlyFollowing) {
                buttonElement.classList.add('following');
                buttonElement.textContent = 'Following';
                buttonElement.style.background = '#28a745';
                // Revert count updates
                updateFollowerCount(userId, 1);
                updateFollowingCount(window.currentUserId, 1);
            } else {
                buttonElement.classList.remove('following');
                buttonElement.textContent = 'Follow';
                buttonElement.style.background = 'var(--twitter-blue)';
                // Revert count updates
                updateFollowerCount(userId, -1);
                updateFollowingCount(window.currentUserId, -1);
            }
        }
    })
    .catch(error => {
        console.error('Error toggling follow:', error);
        // Revert the optimistic update on error
        if (isCurrentlyFollowing) {
            buttonElement.classList.add('following');
            buttonElement.textContent = 'Following';
            buttonElement.style.background = '#28a745';
            // Revert count updates
            updateFollowerCount(userId, 1);
            updateFollowingCount(window.currentUserId, 1);
        } else {
            buttonElement.classList.remove('following');
            buttonElement.textContent = 'Follow';
            buttonElement.style.background = 'var(--twitter-blue)';
            // Revert count updates
            updateFollowerCount(userId, -1);
            updateFollowingCount(window.currentUserId, -1);
        }
        // Show error notification
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to toggle follow', true);
        }
    });
}

// Helper functions to update counts
function updateFollowerCount(userId, delta) {
    const followerElements = document.querySelectorAll(`[data-user-followers="${userId}"]`);
    followerElements.forEach(el => {
        const currentCount = parseInt(el.textContent) || 0;
        el.textContent = currentCount + delta;
    });
}

function updateFollowingCount(userId, delta) {
    const followingElements = document.querySelectorAll(`[data-user-following="${userId}"]`);
    followingElements.forEach(el => {
        const currentCount = parseInt(el.textContent) || 0;
        el.textContent = currentCount + delta;
    });
}

// Toggle block/unblock for a user
function toggleBlock(buttonElement) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const userId = buttonElement.getAttribute('data-user-id');
    const username = buttonElement.getAttribute('data-username');

    // Optimistically update UI immediately
    const isCurrentlyBlocked = buttonElement.classList.contains('blocked') || buttonElement.textContent.trim() === 'Unblock';
    const originalText = buttonElement.textContent;

    // Toggle button state immediately
    if (isCurrentlyBlocked) {
        buttonElement.classList.remove('blocked');
        buttonElement.textContent = 'Block';
        buttonElement.style.background = '#dc3545';
    } else {
        buttonElement.classList.add('blocked');
        buttonElement.textContent = 'Unblock';
        buttonElement.style.background = '#6c757d';
    }

    fetch(`/users/${username}/block`, {
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
            console.log('Block toggled successfully');

            // Handle UI changes - find the container that holds the buttons
            let buttonContainer = buttonElement.closest('.action-buttons') || buttonElement.parentElement;

            if (buttonContainer) {
                if (isCurrentlyBlocked) {
                    // We just unblocked, so we need to add the follow button
                    const followButton = document.createElement('button');
                    followButton.type = 'button';
                    followButton.className = 'btn follow-btn';
                    followButton.setAttribute('data-user-id', userId);
                    followButton.setAttribute('data-username', username);
                    followButton.onclick = () => toggleFollow(followButton);
                    followButton.textContent = 'Follow';
                    followButton.style.background = 'var(--twitter-blue)';
                    followButton.style.marginRight = '10px';

                    // Insert follow button before the block button
                    buttonContainer.insertBefore(followButton, buttonElement);
                } else {
                    // We just blocked, so we need to remove the follow button
                    const followButton = buttonContainer.querySelector('.follow-btn');
                    if (followButton) {
                        followButton.remove();
                    }
                }
            }

            // Real-time events will sync with other users if needed
        } else {
            console.error('Block request failed');
            // Revert the optimistic update if request failed
            if (isCurrentlyBlocked) {
                buttonElement.classList.add('blocked');
                buttonElement.textContent = originalText;
                buttonElement.style.background = '#6c757d';
            } else {
                buttonElement.classList.remove('blocked');
                buttonElement.textContent = originalText;
                buttonElement.style.background = '#dc3545';
            }
        }
    })
    .catch(error => {
        console.error('Error toggling block:', error);
        // Revert the optimistic update on error
        if (isCurrentlyBlocked) {
            buttonElement.classList.add('blocked');
            buttonElement.textContent = originalText;
            buttonElement.style.background = '#6c757d';
        } else {
            buttonElement.classList.remove('blocked');
            buttonElement.textContent = originalText;
            buttonElement.style.background = '#dc3545';
        }
        // Show error notification
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to toggle block', true);
        }
    });
}

function deletePost(postSlug, button) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    const postId = button.closest('.post').dataset.postId;
    const postElement = document.getElementById('post-' + postId);

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
            showNotification('Post deleted successfully!', 'success');
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
    console.log('🗑️ deleteComment called with:', { commentId, buttonElement });

    if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
        return;
    }

    if (!commentId || commentId === 'undefined' || commentId === 'null') {
        console.error('❌ Invalid commentId:', commentId);
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Invalid comment ID', true);
        }
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Disable button during deletion
    buttonElement.disabled = true;
    buttonElement.textContent = 'Deleting...';

    console.log('🔗 Making DELETE request to:', `/comments/${commentId}`);

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
            console.log('Comment deleted successfully');
            // Remove the comment from the UI
            const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
            if (commentElement) {
                commentElement.remove();
            }
            // Comment removed silently without notification
        } else {
            console.error('Comment deletion failed');
            if (window.realTimeManager) {
                window.realTimeManager.showNotification('Failed to delete comment', true);
            }
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
        if (window.realTimeManager) {
            window.realTimeManager.showNotification('Failed to delete comment', true);
        }
    })
    .finally(() => {
        // Re-enable button
        buttonElement.disabled = false;
        buttonElement.textContent = 'Delete';
    });
}

// ==================== COMMENT REPLY FUNCTIONS ====================

// Toggle reply form visibility
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
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