/* Realtime - Chat and Notifications Polling */

(function() {
    'use strict';

    window.RealTimeConfig = {
        chatListInterval: 2000,
        chatRoomInterval: 1000,
        accountStatusInterval: 10000,
        notificationsInterval: 3000,
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
        deletedMessageIds: new Set()
    };

    function init() {
        if (!window.RealTimeConfig.userId) {
            console.warn('RealTime: No userId configured');
            return;
        }

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('beforeunload', cleanup);

        const path = window.location.pathname;

        if (path === '/chat' || path === '/chat/') {
            startChatListPolling();
        } else if (path.startsWith('/chat/') && path !== '/chat/') {
            state.conversationSlug = path.split('/').pop();
            const lastMessageEl = document.querySelector('#chatMessages .message:last-child');
            if (lastMessageEl) {
                state.lastMessageId = parseInt(lastMessageEl.dataset.messageId) || 0;
            }
            state.deletedMessageIds.clear();
            if (window.activeConversationId) {
                state.activeConversationId = window.activeConversationId;
            }
            startChatRoomPolling();
            startChatListPolling();
            initTypingIndicator();
        }

        startAccountStatusCheck();
        startNotificationsPolling();
        startOnlineStatusPing();
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

        setTimeout(() => {
            refreshChatList();
            setTimeout(() => refreshChatList(), 500);
        }, 100);

        state.chatListTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshChatList();
            }
        }, window.RealTimeConfig.chatListInterval);

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
        fetch('/chat/conversations?t=' + Date.now(), {
            credentials: 'include',
            cache: 'no-cache',
            headers: {
                'X-CSRF-TOKEN': window.getCsrfToken ? window.getCsrfToken() : '',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.conversations) {
                updateChatListUI(data.conversations);
                refreshAllOnlineStatuses();
            }
        })
        .catch(err => console.error('Chat list refresh error:', err));
    }

    function updateChatListUI(conversations) {
        let conversationsList = document.getElementById('sidebarConvList') || document.getElementById('conversationsList');
        if (!conversationsList) return;

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
                createConversationItem(conv, conversationsList, conversations);
                return;
            }

            updateExistingConversationItem(item, conv, idx, TYPING_CHECK_LIMIT);
        });

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

            let messageIcon = '', messagePreview = '';
            switch (msgType) {
                case 'image': messageIcon = '📷 '; messagePreview = isMyMessage ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo'); break;
                case 'video': messageIcon = '🎥 '; messagePreview = isMyMessage ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video'); break;
                case 'audio': messageIcon = '🎤 '; messagePreview = isMyMessage ? (t.you_sent_audio || 'You sent an audio') : (t.sent_audio || 'Sent an audio'); break;
                case 'document': messageIcon = '📎 '; messagePreview = isMyMessage ? (t.you_sent_document || 'You sent a document') : (t.sent_document || 'Sent a document'); break;
                case 'gif': messageIcon = 'GIF '; messagePreview = isMyMessage ? (t.you_sent_gif || 'You sent a GIF') : (t.sent_gif || 'Sent a GIF'); break;
                case 'sticker': messageIcon = '⭐ '; messagePreview = isMyMessage ? (t.you_sent_sticker || 'You sent a sticker') : (t.sent_sticker || 'Sent a sticker'); break;
                case 'story_reply': messageIcon = '📸 '; messagePreview = isMyMessage ? (t.you_replied_to_story || 'You replied to story') : (t.replied_to_story || 'Replied to your story'); break;
                default: messagePreview = msg.content || '';
            }

            if (isMyMessage && msgType !== 'story_reply') {
                messagePreview = (t.you || 'You') + ': ' + messagePreview;
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

        const itemHtml = '<a href="/chat/' + conv.slug + '" class="conversation-item ' + (conv.unread_count > 0 ? 'unread' : '') + '" data-name="' + escapeHtml(displayName) + '">' +
            '<div class="conv-avatar">' + avatarHtml + onlineIndicatorHtml + '</div>' +
            '<div class="conv-content">' +
            '<div class="conv-header"><div class="conv-title-container"><div class="conv-title">' + escapeHtml(displayName) + '</div><span class="conv-time">' + timeHtml + '</span></div></div>' +
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

            let messageIcon = '', messagePreview = '';
            switch (msgType) {
                case 'image': messageIcon = '📷 '; messagePreview = isMyMessage ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo'); break;
                case 'video': messageIcon = '🎥 '; messagePreview = isMyMessage ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video'); break;
                case 'audio': messageIcon = '🎤 '; messagePreview = isMyMessage ? (t.you_sent_audio || 'You sent an audio') : (t.sent_audio || 'Sent an audio'); break;
                case 'document': messageIcon = '📎 '; messagePreview = isMyMessage ? (t.you_sent_document || 'You sent a document') : (t.sent_document || 'Sent a document'); break;
                case 'gif': messageIcon = 'GIF '; messagePreview = isMyMessage ? (t.you_sent_gif || 'You sent a GIF') : (t.sent_gif || 'Sent a GIF'); break;
                case 'sticker': messageIcon = '⭐ '; messagePreview = isMyMessage ? (t.you_sent_sticker || 'You sent a sticker') : (t.sent_sticker || 'Sent a sticker'); break;
                case 'story_reply': messageIcon = '📸 '; messagePreview = isMyMessage ? (t.you_replied_to_story || 'You replied to story') : (t.replied_to_story || 'Replied to your story'); break;
                default: messagePreview = content;
            }

            if (isMyMessage && msgType !== 'story_reply') {
                messagePreview = (t.you || 'You') + ': ' + messagePreview;
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
        state.chatRoomTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkForNewMessages();
            }
        }, window.RealTimeConfig.chatRoomInterval);
        startChatUserStatusPolling();
        startReadReceiptPolling();
    }

    function startChatUserStatusPolling() {
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkCurrentChatUserOnlineStatus();
            }
        }, 10000);
    }

    function checkCurrentChatUserOnlineStatus() {
        // Implementation for checking online status
    }

    function checkForNewMessages() {
        // Implementation for checking new messages
    }

    function startReadReceiptPolling() {
        // Implementation for read receipts
    }

    function startAccountStatusCheck() {
        state.accountStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkAccountStatus();
            }
        }, window.RealTimeConfig.accountStatusInterval);
    }

    function checkAccountStatus() {
        // Implementation for account status check
    }

    function startNotificationsPolling() {
        state.notificationsTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkNotifications();
            }
        }, window.RealTimeConfig.notificationsInterval);
    }

    function checkNotifications() {
        // Implementation for notifications check
    }

    function startOnlineStatusPing() {
        state.onlineStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                pingOnlineStatus();
            }
        }, window.RealTimeConfig.onlineStatusInterval);
    }

    function pingOnlineStatus() {
        // Implementation for online status ping
    }

    function refreshAllOnlineStatuses() {
        // Implementation for refreshing online statuses
    }

    function initTypingIndicator() {
        // Implementation for typing indicator
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
