/* Realtime - Chat and Notifications Polling */

(function() {
    'use strict';

    window.RealTimeConfig = {
        chatListInterval: 2000,
        chatRoomInterval: 2000,
        accountStatusInterval: 10000,
        notificationsInterval: 2000,
        onlineStatusInterval: 10000,
        userId: window.currentUserId || null,
        active: true
    };

    const state = {
        chatListTimer: null,
        chatRoomTimer: null,
        accountStatusTimer: null,
        notificationsTimer: null,
        onlineStatusTimer: null,
        lastMessageId: 0,
        conversationSlug: null,
        activeConversationId: null,
        isVisible: true,
        onlineUserIds: new Set(),
        deletedMessageIds: new Set(),
        pendingMessageIds: new Set(), // Track messages that were sent but not yet confirmed
        lastPollTime: 0
    };

    function init() {
        if (!window.RealTimeConfig.userId) {
            console.warn('RealTime: No userId configured');
            return;
        }

        console.log('RealTime: Initializing for user', window.RealTimeConfig.userId);

        // Request notification permission on user interaction (not on page load)
        // This prevents the browser error: "Notification permission may only be requested from inside a short running user-generated event handler"
        document.addEventListener('click', function requestPermissionOnClick() {
            if ('Notification' in window && Notification.permission === 'default') {
                requestNotificationPermission();
                // Remove listener after first click to avoid repeated requests
                document.removeEventListener('click', requestPermissionOnClick);
            }
        }, { once: true });

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('beforeunload', cleanup);

        let path = window.location.pathname;
        // Normalize to avoid trailing slash causing empty slug values (e.g. '/chat/abc/').
        if (path.endsWith('/') && path !== '/') {
            path = path.slice(0, -1);
        }
        console.log('RealTime: Current path:', path, 'Full URL:', window.location.href);

        // Check for chat index page (handle both /chat and /chat/)
        if (path === '/chat' || path === '/chat/') {
            console.log('RealTime: Starting chat list polling');
            startChatListPolling();
        } else if (path.startsWith('/chat/') && path !== '/chat' && path !== '/chat/') {
            state.conversationSlug = path.split('/').pop();
            console.log('RealTime: Starting chat room polling for slug:', state.conversationSlug);

            // Wait for DOM to be ready
            const initChatRoom = () => {
                const lastMessageEl = document.querySelector('#chatMessages .message:last-child');
                if (lastMessageEl) {
                    state.lastMessageId = parseInt(lastMessageEl.dataset.messageId) || 0;
                    console.log('RealTime: Last message ID:', state.lastMessageId);
                }
                state.deletedMessageIds.clear();
                if (window.activeConversationId) {
                    state.activeConversationId = window.activeConversationId;
                }
                startChatRoomPolling();
                startChatListPolling();
                initTypingIndicator();
                console.log('RealTime: Chat room polling started');
            };

            // Try immediately and again after 500ms as fallback
            initChatRoom();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChatRoom);
            } else {
                setTimeout(initChatRoom, 500);
            }
        }

        startAccountStatusCheck();
        startNotificationsPolling();
        startOnlineStatusPing();
    }

    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            console.log('RealTime: Requesting notification permission');
            Notification.requestPermission().then(permission => {
                console.log('RealTime: Notification permission:', permission);
            });
        }
    }

    function handleVisibilityChange() {
        state.isVisible = document.visibilityState === 'visible';
    }

    function cleanup() {
        window.RealTimeConfig.active = false;
        if (state.chatListTimer) clearInterval(state.chatListTimer);
        if (state.chatRoomTimer) clearInterval(state.chatRoomTimer);
        if (state.accountStatusTimer) clearInterval(state.accountStatusTimer);
        if (state.notificationsTimer) clearInterval(state.notificationsTimer);
        if (state.onlineStatusTimer) clearInterval(state.onlineStatusTimer);
    }

    function startChatListPolling() {
        if (state.chatListTimer) return;

        // Initial load with retry
        setTimeout(() => {
            refreshChatList();
            setTimeout(() => refreshChatList(), 300);
        }, 100);

        state.chatListTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshChatList();
            }
        }, 1500); // Poll every 1.5 seconds for faster updates

        startChatListOnlineStatusPolling();
    }

    function startChatListOnlineStatusPolling() {
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshAllOnlineStatuses();
            }
        }, 10000);
    }

    function refreshChatList() {
        const url = '/chat/conversations?t=' + Date.now();
        console.log('RealTime: Refreshing chat list:', url);
        
        fetch(url, {
            credentials: 'include',
            cache: 'no-cache', // Prevent browser caching
            headers: {
                'X-CSRF-TOKEN': window.getCsrfToken ? window.getCsrfToken() : '',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate', // HTTP 1.1
                'Pragma': 'no-cache' // HTTP 1.0
            }
        })
        .then(response => {
            console.log('RealTime: Chat list HTTP status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('RealTime: Chat list data:', data);
            if (data.success && data.conversations) {
                console.log('RealTime: Found', data.conversations.length, 'conversations');
                updateChatListUI(data.conversations);
                refreshAllOnlineStatuses();
            } else {
                console.log('RealTime: Chat list response not successful');
            }
        })
        .catch(err => console.error('RealTime: Chat list refresh error:', err));
    }

    function updateChatListUI(conversations) {
        let conversationsList = document.getElementById('sidebarConvList') || document.getElementById('conversationsList');
        console.log('RealTime: updateChatListUI - conversationsList element:', conversationsList);
        if (!conversationsList) {
            console.log('RealTime: No conversations list element found');
            return;
        }
        
        console.log('RealTime: Updating', conversations.length, 'conversations in UI');
        
        const TYPING_CHECK_LIMIT = 25;
        let idx = 0;
        const seenConvIds = new Set();

        conversations.forEach(conv => {
            idx++;
            seenConvIds.add(conv.id);

            let item = document.querySelector('.conversation-item[href*="/chat/' + conv.id + '"]');
            if (!item && conv.slug) {
                item = document.querySelector('.conversation-item[href*="/chat/' + conv.slug + '"]');
            }

            if (!item) {
                console.log('RealTime: Creating new conversation item for:', conv.id, conv.name || conv.other_user?.username);
                createConversationItem(conv, conversationsList, conversations);
                return;
            }

            console.log('RealTime: Updating existing conversation item:', conv.id);
            updateExistingConversationItem(item, conv, idx, TYPING_CHECK_LIMIT);
        });
        
        // Sort conversations
        const items = Array.from(conversationsList.querySelectorAll('.conversation-item'));
        items.sort((a, b) => {
            const aHref = a.getAttribute('href') || '';
            const bHref = b.getAttribute('href') || '';
            const aConv = conversations.find(c => '/chat/' + c.id === aHref || '/chat/' + c.slug === aHref);
            const bConv = conversations.find(c => '/chat/' + c.id === bHref || '/chat/' + c.slug === bHref);
            if (!aConv || !bConv) return 0;
            const aUnread = aConv.unread_count > 0;
            const bUnread = bConv.unread_count > 0;
            if (aUnread !== bUnread) return bUnread ? 1 : -1;
            const aTime = aConv.last_message_at ? new Date(aConv.last_message_at).getTime() : 0;
            const bTime = bConv.last_message_at ? new Date(bConv.last_message_at).getTime() : 0;
            return bTime - aTime;
        });
        items.forEach(item => conversationsList.appendChild(item));
        console.log('RealTime: Chat list UI update complete');
    }

    function createConversationItem(conv, conversationsList) {
        const isGroup = conv.is_group || false;
        const displayName = isGroup ? (conv.name || 'Group') : (conv.other_user?.username || 'User');
        const avatarUrl = isGroup ? (conv.avatar || null) : (conv.other_user?.avatar_url || null);

        let avatarHtml = '';
        if (avatarUrl) {
            avatarHtml = '<img src="' + escapeHtml(avatarUrl) + '" alt="' + escapeHtml(displayName) + '">';
        } else if (isGroup) {
            avatarHtml = '<div class="avatar-fallback group"><i class="fas fa-users"></i></div>';
        } else {
            avatarHtml = '<div class="avatar-fallback">' + escapeHtml(displayName.substring(0, 1).toUpperCase()) + '</div>';
        }

        let onlineIndicatorHtml = '';
        if (!isGroup && conv.other_user) {
            const isOnline = conv.other_user.is_online && conv.other_user.last_active &&
                (new Date().getTime() - new Date(conv.other_user.last_active).getTime()) < 120000;
            onlineIndicatorHtml = '<span class="online-indicator ' + (isOnline ? 'online' : '') + '" data-user-id="' + conv.other_user.id + '"></span>';
        }

        let previewHtml = '';
        let previewClass = conv.unread_count > 0 ? 'unread-text' : '';
        if (conv.latest_message) {
            const msg = conv.latest_message;
            const isMyMessage = msg.sender_id == window.RealTimeConfig.userId;
            const msgType = msg.type || 'text';
            const t = window.chatTranslations || {};
            const isGroup = conv.is_group === true || conv.is_group === 1 || conv.is_group === '1';

            let messageIcon = '', messagePreview = '';
            switch (msgType) {
                case 'image': messageIcon = '📷 '; messagePreview = isMyMessage ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo'); break;
                case 'video': messageIcon = '🎥 '; messagePreview = isMyMessage ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video'); break;
                case 'audio': messageIcon = '🎤 '; messagePreview = isMyMessage ? (t.you_sent_audio || 'You sent an audio') : (t.sent_audio || 'Sent an audio'); break;
                case 'document': messageIcon = '📎 '; messagePreview = isMyMessage ? (t.you_sent_document || 'You sent a document') : (t.sent_document || 'Sent a document'); break;
                case 'gif': messageIcon = 'GIF '; messagePreview = isMyMessage ? (t.you_sent_gif || 'You sent a GIF') : (t.sent_gif || 'Sent a GIF'); break;
                case 'sticker': messageIcon = '⭐ '; messagePreview = isMyMessage ? (t.you_sent_sticker || 'You sent a sticker') : (t.sent_sticker || 'Sent a sticker'); break;
                case 'story_reply': messageIcon = '📸 '; messagePreview = isMyMessage ? (t.you_replied_to_story || 'You replied to story') : (t.replied_to_story || 'Replied to your story'); break;
                case 'group_invite': messageIcon = '👥 '; messagePreview = isMyMessage ? (t.you_sent_group_invite || 'You sent a group invite') : (t.sent_group_invite || 'Sent a group invite'); break;
                default: messagePreview = msg.content || '';
            }

            if (isMyMessage && msgType !== 'story_reply' && msgType !== 'image' && msgType !== 'video' && msgType !== 'audio' && msgType !== 'document' && msgType !== 'gif' && msgType !== 'sticker' && msgType !== 'group_invite') {
                messagePreview = (t.you || 'You') + ': ' + messagePreview;
            }

            // Don't add username prefix for system messages (they already contain usernames in content)
            if (isGroup && !isMyMessage && msg.sender_username && msgType !== 'system') {
                messagePreview = msg.sender_username + ': ' + messagePreview;
            }

            let statusIcon = '';
            if (isMyMessage) {
                statusIcon = msg.read_at ? '<i class="fas fa-check-double read-status read"></i> ' : '<i class="fas fa-check read-status sent"></i> ';
            }

            previewHtml = statusIcon + '<span class="preview-text ' + previewClass + '">' + escapeHtml(messageIcon + messagePreview) + '</span>';
        }

        let unreadBadgeHtml = '';
        if (conv.unread_count > 0) {
            const badgeCount = conv.unread_count > 99 ? '99+' : conv.unread_count;
            unreadBadgeHtml = '<span class="unread-pill" style="display: inline-block;">' + badgeCount + '</span>';
        }

        const timeHtml = conv.last_message_at ? formatMessageTime(conv.last_message_at) : '';

        const typingHtml = conv.typing ? '<span class="typing-indicator-inline" style="display:inline; color:#25d366; font-size:11px; font-style:italic; margin-left:6px;">' + (window.chatTranslations?.typing || 'Typing...') + '</span>' : '';

        const itemHtml = '<a href="/chat/' + conv.slug + '" class="conversation-item ' + (conv.unread_count > 0 ? 'unread' : '') + '" data-name="' + escapeHtml(displayName) + '">' +
            '<div class="conv-avatar">' + avatarHtml + onlineIndicatorHtml + '</div>' +
            '<div class="conv-content">' +
            '<div class="conv-header"><div class="conv-title-container"><div class="conv-title">' + escapeHtml(displayName) + typingHtml + '</div><span class="conv-time">' + timeHtml + '</span></div></div>' +
            '<div class="conv-footer"><p class="conv-preview ' + previewClass + '">' + previewHtml + '</p>' + unreadBadgeHtml + '</div>' +
            '</div></a>';

        const temp = document.createElement('div');
        temp.innerHTML = itemHtml.trim();
        const newItem = temp.firstElementChild;

        const emptyState = conversationsList.querySelector('.empty-state');
        if (emptyState) emptyState.style.display = 'none';

        const firstItem = conversationsList.querySelector('.conversation-item');
        if (firstItem) {
            conversationsList.insertBefore(newItem, firstItem);
        } else {
            conversationsList.appendChild(newItem);
        }
    }

    function updateExistingConversationItem(item, conv, idx, TYPING_CHECK_LIMIT) {
        const preview = item.querySelector('.conv-preview');
        if (preview && conv.latest_message) {
            const msgType = conv.latest_message.type || 'text';
            const content = conv.latest_message.content || '';
            const isMyMessage = conv.latest_message.sender_id == window.RealTimeConfig.userId;
            const t = window.chatTranslations || {};
            const isGroup = conv.is_group === true || conv.is_group === 1 || conv.is_group === '1';

            let messageIcon = '', messagePreview = '';
            switch (msgType) {
                case 'image': messageIcon = '📷 '; messagePreview = isMyMessage ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo'); break;
                case 'video': messageIcon = '🎥 '; messagePreview = isMyMessage ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video'); break;
                case 'audio': messageIcon = '🎤 '; messagePreview = isMyMessage ? (t.you_sent_audio || 'You sent an audio') : (t.sent_audio || 'Sent an audio'); break;
                case 'document': messageIcon = '📎 '; messagePreview = isMyMessage ? (t.you_sent_document || 'You sent a document') : (t.sent_document || 'Sent a document'); break;
                case 'gif': messageIcon = 'GIF '; messagePreview = isMyMessage ? (t.you_sent_gif || 'You sent a GIF') : (t.sent_gif || 'Sent a GIF'); break;
                case 'sticker': messageIcon = '⭐ '; messagePreview = isMyMessage ? (t.you_sent_sticker || 'You sent a sticker') : (t.sent_sticker || 'Sent a sticker'); break;
                case 'story_reply': messageIcon = '📸 '; messagePreview = isMyMessage ? (t.you_replied_to_story || 'You replied to story') : (t.replied_to_story || 'Replied to your story'); break;
                case 'group_invite': messageIcon = '👥 '; messagePreview = isMyMessage ? (t.you_sent_group_invite || 'You sent a group invite') : (t.sent_group_invite || 'Sent a group invite'); break;
                default: messagePreview = content;
            }

            if (isMyMessage && msgType !== 'story_reply' && msgType !== 'image' && msgType !== 'video' && msgType !== 'audio' && msgType !== 'document' && msgType !== 'gif' && msgType !== 'sticker' && msgType !== 'group_invite') {
                messagePreview = (t.you || 'You') + ': ' + messagePreview;
            }

            // Don't add username prefix for system messages (they already contain usernames in content)
            if (isGroup && !isMyMessage && conv.latest_message?.sender_username && msgType !== 'system') {
                messagePreview = conv.latest_message.sender_username + ': ' + messagePreview;
            }

            let statusIcon = '';
            if (isMyMessage) {
                statusIcon = conv.latest_message.read_at ? '<i class="fas fa-check-double read-status read"></i> ' : '<i class="fas fa-check read-status sent"></i> ';
            }

            preview.innerHTML = statusIcon + '<span class="preview-text">' + escapeHtml(messageIcon + messagePreview) + '</span>';

            if (conv.unread_count > 0) {
                preview.classList.add('unread-text');
            } else {
                preview.classList.remove('unread-text');
            }
        }

        const footer = item.querySelector('.conv-footer');
        if (footer) {
            let badge = footer.querySelector('.unread-pill');
            if (conv.unread_count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'unread-pill';
                    footer.appendChild(badge);
                }
                badge.textContent = conv.unread_count > 99 ? '99+' : conv.unread_count;
                badge.style.display = 'inline-block';
                item.classList.add('unread');
            } else {
                if (badge) badge.style.display = 'none';
                item.classList.remove('unread');
            }
        }

        const timeEl = item.querySelector('.conv-time');
        if (timeEl && conv.last_message_at) {
            timeEl.textContent = formatMessageTime(conv.last_message_at);
        }

        // Typing indicator state
        const typingInline = item.querySelector('.typing-indicator-inline');
        if (typingInline) {
            typingInline.style.display = conv.typing ? 'inline' : 'none';
        }
    }

    function formatMessageTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        if (diff < 86400000) return hours + ':' + minutes;
        if (diff < 172800000) return 'Yesterday';
        if (diff < 604800000) return date.toLocaleDateString('en-US', { weekday: 'short' });
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function startChatRoomPolling() {
        if (state.chatRoomTimer) return;
        checkCurrentChatUserOnlineStatus();
        startTypingStatusPolling();
        state.chatRoomTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkForNewMessages();
            }
        }, 500); // Poll every 500ms for faster message delivery
        startChatUserStatusPolling();
        startReadReceiptPolling();
        startMessageStatusPolling();
    }

    function startTypingStatusPolling() {
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkTypingStatus();
            }
        }, 500); // Check typing status every 500ms for instant updates
    }

    function checkTypingStatus() {
        if (!state.conversationSlug) return;

        fetch('/chat/' + state.conversationSlug + '/typing', {
            method: 'GET',
            credentials: 'include',
            cache: 'no-cache',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateTypingIndicatorUI(data.typing_users, data.typing_count);
            }
        })
        .catch(err => console.error('RealTime: Typing status error:', err));
    }

    function updateTypingIndicatorUI(typingUsers, typingCount) {
        const typingIndicator = document.getElementById('typingIndicator');
        const typingInline = document.querySelector('.typing-indicator-inline');
        
        let typingText = '';
        
        if (typingCount > 0) {
            if (typingCount === 1) {
                typingText = typingUsers[0].username + ' ' + (window.chatTranslations?.typing || 'typing...');
            } else if (typingCount === 2) {
                typingText = typingUsers[0].username + ' ' + (window.chatTranslations?.and || 'and') + ' ' + typingUsers[1].username + ' ' + (window.chatTranslations?.are_typing || 'are typing...');
            } else {
                typingText = typingCount + ' ' + (window.chatTranslations?.users_typing || 'users are typing...');
            }
        }

        // Update main typing indicator in chat room
        if (typingIndicator) {
            if (typingCount > 0) {
                typingIndicator.style.display = 'block';
                const typingTextEl = typingIndicator.querySelector('.typing-text');
                if (typingTextEl) {
                    typingTextEl.textContent = typingText;
                }
            } else {
                typingIndicator.style.display = 'none';
            }
        }
        
        // Update inline typing indicator in sidebar (chat list)
        if (typingInline && state.conversationSlug) {
            const slug = typingInline.dataset.conversationSlug;
            if (slug === state.conversationSlug) {
                if (typingCount > 0) {
                    typingInline.textContent = typingText;
                    typingInline.style.display = 'inline';
                } else {
                    typingInline.style.display = 'none';
                }
            }
        }
    }

    function startChatUserStatusPolling() {
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkCurrentChatUserOnlineStatus();
            }
        }, 3000);
    }

    function checkCurrentChatUserOnlineStatus() {
        if (!window.currentChatUserId || window.conversationIsGroup) {
            console.log('RealTime: Skipping online status check (no user or group chat)', {
                currentChatUserId: window.currentChatUserId,
                conversationIsGroup: window.conversationIsGroup
            });
            return;
        }
        
        const url = '/user/' + window.currentChatUserId + '/online-status?t=' + Date.now();
        console.log('RealTime: Checking online status:', url, 'for user:', window.currentChatUserId);
        
        fetch(url, {
            method: 'GET',
            credentials: 'include',
            cache: 'no-cache',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(r => {
            console.log('RealTime: Online status HTTP status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('RealTime: Online status response:', data);
            if (data.success) {
                updateOnlineStatusUI(window.currentChatUserId, data.is_online, data.last_active);
            } else {
                console.log('RealTime: Online status response not successful');
            }
        })
        .catch(err => console.error('RealTime: Online status error:', err));
    }

    function updateOnlineStatusUI(userId, isOnline, lastActive) {
        // Find the status element - it's in the chat header
        const statusEl = document.querySelector('.status[data-user-id="' + userId + '"]');
        console.log('RealTime: Updating online status UI for user', userId, 'isOnline:', isOnline, 'element:', statusEl);
        
        if (!statusEl) {
            console.log('RealTime: Status element not found for user', userId);
            return;
        }

        const statusText = statusEl.querySelector('.status-text');

        if (isOnline) {
            // Add 'online' class to parent status element for CSS styling
            statusEl.classList.add('online');
            if (statusText) {
                statusText.textContent = window.chatTranslations?.online || 'Online';
                statusText.style.color = '#25d366';
            }
            console.log('RealTime: User is ONLINE');
        } else {
            // Remove 'online' class
            statusEl.classList.remove('online');
            if (lastActive && window.chatTranslations?.last_active) {
                statusText.textContent = window.chatTranslations.last_active + ': ' + formatLastActive(lastActive);
                statusText.style.color = '';
            } else if (statusText) {
                statusText.textContent = window.chatTranslations?.offline || 'Offline';
                statusText.style.color = '';
            }
            console.log('RealTime: User is OFFLINE');
        }
    }

    function formatLastActive(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
        return date.toLocaleDateString();
    }

    function checkForNewMessages() {
        if (!state.conversationSlug) {
            console.log('RealTime: No conversation slug, skipping message check');
            return;
        }

        // Prevent polling too frequently (minimum 300ms between polls)
        const now = Date.now();
        if (now - state.lastPollTime < 300) {
            return;
        }
        state.lastPollTime = now;

        // Always fetch recent messages to catch any missed ones
        let url = '/chat/' + state.conversationSlug + '/messages?t=' + Date.now();

        // Use lastMessageId to get only new messages, but also fetch last 50 to catch any missed
        if (state.lastMessageId) {
            url += '&after_id=' + state.lastMessageId + '&limit=50';
        } else {
            url += '&limit=20'; // Get last 20 messages if no lastMessageId
        }

        console.log('RealTime: Checking for new messages:', url, 'lastMessageId:', state.lastMessageId);

        fetch(url, {
            method: 'GET',
            credentials: 'include',
            cache: 'no-cache',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(r => {
            console.log('RealTime: Messages response status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('RealTime: Messages response data:', data);

            if (!data.success) {
                console.log('RealTime: Message fetch not successful');
                return;
            }

            // Check for notification of deleted messages in recent_messages
            if (data.recent_messages && Array.isArray(data.recent_messages)) {
                data.recent_messages.forEach(message => {
                    const shouldShowDeleted = Boolean(message.deleted_by_sender || message.deleted_at);
                    const isHiddenForCurrent = Array.isArray(message.deleted_for) && message.deleted_for.includes(window.RealTimeConfig.userId);

                    // Update read receipt icon for own messages when the status changes.
                    if (message.sender_id === window.RealTimeConfig.userId && message.read_at) {
                        updateMessageStatusUI(message.id, message.read_at);
                    }

                    if (message.id && shouldShowDeleted) {
                        if (isHiddenForCurrent) {
                            const msgEl = document.querySelector('.message[data-message-id="' + message.id + '"]');
                            if (msgEl) {
                                msgEl.remove();
                            }
                        } else {
                            // Mark as deleted for everyone or read-only deletion
                            if (typeof window.handleDeleteMessage === 'function') {
                                window.handleDeleteMessage(message.id, 'everyone', message.deleted_for || []);
                            }
                        }
                    }
                });
            }

            // Check for new_messages (Laravel API response)
            if (data.new_messages && data.new_messages.length > 0) {
                console.log('RealTime: Found', data.new_messages.length, 'new messages');

                // Get ALL existing message IDs from DOM using Set for faster lookup
                const existingMessageIds = new Set(
                    Array.from(document.querySelectorAll('[data-message-id]'))
                        .map(el => parseInt(el.dataset.messageId))
                        .filter(id => !isNaN(id))
                );

                let hasUnreadFromOther = false;
                let messagesAdded = 0;
                let messagesSkipped = 0;

                data.new_messages.forEach(message => {
                    const messageId = message.id;

                    // Skip if already in DOM
                    if (existingMessageIds.has(messageId)) {
                        messagesSkipped++;
                        // Remove from pending if it was there
                        state.pendingMessageIds.delete(messageId);
                        return;
                    }

                    // CRITICAL: Always try to add message, even if addMessage might not exist
                    if (typeof window.addMessage === 'function') {
                        console.log('RealTime: Adding new message:', messageId, 'from', message.sender_id);
                        const msgObj = {
                            id: messageId,
                            content: message.content,
                            created_at: message.created_at,
                            type: message.type,
                            media_path: message.media_path,
                            sender_id: message.sender_id,
                            sender: {
                                username: message.sender?.username || message.sender?.name,
                                avatar_url: message.sender?.avatar_url
                            },
                            read_at: message.read_at
                        };

                        try {
                            window.addMessage(msgObj);
                            messagesAdded++;
                            console.log('RealTime: Successfully added message', messageId);
                            // Remove from pending if it was there
                            state.pendingMessageIds.delete(messageId);
                        } catch (err) {
                            console.error('RealTime: Error adding message', messageId, err);
                            // Keep in pending for retry
                        }

                        if (window.RealTime && typeof window.RealTime.updateSidebarConversation === 'function') {
                            window.RealTime.updateSidebarConversation(msgObj);
                        }

                        if (msgObj.sender_id != window.RealTimeConfig.userId) {
                            hasUnreadFromOther = true;
                        }
                    } else {
                        console.error('RealTime: window.addMessage function not found!');
                        // Fallback: reload the page to get new messages
                        console.log('RealTime: Will reload page to sync messages');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }

                    // Always update lastMessageId to the highest ID received
                    if (messageId && Number.isFinite(messageId)) {
                        state.lastMessageId = Math.max(state.lastMessageId || 0, Number(messageId));
                        if (typeof myLastMessageId !== 'undefined') {
                            myLastMessageId = Math.max(myLastMessageId || 0, Number(messageId));
                        }
                    }
                });

                console.log('RealTime: Added', messagesAdded, 'new messages,', messagesSkipped, 'already existed');

                if (hasUnreadFromOther) {
                    console.log('RealTime: Marking incoming messages as read');
                    markMessagesAsRead();
                }
            } else {
                console.log('RealTime: No new messages');
            }

            // Sync lastMessageId from DOM as fallback
            const domMaxId = Array.from(document.querySelectorAll('[data-message-id]'))
                .map(el => parseInt(el.dataset.messageId))
                .filter(id => !isNaN(id))
                .reduce((max, id) => Math.max(max, id), 0);
            if (domMaxId > (state.lastMessageId || 0)) {
                console.log('RealTime: Syncing lastMessageId from DOM fallback:', domMaxId);
                state.lastMessageId = domMaxId;
                myLastMessageId = domMaxId;
            }
        })
        .catch(err => console.error('RealTime: Polling error:', err));
    }

    function startReadReceiptPolling() {
        if (!state.conversationSlug) return;

        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                markMessagesAsRead();
            }
        }, 2000);
    }

    function startMessageStatusPolling() {
        if (!state.conversationSlug) return;

        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkMessageStatuses();
            }
        }, 3000);
    }

    function checkMessageStatuses() {
        // Get all message IDs sent by current user
        const sentMessageIds = Array.from(document.querySelectorAll('.message.own[data-message-id]'))
            .map(el => parseInt(el.dataset.messageId))
            .filter(id => !isNaN(id));

        if (sentMessageIds.length === 0) return;

        fetch('/chat/' + state.conversationSlug + '/status', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message_ids: sentMessageIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.statuses) {
                data.statuses.forEach(status => {
                    updateMessageStatusUI(status.id, status.read_at);
                });
            }
        })
        .catch(err => console.error('Message status check error:', err));
    }

    function updateMessageStatusUI(messageId, readAt) {
        const msgEl = document.querySelector('.message[data-message-id="' + messageId + '"]');
        if (!msgEl) return;

        // Only update read receipts for messages sent by current user
        if (!msgEl.classList.contains('own')) return;

        const timeEl = msgEl.querySelector('.message-time');
        if (!timeEl) return;

        const checkIcon = timeEl.querySelector('.fa-check, .fa-check-double');

        if (readAt && (!checkIcon || checkIcon.classList.contains('fa-check'))) {
            // Message has been read - show double blue check
            if (checkIcon) {
                checkIcon.classList.remove('fa-check');
                checkIcon.classList.add('fa-check-double', 'read');
            } else {
                // No icon exists, add one
                const icon = document.createElement('i');
                icon.className = 'fas fa-check-double read';
                icon.title = 'Read';
                timeEl.appendChild(icon);
            }
            console.log('RealTime: Message', messageId, 'was read');
        }
    }

    function markMessagesAsRead() {
        if (!state.conversationSlug) {
            console.log('RealTime: No conversation slug, skipping mark as read');
            return;
        }

        const url = '/chat/' + state.conversationSlug + '/read';
        console.log('RealTime: Marking messages as read:', url);

        fetch(url, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => {
            console.log('RealTime: Mark read response status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('RealTime: Mark read response:', data);
            if (data.success) {
                if (data.read_message_ids && data.read_message_ids.length > 0) {
                    console.log('RealTime: Updating read receipts for', data.read_message_ids.length, 'messages');
                }
                updateReadReceiptsUI(data.read_message_ids);
            }
        })
        .catch(err => console.error('RealTime: Mark read error:', err));
    }

    function updateReadReceiptsUI(readMessageIds) {
        if (!readMessageIds || !Array.isArray(readMessageIds)) return;

        readMessageIds.forEach(id => {
            const msgEl = document.querySelector('.message[data-message-id="' + id + '"]');
            if (msgEl) {
                const timeEl = msgEl.querySelector('.message-time');
                if (timeEl && !timeEl.querySelector('.fa-check-double')) {
                    const checkIcon = timeEl.querySelector('.fa-check');
                    if (checkIcon) {
                        checkIcon.classList.remove('fa-check');
                        checkIcon.classList.add('fa-check-double', 'read');
                    }
                }
            }
        });
    }

    function startAccountStatusCheck() {
        state.accountStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkAccountStatus();
            }
        }, window.RealTimeConfig.accountStatusInterval);
    }

    function checkAccountStatus() {
        // Check if user account is still active
        fetch('/user/check-account-status', {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => {
            if (!r.ok) {
                window.RealTimeConfig.active = false;
            }
        })
        .catch(() => {
            window.RealTimeConfig.active = false;
        });
    }

    function startNotificationsPolling() {
        state.notificationsTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkNotifications();
            }
        }, window.RealTimeConfig.notificationsInterval);
    }

    function checkNotifications() {
        const url = '/api/notifications?limit=50&t=' + Date.now();
        console.log('RealTime: Checking notifications:', url);
        
        fetch(url, {
            credentials: 'include',
            cache: 'no-cache',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(r => {
            console.log('RealTime: Notifications HTTP status:', r.status);
            if (r.status === 401) {
                console.log('RealTime: Not authenticated for notifications');
                return { success: false, notifications: [], unread_count: 0 };
            }
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.json();
        })
        .then(data => {
            console.log('RealTime: Notifications data:', data);
            if (data.success && data.notifications) {
                // Update notification badge
                const unreadCount = data.unread_count || 0;
                console.log('RealTime: Unread count:', unreadCount);
                updateNotificationBadge(unreadCount);
                
                // Update notification list UI if function exists
                if (typeof window.loadNotifications === 'function') {
                    console.log('RealTime: Calling loadNotifications()');
                    window.loadNotifications();
                } else {
                    console.log('RealTime: loadNotifications function not found');
                }
                
                // Show browser notification for new unread notifications
                const newNotifications = data.notifications.filter(n => !n.read_at);
                if (newNotifications.length > 0 && newNotifications.length <= 5) {
                    console.log('RealTime: Showing browser notifications for', newNotifications.length, 'items');
                    showBrowserNotification(newNotifications);
                }
            } else {
                console.log('RealTime: Notifications response not successful');
            }
        })
        .catch(err => console.error('RealTime: Notifications error:', err));
    }

    function showBrowserNotification(notifications) {
        // Request permission if not granted
        if (!('Notification' in window)) {
            console.log('RealTime: Browser does not support notifications');
            return;
        }

        if (Notification.permission === 'granted') {
            notifications.forEach(notif => {
                // Don't notify for message notifications when viewing that conversation
                if (notif.type === 'message' && state.activeConversationId) {
                    const notifConvId = notif.data?.conversation_id;
                    if (notifConvId == state.activeConversationId) return;
                }

                const title = getNotificationTitle(notif);
                const body = getNotificationBody(notif);
                const icon = notif.data?.from_user?.avatar_url || '/favicon.ico';

                new Notification(title, {
                    body: body,
                    icon: icon,
                    tag: 'notif-' + notif.id,
                    requireInteraction: false
                });
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                console.log('RealTime: Notification permission:', permission);
            });
        }
    }

    function getNotificationTitle(notif) {
        switch(notif.type) {
            case 'like': return 'New Like';
            case 'comment': return 'New Comment';
            case 'follow': return 'New Follower';
            case 'mention': return 'Mentioned You';
            case 'message': return 'New Message';
            default: return 'New Notification';
        }
    }

    function getNotificationBody(notif) {
        if (notif.data?.from_user?.username) {
            const username = notif.data.from_user.username;
            switch(notif.type) {
                case 'like': return username + ' liked your post';
                case 'comment': return username + ' commented on your post';
                case 'follow': return username + ' started following you';
                case 'mention': return username + ' mentioned you';
                case 'message': return notif.data?.message_preview || 'New message from ' + username;
                default: return notif.message || 'New notification';
            }
        }
        return notif.message || 'New notification';
    }

    function updateNotificationBadge(count) {
        // Update badge in UI if it exists
        const badgeEl = document.querySelector('.notification-badge, .notif-badge');
        if (badgeEl) {
            if (count > 0) {
                badgeEl.textContent = count > 99 ? '99+' : count;
                badgeEl.style.display = 'inline-block';
            } else {
                badgeEl.style.display = 'none';
            }
        }

        // Update favicon badge (browser tab)
        if (count > 0) {
            document.title = '(' + count + ') ' + document.title.replace(/^\(\d+\)\s/, '');
        } else {
            document.title = document.title.replace(/^\(\d+\)\s/, '');
        }
    }

    function startOnlineStatusPing() {
        state.onlineStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                pingOnlineStatus();
            }
        }, window.RealTimeConfig.onlineStatusInterval);
    }

    function pingOnlineStatus() {
        fetch('/user/online-status', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .catch(err => console.error('Ping error:', err));
    }

    function refreshAllOnlineStatuses() {
        // Refresh online status for all users in conversation list
        const indicators = document.querySelectorAll('.online-indicator[data-user-id]');
        const userIds = Array.from(indicators).map(el => el.dataset.userId);

        if (userIds.length === 0) return;

        fetch('/user/online-status/batch', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.statuses) {
                Object.keys(data.statuses).forEach(uid => {
                    const status = data.statuses[uid];
                    const indicator = document.querySelector('.online-indicator[data-user-id="' + uid + '"]');
                    if (indicator) {
                        if (status.is_online) {
                            indicator.classList.add('online');
                        } else {
                            indicator.classList.remove('online');
                        }
                    }
                });
            }
        })
        .catch(err => console.error('Batch status error:', err));
    }

    function initTypingIndicator() {
        // Typing indicator implementation
        const messageInput = document.getElementById('messageInput');
        if (!messageInput || !state.conversationSlug) return;

        let typingTimeout;
        let isTyping = false;
        let lastTypingSent = 0;

        messageInput.addEventListener('input', () => {
            const now = Date.now();
            
            // Send typing indicator immediately when user starts typing
            if (!isTyping) {
                isTyping = true;
                sendTypingIndicator(true);
                lastTypingSent = now;
            }

            // Clear existing timeout
            clearTimeout(typingTimeout);
            
            // Stop typing after 1 second of inactivity (faster response)
            typingTimeout = setTimeout(() => {
                isTyping = false;
                sendTypingIndicator(false);
            }, 1000);
        });

        // Also stop typing when user leaves the input
        messageInput.addEventListener('blur', () => {
            if (isTyping) {
                isTyping = false;
                sendTypingIndicator(false);
                clearTimeout(typingTimeout);
            }
        });
    }

    function sendTypingIndicator(isTyping) {
        if (!state.conversationSlug) return;

        fetch('/chat/' + state.conversationSlug + '/typing', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ is_typing: isTyping })
        })
        .catch(err => console.error('Typing indicator error:', err));
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // Expose functions globally for use in other scripts
    window.RealTime = {
        markMessagesAsRead: markMessagesAsRead,
        updateSidebarConversation: updateSidebarConversation,
        refreshChatList: refreshChatList,
        checkForNewMessages: checkForNewMessages,
        updateLastMessageId: updateLastMessageId
    };

    // Update lastMessageId from sendMessage to sync with polling
    function updateLastMessageId(messageId) {
        if (messageId && Number.isFinite(messageId)) {
            const newId = Number(messageId);
            state.lastMessageId = Math.max(state.lastMessageId || 0, newId);
            console.log('RealTime: Updated lastMessageId to', state.lastMessageId);
        }
    }

    function updateSidebarConversation(message) {
        // Update conversation list with latest message
        const convHref = '/chat/' + (state.conversationSlug || '');
        const convItem = document.querySelector('.conversation-item[href="' + convHref + '"]');
        if (!convItem) return;

        const preview = convItem.querySelector('.conv-preview');
        if (preview && message.content) {
            const isOwn = message.sender_id == window.RealTimeConfig.userId;
            const t = window.chatTranslations || {};

            let messagePreview = message.content;
            if (message.type === 'image') {
                messagePreview = isOwn ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo');
            } else if (message.type === 'video') {
                messagePreview = isOwn ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video');
            }

            if (isOwn) {
                messagePreview = (t.you || 'You') + ': ' + messagePreview;
            }

            let statusIcon = isOwn ? '<i class="fas fa-check read-status sent"></i> ' : '';
            preview.innerHTML = statusIcon + '<span class="preview-text">' + escapeHtml(messagePreview) + '</span>';
        }

        const timeEl = convItem.querySelector('.conv-time');
        if (timeEl && message.created_at) {
            timeEl.textContent = formatMessageTime(message.created_at);
        }

        // Move conversation to top
        const convList = convItem.parentElement;
        if (convList) {
            convList.insertBefore(convItem, convList.firstChild);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
