/**
 * Real-Time Social Updates - WebSocket + AJAX Hybrid
 * Production-ready real-time system for Laravel Social
 */

class SocialRealtime {
    constructor() {
        this.userId = window.currentUserId;
        this.echo = null;
        this.pollingInterval = null;
        this.isConnected = false;
        this.channels = new Map();

        this.init();
    }

    init() {
        if (!this.userId) {
            return;
        }

        this.initializeEcho();
        this.setupChannels();
        this.startPolling();
    }

    initializeEcho() {
        
        const checkEcho = () => {
            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo not loaded - using AJAX polling only');
                return false;
            }

            
            if (!window.Echo || !window.Echo.connector || !window.Echo.connector.pusher) {
                console.warn('Echo not fully initialized - waiting for echo:ready event');
                return false;
            }

            try {
                this.echo = window.Echo;
                console.log('🎯 Echo is ready, initializing real-time connections');

                
                this.echo.connector.pusher.connection.bind('connected', () => {
                    this.isConnected = true;
                    console.log('🔗 WebSocket connected');
                    this.showConnectionStatus(true);
                });

                this.echo.connector.pusher.connection.bind('disconnected', () => {
                    this.isConnected = false;
                    console.log('❌ WebSocket disconnected');
                    this.showConnectionStatus(false);
                });

                this.echo.connector.pusher.connection.bind('error', (error) => {
                    console.error('WebSocket connection error:', error);
                    this.isConnected = false;
                    this.showConnectionStatus(false);
                });

                
                const state = this.echo.connector.pusher.connection.state;
                if (state === 'connected') {
                    this.isConnected = true;
                    this.showConnectionStatus(true);
                }

                return true;

            } catch (error) {
                console.error('Echo initialization failed:', error);
                this.isConnected = false;
                return false;
            }
        };

        
        if (!checkEcho()) {
            
            window.addEventListener('echo:ready', () => {
                console.log('Received echo:ready event, initializing Echo');
                checkEcho();
                if (this.echo) {
                    this.setupChannels(); 
                }
            }, { once: true }); 
        }
    }

    setupChannels() {
        if (!this.echo) return;

        
        this.listenToUserChannel();

        
        this.listenToPostChannels();
    }

    listenToUserChannel() {
        const userChannel = this.echo.private(`user.${this.userId}`)
            .listen('.notification.received', (data) => {
                this.handleNotification(data);
            })
            .listen('.message.received', (data) => {
                this.handleMessage(data);
            })
            .error((error) => {
                console.error('User channel error:', error);
            });

        this.channels.set('user', userChannel);
    }

    listenToPostChannels() {
        
        document.querySelectorAll('[data-post-id]').forEach(post => {
            const postId = post.dataset.postId;
            if (postId && !this.channels.has(`post-${postId}`)) {
                const postChannel = this.echo.private(`post.${postId}`)
                    .listen('.comment.added', (data) => {
                        this.handleCommentAdded(data);
                    })
                    .listen('.post.updated', (data) => {
                        this.handlePostUpdated(data);
                    })
                    .error((error) => {
                        console.error(`Post ${postId} channel error:`, error);
                    });

                this.channels.set(`post-${postId}`, postChannel);
            }
        });
    }

    startPolling() {
        
        this.pollingInterval = setInterval(() => {
            this.pollUpdates();
        }, 30000);

        
        setTimeout(() => this.pollUpdates(), 1000);
    }

    async pollUpdates() {
        try {
            const response = await fetch('/api/user/realtime-updates', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.processPollingData(data);
            }
        } catch (error) {
            console.warn('Polling failed:', error);
        }
    }

    processPollingData(data) {
        
        if (data.notifications !== undefined) {
            this.updateNotificationBadge(data.notifications);
        }

        
        if (data.posts) {
            Object.entries(data.posts).forEach(([postId, postData]) => {
                this.updatePostUI(postId, postData);
            });
        }
    }

    
    handleNotification(data) {
        this.updateNotificationBadge(data.unread_count);
        this.showToast(data.notification.message, 'info');
        this.refreshNotifications();
    }

    handleMessage(data) {
        this.showToast(`New message from ${data.sender.name}`, 'message');
        
        this.updateChatUI(data);
    }

    handleCommentAdded(data) {
        this.addCommentToPost(data.post_id, data.comment);
        this.updateCommentCount(data.post_id);
        this.showToast('New comment added', 'info', 2000);
    }

    handlePostUpdated(data) {
        this.updatePostUI(data.post_id, data);
    }

    
    updateNotificationBadge(count) {
        // Update the notificationBadge element (if exists)
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = count > 0 ? 'flex' : 'none';

            if (count > 0) {
                badge.classList.add('pulse');
                setTimeout(() => badge.classList.remove('pulse'), 1000);
            }
        }

        // Update the header notification badge
        const headerBadge = document.getElementById('header-notification-badge');
        if (headerBadge) {
            headerBadge.textContent = count > 99 ? '99+' : count;
            headerBadge.style.display = count > 0 ? 'inline-flex' : 'none';

            if (count > 0) {
                headerBadge.classList.add('pulse');
                setTimeout(() => headerBadge.classList.remove('pulse'), 1000);
            }
        }
    }

    updatePostUI(postId, data) {
        const post = document.querySelector(`[data-post-id="${postId}"]`);
        if (!post) return;

        
        if (data.likes_count !== undefined) {
            const likeCount = post.querySelector('.like-count');
            if (likeCount) likeCount.textContent = data.likes_count;
        }

        
        if (data.comments_count !== undefined) {
            this.updateCommentCount(postId, data.comments_count);
        }

        
        if (data.action) {
            const likeBtn = post.querySelector('.like-btn');
            if (likeBtn) {
                likeBtn.classList.toggle('liked', data.action === 'like');
            }
        }
    }

    updateCommentCount(postId, count = null) {
        const post = document.querySelector(`[data-post-id="${postId}"]`);

        if (count === null) {
            
            fetch(`/api/posts/${postId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.comments_count !== undefined) {
                        this.updateCommentCount(postId, data.comments_count);
                    }
                });
        } else {
            
            const counts = post.querySelectorAll('.comment-count');
            counts.forEach(el => el.textContent = count);
        }
    }

    addCommentToPost(postId, comment) {
        const post = document.querySelector(`[data-post-id="${postId}"]`);
        if (!post) return;

        const commentsContainer = post.querySelector('.comments-container');
        if (!commentsContainer) return;

        
        const visibleComments = commentsContainer.querySelectorAll('.comment-simple').length;
        if (visibleComments >= 2) {
            
            const showMoreBtn = commentsContainer.querySelector('.show-more-comments-btn');
            if (showMoreBtn) {
                const match = showMoreBtn.textContent.match(/(\d+)/);
                const current = match ? parseInt(match[1]) : 0;
                showMoreBtn.innerHTML = showMoreBtn.innerHTML.replace(/\d+/, current + 1);
            }
            return;
        }

        
        const commentHTML = this.createCommentHTML(comment);
        const firstComment = commentsContainer.querySelector('.comment-simple');

        if (firstComment) {
            firstComment.insertAdjacentHTML('beforebegin', commentHTML);
        } else {
            commentsContainer.insertAdjacentHTML('afterbegin', commentHTML);
        }

        
        const newComment = commentsContainer.querySelector('.comment-simple:first-child');
        if (newComment) {
            newComment.style.animation = 'highlightNew 2s ease-out';
        }
    }

    createCommentHTML(comment) {
        const avatar = comment.user.avatar
            ? `<img src="${comment.user.avatar}" alt="${comment.user.name}" class="comment-avatar-simple">`
            : `<div class="comment-avatar-simple comment-avatar-placeholder-simple">${comment.user.name.charAt(0)}</div>`;

        return `
            <div class="comment-simple" data-comment-id="${comment.id}">
                <div class="comment-header-simple">
                    <div class="comment-user-info-simple">
                        ${avatar}
                        <div class="comment-meta-simple">
                            <a href="/users/${comment.user.name}" class="comment-author-simple">${comment.user.name}</a>
                            <span class="comment-time-simple">${comment.created_at}</span>
                        </div>
                    </div>
                </div>
                <div class="comment-content-simple">
                    <p class="comment-text-simple">${comment.content}</p>
                </div>
                <div class="comment-actions-simple">
                    <button class="comment-action-btn" onclick="likeComment(${comment.id}, this)">
                        <i class="fas fa-heart"></i> <span class="count">${comment.likes_count}</span>
                    </button>
                </div>
            </div>
        `;
    }

    updateChatUI(data) {
        
        const chatContainer = document.querySelector('.chat-messages');
        if (chatContainer && data.conversation_id) {
            
            const messageHTML = `
                <div class="message received">
                    <div class="message-content">${data.content}</div>
                    <div class="message-time">${new Date().toLocaleTimeString()}</div>
                </div>
            `;
            chatContainer.insertAdjacentHTML('beforeend', messageHTML);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    refreshNotifications() {
        
        if (typeof loadNotifications === 'function') {
            loadNotifications();
        }
    }

    showToast(message, type = 'info', duration = 3000) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type, duration);
        } else {
            console.log(`Toast: ${message} (${type})`);
        }
    }

    showConnectionStatus(connected) {
        
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            statusElement.textContent = connected ? '🟢 Connected' : '🔴 Disconnected';
            statusElement.className = connected ? 'status-connected' : 'status-disconnected';
        }
    }

    
    destroy() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        if (this.echo) {
            this.channels.forEach(channel => {
                if (channel.stopListening) {
                    channel.stopListening();
                }
            });
            this.echo.disconnect();
        }

        console.log('Social Real-time system destroyed');
    }
}


window.addEventListener('load', () => {
    
    setTimeout(() => {
        window.socialRealtime = new SocialRealtime();
    }, 100);
});


window.addEventListener('beforeunload', () => {
    if (window.socialRealtime) {
        window.socialRealtime.destroy();
    }
});
