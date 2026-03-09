/**
 * Real-Time AJAX Polling
 * Handles all real-time updates via polling
 */

(function() {
    'use strict';

    // Configuration
    window.RealTimeConfig = {
        chatListInterval: 1000,      // Poll chat list every 1 seconds
        chatRoomInterval: 1000,      // Poll chat room every 1 seconds
        accountStatusInterval: 10000, // Check account status every 10 seconds
        notificationsInterval: 2000,  // Poll notifications every 2 seconds
        onlineStatusInterval: 10000,  // Ping online status every 10 seconds
        userId: window.currentUserId || null,
        active: true
    };

    // State
    const state = {
        chatListTimer: null,
        chatRoomTimer: null,
        accountStatusTimer: null,
        notificationsTimer: null,
        onlineStatusTimer: null,
        readReceiptTimer: null,  // Track read receipt polling
        lastMessageId: 0,
        conversationSlug: null,
        activeConversationId: null,  // Track active conversation ID
        isVisible: true,
        onlineUserIds: new Set(),
        deletedMessageIds: new Set()  // Track deleted message IDs
    };

    // Initialize
    function init() {
        
        if (!window.RealTimeConfig.userId) {
            console.warn('RealTime: No userId configured');
            return;
        }

        

        // Track page visibility
        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('beforeunload', cleanup);

        // Get current page context
        const path = window.location.pathname;

        // Start appropriate polling based on page
        if (path === '/chat' || path === '/chat/') {
            startChatListPolling();
        } else if (path.startsWith('/chat/') && path !== '/chat/') {
            state.conversationSlug = path.split('/').pop();

            // Initialize lastMessageId from the last message in the DOM
            const lastMessageEl = document.querySelector('#chatMessages .message:last-child');
            if (lastMessageEl) {
                state.lastMessageId = parseInt(lastMessageEl.dataset.messageId) || 0;
            }

            // Reset deleted messages tracking for new conversation
            state.deletedMessageIds.clear();

            // Get active conversation ID from window (set in chat show view)
            if (window.activeConversationId) {
                state.activeConversationId = window.activeConversationId;
            }

            startChatRoomPolling();
            startChatListPolling(); // Also update chat list

            // Initialize typing indicator for chat pages
            initTypingIndicator();
        } else {
        }

        // Start account status check for security monitoring
        startAccountStatusCheck();

        // Start notifications polling (works on all pages)
        startNotificationsPolling();

        // Start online status ping (works on all pages)
        startOnlineStatusPing();
    }

    /**
     * Chat List Polling
     */
    function startChatListPolling() {
        if (state.chatListTimer) {
            return;
        }

        // Initial load
        refreshChatList();

        // Start polling for chat list
        state.chatListTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshChatList();
            }
        }, window.RealTimeConfig.chatListInterval);

        // Start separate polling for online statuses (every 10 seconds)
        startChatListOnlineStatusPolling();
    }

    /**
     * Poll online status for all users in chat list
     */
    function startChatListOnlineStatusPolling() {
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshAllOnlineStatuses();
            }
        }, 10000); // 10 seconds
    }

    function refreshChatList() {
        fetch('/chat/conversations', {
            credentials: 'include',
            cache: 'no-store', // ensure we don't get a cached response in some browsers
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.conversations) {
                updateChatListUI(data.conversations);
                // Refresh online statuses for all users in chat list
                refreshAllOnlineStatuses();
            }
        })
        .catch(err => console.error('Chat list refresh error:', err));
    }

    function updateChatListUI(conversations) {

        // Support both ID naming conventions
        const conversationsList = document.getElementById('sidebarConvList') || document.getElementById('conversationsList');
        if (!conversationsList) return;

        // Limit typing checks to the top N conversations to avoid flooding
        const TYPING_CHECK_LIMIT = 25;
        let idx = 0;

        // Track which conversation IDs we've seen
        const seenConvIds = new Set();

        conversations.forEach(conv => {
            idx++;
            seenConvIds.add(conv.id);

            // Try to find by conversation ID or slug
            let item = document.querySelector(`.conversation-item[href*="/chat/${conv.id}"]`);
            if (!item && conv.slug) {
                item = document.querySelector(`.conversation-item[href*="/chat/${conv.slug}"]`);
            }

            // If conversation doesn't exist in DOM, create it
            if (!item) {
                createConversationItem(conv, conversationsList, conversations);
                return;
            }

            // Update existing conversation item
            updateExistingConversationItem(item, conv, idx, TYPING_CHECK_LIMIT);
        });

        // Re-sort the entire list based on last_message_at (WhatsApp style)
        const items = Array.from(conversationsList.querySelectorAll('.conversation-item'));
        items.sort((a, b) => {
            const aHref = a.getAttribute('href') || '';
            const bHref = b.getAttribute('href') || '';

            // Find corresponding conversation data
            const aConv = conversations.find(c =>
                `/chat/${c.id}` === aHref || `/chat/${c.slug}` === aHref
            );
            const bConv = conversations.find(c =>
                `/chat/${c.id}` === bHref || `/chat/${c.slug}` === bHref
            );

            if (!aConv || !bConv) return 0;

            // Chats with unread messages should always bubble to the top
            const aUnread = aConv.unread_count > 0;
            const bUnread = bConv.unread_count > 0;
            if (aUnread !== bUnread) {
                return bUnread ? 1 : -1; // put the one with unread first
            }

            // Sort by last_message_at (newest first, NULL last)
            const aTime = aConv.last_message_at ? new Date(aConv.last_message_at).getTime() : 0;
            const bTime = bConv.last_message_at ? new Date(bConv.last_message_at).getTime() : 0;

            return bTime - aTime;
        });

        // Reorder DOM elements to match sorted order
        items.forEach(item => conversationsList.appendChild(item));

        // Remove conversations that no longer exist (optional - can be enabled if needed)
        // For now, we keep them to avoid flickering
    }

    function createConversationItem(conv, conversationsList, allConversations) {
        const isGroup = conv.is_group || false;
        const displayName = isGroup ? (conv.name || 'Group') : (conv.other_user?.username || 'User');
        const avatarUrl = isGroup 
            ? (conv.avatar || null) 
            : (conv.other_user?.avatar_url || null);
        
        // Build avatar HTML
        let avatarHtml = '';
        if (avatarUrl) {
            avatarHtml = `<img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(displayName)}">`;
        } else if (isGroup) {
            avatarHtml = `<div class="avatar-fallback group"><i class="fas fa-users"></i></div>`;
        } else {
            avatarHtml = `<div class="avatar-fallback">${escapeHtml(displayName.substring(0, 1).toUpperCase())}</div>`;
        }

        // Build online indicator for non-group chats
        let onlineIndicatorHtml = '';
        if (!isGroup && conv.other_user) {
            const isOnline = conv.other_user.is_online && conv.other_user.last_active &&
                (new Date().getTime() - new Date(conv.other_user.last_active).getTime()) < 120000;
            onlineIndicatorHtml = `<span class="online-indicator ${isOnline ? 'online' : ''}" data-user-id="${conv.other_user.id}"></span>`;
        }

        // Build message preview
        let previewHtml = '';
        let previewClass = conv.unread_count > 0 ? 'unread-text' : '';
        if (conv.latest_message) {
            const msg = conv.latest_message;
            const isMyMessage = msg.sender_id == window.RealTimeConfig.userId;
            const msgType = msg.type || 'text';

            let messageIcon = '';
            let messagePreview = '';
            const t = window.chatTranslations || {};

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
                statusIcon = msg.read_at
                    ? '<i class="fas fa-check-double read-status read"></i> '
                    : '<i class="fas fa-check read-status sent"></i> ';
            }

            previewHtml = `${statusIcon}<span class="preview-text ${previewClass}">${escapeHtml(messageIcon + messagePreview)}</span>`;
        } else {
            previewHtml = `<span class="preview-text">${t.start_a_conversation || 'Start a conversation'}</span>`;
        }

        // Build unread badge
        let unreadBadgeHtml = '';
        let unreadClass = conv.unread_count > 0 ? 'unread' : '';
        if (conv.unread_count > 0) {
            const badgeCount = conv.unread_count > 99 ? '99+' : conv.unread_count;
            unreadBadgeHtml = `<span class="unread-pill" style="display: inline-block;">${badgeCount}</span>`;
        }

        // Build timestamp
        const timeHtml = conv.last_message_at ? formatMessageTime(conv.last_message_at) : '';

        // Build online status text for non-group chats
        let onlineStatusHtml = '';
        if (!isGroup && conv.other_user) {
            const t = window.chatTranslations || {};
            const isOnline = conv.other_user.is_online && conv.other_user.last_active &&
                (new Date().getTime() - new Date(conv.other_user.last_active).getTime()) < 120000;
            onlineStatusHtml = `<span class="online-status-text ${isOnline ? 'online' : 'offline'}" data-user-id="${conv.other_user.id}">${isOnline ? '• ' + (t.online || 'Online') : ''}</span>`;
        }

        // Create the conversation item HTML
        const itemHtml = `
            <a href="/chat/${conv.slug}" class="conversation-item ${unreadClass}" data-name="${escapeHtml(displayName)}" data-user-id="${isGroup ? '' : (conv.other_user?.id || '')}" data-conversation-slug="${conv.slug}">
                <div class="conv-avatar">
                    ${avatarHtml}
                    ${onlineIndicatorHtml}
                </div>
                <div class="conv-content">
                    <div class="conv-header">
                        <div class="conv-title-container">
                            <span class="conv-title">
                                ${escapeHtml(displayName)}
                                ${onlineStatusHtml}
                            </span>
                            <span class="conv-time">${timeHtml}</span>
                        </div>
                    </div>
                    <div class="conv-footer">
                        <p class="conv-preview ${previewClass}">${previewHtml}</p>
                        ${unreadBadgeHtml}
                    </div>
                </div>
            </a>
        `;

        // Create temporary element to parse HTML
        const temp = document.createElement('div');
        temp.innerHTML = itemHtml.trim();
        const newItem = temp.firstElementChild;

        // Hide "No messages yet" empty state if it exists
        const emptyState = conversationsList.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        // Insert at the TOP of the list for new conversations
        const firstItem = conversationsList.querySelector('.conversation-item');
        if (firstItem) {
            conversationsList.insertBefore(newItem, firstItem);
        } else {
            conversationsList.appendChild(newItem);
        }

        // Note: Notification sound can be added here if needed
        // For new conversations with unread messages from others
    }

    function updateExistingConversationItem(item, conv, idx, TYPING_CHECK_LIMIT) {
        // Update preview with sender indicator and message type
        const preview = item.querySelector('.conv-preview');
        if (preview && conv.latest_message) {
                const msgType = conv.latest_message.type || 'text';
                const content = conv.latest_message.content || '';
                const isMyMessage = conv.latest_message.sender_id == window.RealTimeConfig.userId;
                const isRead = conv.latest_message.read_at;

                let messageIcon = '';
                let messagePreview = '';
                const t = window.chatTranslations || {};

                // Handle different message types
                switch (msgType) {
                    case 'image':
                        messageIcon = '📷 ';
                        messagePreview = isMyMessage ? (t.you_sent_photo || 'You sent a photo') : (t.sent_photo || 'Sent a photo');
                        break;
                    case 'video':
                        messageIcon = '🎥 ';
                        messagePreview = isMyMessage ? (t.you_sent_video || 'You sent a video') : (t.sent_video || 'Sent a video');
                        break;
                    case 'audio':
                        messageIcon = '🎤 ';
                        messagePreview = isMyMessage ? (t.you_sent_audio || 'You sent an audio') : (t.sent_audio || 'Sent an audio');
                        break;
                    case 'document':
                        messageIcon = '📎 ';
                        messagePreview = isMyMessage ? (t.you_sent_document || 'You sent a document') : (t.sent_document || 'Sent a document');
                        break;
                    case 'gif':
                        messageIcon = 'GIF ';
                        messagePreview = isMyMessage ? (t.you_sent_gif || 'You sent a GIF') : (t.sent_gif || 'Sent a GIF');
                        break;
                    case 'sticker':
                        messageIcon = '⭐ ';
                        messagePreview = isMyMessage ? (t.you_sent_sticker || 'You sent a sticker') : (t.sent_sticker || 'Sent a sticker');
                        break;
                    case 'story_reply':
                        messageIcon = '📸 ';
                        const storyContent = content.replace('📸 Reply to your story:', '').trim();
                        messagePreview = isMyMessage ? (t.you_replied_to_story || 'You replied to story') : (t.replied_to_story || 'Replied to your story');
                        if (storyContent) {
                            messagePreview += ': ' + storyContent.substring(0, 25);
                        }
                        break;
                    default:
                        messagePreview = content;
                        break;
                }

                // Add "You: " prefix for own messages (except story replies)
                if (isMyMessage && msgType !== 'story_reply') {
                    messagePreview = (t.you || 'You') + ': ' + messagePreview;
                }
                
                // Add status icon for own messages
                let statusIcon = '';
                if (isMyMessage) {
                    statusIcon = isRead
                        ? '<i class="fas fa-check-double read-status read"></i> '
                        : '<i class="fas fa-check read-status sent"></i> ';
                }
                
                // Update preview HTML
                preview.innerHTML = `${statusIcon}<span class="preview-text">${escapeHtml(messageIcon + messagePreview)}</span>`;
                
                // Update unread styling
                if (conv.unread_count > 0) {
                    preview.classList.add('unread-text');
                } else {
                    preview.classList.remove('unread-text');
                }
            }

            // Update unread badge
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

            // Update timestamp
            const timeEl = item.querySelector('.conv-time');
            if (timeEl && conv.last_message_at) {
                timeEl.textContent = formatMessageTime(conv.last_message_at);
            }

            // Update online status for direct chats
            if (conv.other_user && !conv.is_group) {
                const isOnline = conv.other_user.is_online && conv.other_user.last_active && 
                    (new Date().getTime() - new Date(conv.other_user.last_active).getTime()) < 120000;
                
                // Update online status text in conversation title
                const titleContainer = item.querySelector('.conv-title-container');
                if (titleContainer) {
                    let statusSpan = titleContainer.querySelector('.online-status-text');
                    if (!statusSpan) {
                        statusSpan = document.createElement('span');
                        statusSpan.className = 'online-status-text';
                        statusSpan.setAttribute('data-user-id', conv.other_user.id);
                        const titleSpan = titleContainer.querySelector('.conv-title');
                        if (titleSpan) {
                            titleSpan.appendChild(statusSpan);
                        }
                    }
                    statusSpan.className = `online-status-text ${isOnline ? 'online' : 'offline'}`;
                    statusSpan.textContent = isOnline ? '• Online' : '';
                    statusSpan.setAttribute('data-user-id', conv.other_user.id);
                }

                // Update online indicator dot on avatar
                const avatarEl = item.querySelector('.conv-avatar');
                if (avatarEl && !conv.is_group) {
                    let indicator = avatarEl.querySelector('.online-indicator');
                    if (!indicator) {
                        indicator = document.createElement('span');
                        indicator.className = 'online-indicator';
                        indicator.setAttribute('data-user-id', conv.other_user.id);
                        avatarEl.appendChild(indicator);
                    }
                    indicator.classList.toggle('online', isOnline);
                    indicator.title = isOnline ? 'Online' : 'Offline';
                }

                // Check typing status for this conversation (only for a limited number per refresh)
                if (idx <= TYPING_CHECK_LIMIT && conv.slug) {
                    fetch(`/chat/${conv.slug}/typing`, {
                        credentials: 'include',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        const typingInline = item.querySelector('.typing-indicator-inline');
                        if (!typingInline) return;
                        if (data.success && data.is_typing) {
                            typingInline.style.display = 'inline';
                        } else {
                            typingInline.style.display = 'none';
                        }
                    })
                    .catch(() => {});
                }
            }
        }

    function formatMessageTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        // Today - show time
        if (diff < 86400000) {
            return `${hours}:${minutes}`;
        }

        // Yesterday
        if (diff < 172800000) {
            return 'Yesterday';
        }

        // This week - show day name
        if (diff < 604800000) {
            return date.toLocaleDateString('en-US', { weekday: 'short' });
        }

        // Older - show date
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    /**
     * Chat Room Polling
     */
    function startChatRoomPolling() {
        if (state.chatRoomTimer) {
            return;
        }

        // Check online status of the other user when entering chat room
        checkCurrentChatUserOnlineStatus();

        // Start polling for new messages
        state.chatRoomTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkForNewMessages();
            }
        }, window.RealTimeConfig.chatRoomInterval);

        // Start separate polling for online status (every 10 seconds)
        startChatUserStatusPolling();
        
        // Start polling for read receipts (every 500ms for instant feedback)
        startReadReceiptPolling();
    }

    /**
     * Poll online status for current chat user separately
     */
    function startChatUserStatusPolling() {
        // Check status every 10 seconds for the current chat user
        setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                checkCurrentChatUserOnlineStatus();
            }
        }, 10000); // 10 seconds
    }

    function checkCurrentChatUserOnlineStatus() {
        // Get the other user's ID from the chat header
        const chatUserStatus = document.getElementById('chat-user-status');
        if (!chatUserStatus) return;

        const userId = chatUserStatus.getAttribute('data-user-id');
        if (!userId) return;

        // Check online status
        checkUserOnlineStatus(userId).then(data => {
            if (data.success) {
                updateUserOnlineIndicator(userId, data.is_online, data.last_active_human);
            }
        });
    }

    /**
     * Poll for read receipts frequently (every 500ms for instant feedback)
     */
    function startReadReceiptPolling() {
        if (state.readReceiptTimer) {
            return;
        }

        state.readReceiptTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active && state.conversationSlug) {
                // Update read receipts on chat page (updates message icons)
                updateReadReceipts();
            }
        }, 500); // Check every 500ms for instant updates to message read status
    }

    function checkForNewMessages() {
        if (!state.conversationSlug) {
            console.warn('RealTime: No conversation slug for checkForNewMessages');
            return;
        }

        const url = `/chat/${state.conversationSlug}/messages?after=${state.lastMessageId}`;

        fetch(url, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Controller returns 'new_messages' and 'recent_messages'
            if (data.success && data.new_messages && data.new_messages.length > 0) {
                const msgs = data.new_messages;

                let hasNewMessageFromOther = false;

                msgs.forEach(msg => {
                    if (msg.sender_id !== window.RealTimeConfig.userId) {
                        appendMessage(msg);
                        confirmDelivery(msg.id);
                        hasNewMessageFromOther = true;
                    }
                    state.lastMessageId = Math.max(state.lastMessageId, msg.id);
                });

                // Update sidebar conversation preview when new message received
                if (msgs.length > 0) {
                    updateSidebarConversation(msgs[msgs.length - 1]);
                    // also refresh the list so other conversations will reorder immediately
                    refreshChatList();
                }

                // Mark messages as read if we received new messages from other user
                if (hasNewMessageFromOther) {
                    markMessagesAsReadInCurrentChat();
                }
            }
            
            // Check for deleted messages in recent_messages
            if (data.success && data.recent_messages) {
                checkForDeletedMessages(data.recent_messages);
            }
            
            // Also check typing status when receiving new messages
            checkTypingStatus();

            // Update read receipts for own messages
            updateReadReceipts();
        })
        .catch(err => console.error('RealTime: Check messages error:', err));
    }

    /**
     * Check for deleted messages by comparing with DOM
     */
    function checkForDeletedMessages(recentMessages) {
        if (!recentMessages || recentMessages.length === 0) return;

        const userId = window.RealTimeConfig.userId;

        // Get all message IDs currently in the DOM
        const messageElements = document.querySelectorAll('#chatMessages .message[data-message-id]');

        messageElements.forEach(el => {
            const messageId = parseInt(el.dataset.messageId);
            if (!messageId) return;

            // Skip if already marked as deleted or hidden
            if (el.classList.contains('deleted') || el.style.display === 'none') return;

            // Skip if we already processed this deletion
            if (state.deletedMessageIds.has(messageId)) return;

            // Find this message in recent messages
            const msg = recentMessages.find(m => m.id === messageId);
            if (!msg) return;

            // Check if message was deleted for everyone (deleted_by_sender)
            if (msg.deleted_by_sender) {
                markMessageAsDeletedInRealtime(el);
                state.deletedMessageIds.add(messageId);
                console.log('RealTime: Message marked as deleted (everyone):', messageId);
                return;
            }

            // Check if message was deleted for current user
            if (msg.deleted_for && Array.isArray(msg.deleted_for) && msg.deleted_for.includes(userId)) {
                // Hide message for current user
                el.style.display = 'none';
                state.deletedMessageIds.add(messageId);
                console.log('RealTime: Message hidden (deleted for me):', messageId);
            }
        });
    }
    
    /**
     * Mark a message as deleted in the UI (for real-time updates)
     */
    function markMessageAsDeletedInRealtime(el) {
        const contentEl = el.querySelector('.message-content');
        if (contentEl) {
            contentEl.innerHTML = '<em class="deleted-text">message deleted</em>';
            el.classList.add('deleted');
        }
        // Remove delete button if present
        const deleteBtn = el.querySelector('.delete-btn');
        if (deleteBtn) deleteBtn.remove();
    }

    /**
     * Update read receipts for sent messages
     */
    function updateReadReceipts() {
        if (!state.conversationSlug) return;

        // Collect IDs for own messages currently visible in DOM
        const ownMessages = Array.from(document.querySelectorAll('.message.own[data-message-id]'));
        if (ownMessages.length === 0) {
            return;
        }

        const ids = ownMessages.map(el => parseInt(el.dataset.messageId)).filter(Boolean);
        if (ids.length === 0) {
            return;
        }

        fetch(`/chat/${state.conversationSlug}/status`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message_ids: ids })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.statuses) {
                data.statuses.forEach(msg => {
                    const msgEl = document.querySelector(`.message[data-message-id="${msg.id}"]`);
                    if (msgEl) {
                        const timeEl = msgEl.querySelector('.message-time');
                        if (timeEl) {
                            const icon = timeEl.querySelector('i');
                            if (msg.read_at) {
                                if (icon) {
                                    icon.className = 'fas fa-check-double read';
                                    icon.title = 'Seen';
                                } else {
                                    timeEl.innerHTML += '<i class="fas fa-check-double read" title="Seen"></i>';
                                }
                            } else {
                                if (icon) {
                                    icon.className = 'fas fa-check';
                                    icon.title = 'Sent';
                                } else {
                                    timeEl.innerHTML += '<i class="fas fa-check" title="Sent"></i>';
                                }
                            }
                        }
                    }
                });

                // After updating individual message icons in the chat view, refresh
                // the sidebar status icon for the conversation so the list also
                // reflects the latest read/delivered state without a full reload.
                updateSidebarStatusFromDOM();
            }
        })
        .catch(() => {});
    }

    /**
     * Typing Indicator Functions
     */
    let typingTimeout = null;
    let typingInterval = null;
    let isTyping = false;

    function initTypingIndicator() {
        const messageInput = document.getElementById('messageInput');
        if (!messageInput) return;

        // Listen for typing - send immediately on first keystroke
        messageInput.addEventListener('input', function() {
            if (!isTyping) {
                isTyping = true;
                sendTypingStatus(true);
                
                // Keep sending typing status every 500ms while actively typing
                if (typingInterval) clearInterval(typingInterval);
                typingInterval = setInterval(() => {
                    if (isTyping) {
                        sendTypingStatus(true);
                    }
                }, 500);
            }

            // Clear existing timeout
            if (typingTimeout) {
                clearTimeout(typingTimeout);
            }

            // Stop typing after 800ms of inactivity (very fast response)
            typingTimeout = setTimeout(() => {
                isTyping = false;
                if (typingInterval) {
                    clearInterval(typingInterval);
                    typingInterval = null;
                }
                sendTypingStatus(false);
            }, 800);
        });

        // Stop typing when message is sent
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                if (isTyping) {
                    isTyping = false;
                    if (typingInterval) {
                        clearInterval(typingInterval);
                        typingInterval = null;
                    }
                    sendTypingStatus(false);
                }
            });
        }

        // Stop typing when input loses focus
        messageInput.addEventListener('blur', function() {
            if (isTyping) {
                isTyping = false;
                if (typingInterval) {
                    clearInterval(typingInterval);
                    typingInterval = null;
                }
                sendTypingStatus(false);
            }
        });

        // Start polling for other user's typing status (VERY FAST - every 200ms)
        if (state.conversationSlug) {
            setInterval(() => {
                if (state.isVisible && window.RealTimeConfig.active) {
                    checkTypingStatus();
                }
            }, 200); // Check every 200ms for instant response
        }
    }

    function sendTypingStatus(isTyping) {
        if (!state.conversationSlug) return;

        fetch(`/chat/${state.conversationSlug}/typing`, {
            credentials: 'include',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_typing: isTyping })
        })
        .catch(() => {}); // Silent fail
    }

    function checkTypingStatus() {
        if (!state.conversationSlug) return;

        fetch(`/chat/${state.conversationSlug}/typing`, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateTypingIndicator(data.is_typing);
            }
        })
        .catch(() => {});
    }

    function updateTypingIndicator(isTyping) {
        const indicator = document.getElementById('typingIndicator');
        if (!indicator) return;

        if (isTyping) {
            indicator.style.display = 'flex';
            indicator.classList.remove('hiding');
            // Scroll to bottom to show typing indicator
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } else {
            // Add hiding class for smooth fade out
            indicator.classList.add('hiding');
            // Completely hide after transition
            setTimeout(() => {
                if (indicator.classList.contains('hiding')) {
                    indicator.style.display = 'none';
                }
            }, 150);
        }
    }

    function appendMessage(msg) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        // Remove "no messages" placeholder
        const noMessages = container.querySelector('.no-messages');
        if (noMessages) noMessages.remove();

        const isOwn = msg.sender_id == window.RealTimeConfig.userId;
        const div = document.createElement('div');
        div.className = `message ${isOwn ? 'own' : 'other'}`;
        div.dataset.messageId = msg.id;

        const time = new Date(msg.created_at).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        // Build message HTML to match Blade template exactly
        let avatarHtml = '';
        let senderNameHtml = '';
        let contentHtml = '';
        let timeHtml = '';

        // Avatar for other users
        if (!isOwn && msg.sender) {
            const username = msg.sender.username || 'U';
            const avatar = `<img src="${escapeHtml(msg.sender.avatar_url)}" alt="${escapeHtml(username)}">`;
            avatarHtml = `<div class="message-avatar">${avatar}</div>`;
        }

        // Sender name for other users
        if (!isOwn && msg.sender) {
            senderNameHtml = `<div class="sender-name">${escapeHtml(msg.sender.username || msg.sender.name || 'User')}</div>`;
        }

        // Content with media support
        if (msg.type === 'system') {
            // System message - centered, no avatar
            div.className = 'system-message';
            div.innerHTML = `
                <span class="system-text">${escapeHtml(msg.content)}</span>
                <span class="system-time">${time}</span>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            return;
        }
        
        if (msg.type === 'group_invite' && msg.media_path) {
            // Group invite message card - matches Blade template exactly
            try {
                const inviteData = typeof msg.media_path === 'string' ? JSON.parse(msg.media_path) : msg.media_path;
                const isOwn = msg.sender_id == window.RealTimeConfig.userId;
                // For group invite, render the entire message structure
                div.className = `message ${isOwn ? 'own' : 'other'} group-invite`;
                div.innerHTML = `
                    ${!isOwn && msg.sender ? avatarHtml : ''}
                    <div class="message-bubble">
                        ${!isOwn && msg.sender ? senderNameHtml : ''}
                        <div class="invite-card">
                            <div class="invite-icon"><i class="fas fa-users"></i></div>
                            <div class="invite-content">
                                <div class="invite-title">${escapeHtml(inviteData.group_name || 'Group')}</div>
                                <div class="invite-text">${escapeHtml(msg.sender?.username || msg.sender?.name || 'Someone')} invited you to join</div>
                            </div>
                            ${!isOwn && inviteData.invite_link ? `<button class="accept-btn" onclick="acceptGroupInvite('${escapeHtml(inviteData.invite_link)}')"><i class="fas fa-check"></i> Join</button>` : ''}
                        </div>
                        <span class="message-time">${time}${isOwn ? '<i class="fas fa-check" title="Sent"></i>' : ''}</span>
                    </div>
                `;
                
                container.appendChild(div);
                container.scrollTop = container.scrollHeight;
                return; // Skip rest of rendering for group invites
            } catch (e) {
                console.error('Error parsing group invite:', e);
            }
        }
        
        // Regular content rendering (skipped for group invites)
        if (msg.media_path && msg.media_path.startsWith('[')) {
            // Multiple media files (JSON)
            try {
                const mediaItems = JSON.parse(msg.media_path);
                if (Array.isArray(mediaItems) && mediaItems.length > 0) {
                    const displayCount = Math.min(mediaItems.length, 4);
                    const remainingCount = mediaItems.length - displayCount;
                    
                    if (displayCount === 1) {
                        const media = mediaItems[0];
                        if (media.type === 'image') {
                            contentHtml += `<div class="message-media-album"><div class="media-grid-single">
                                <img src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, 0)">
                            </div></div>`;
                        } else if (media.type === 'video') {
                            contentHtml += `<div class="message-media-album"><div class="media-grid-single">
                                <video src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, 0)"></video>
                            </div></div>`;
                        }
                    } else if (displayCount === 2) {
                        contentHtml += `<div class="message-media-album"><div class="media-grid-two">`;
                        mediaItems.slice(0, 2).forEach((media, index) => {
                            if (media.type === 'image') {
                                contentHtml += `<img src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, ${index})">`;
                            } else if (media.type === 'video') {
                                contentHtml += `<div class="media-item video">
                                    <video src="/storage/${escapeHtml(media.path)}"></video>
                                    <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, ${msg.id}, ${index})">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>`;
                            }
                        });
                        contentHtml += `</div></div>`;
                    } else {
                        contentHtml += `<div class="message-media-album"><div class="media-grid-${displayCount}">`;
                        mediaItems.slice(0, displayCount).forEach((media, index) => {
                            if (media.type === 'image') {
                                contentHtml += `<div class="media-item">
                                    <img src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, ${index})">`;
                                if (index === 3 && remainingCount > 0) {
                                    contentHtml += `<div class="media-overlay" onclick="openMediaViewerFromAlbum(this, ${msg.id}, 4)">
                                        <span class="overlay-text">+${remainingCount}</span>
                                    </div>`;
                                }
                                contentHtml += `</div>`;
                            } else if (media.type === 'video') {
                                contentHtml += `<div class="media-item video">
                                    <video src="/storage/${escapeHtml(media.path)}"></video>
                                    <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, ${msg.id}, ${index})">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>`;
                            }
                        });
                        contentHtml += `</div></div>`;
                    }
                }
            } catch (e) {
                console.error('Error parsing media_path:', e);
            }
        } else if (msg.type === 'image' && msg.media_path) {
            contentHtml += `<div class="message-media"><img src="/storage/${escapeHtml(msg.media_path)}" alt="Image" onclick="openMediaViewer(this.src)"></div>`;
        } else if (msg.type === 'video' && msg.media_path) {
            contentHtml += `<div class="message-media"><video src="/storage/${escapeHtml(msg.media_path)}" controls></video></div>`;
        }

        // Text content with story reply detection
        if (msg.content) {
            const isStoryReply = msg.content.startsWith('📸 Reply to your story:');
            const storyReplyContent = isStoryReply ? msg.content.replace('📸 Reply to your story:', '').trim() : null;
            
            if (isStoryReply) {
                contentHtml += `<div class="story-reply-message">
                    <div class="story-reply-header">
                        <span class="story-reply-label">Story Reply</span>
                    </div>
                    <div class="story-reply-content">${escapeHtml(storyReplyContent)}</div>
                </div>`;
            } else {
                contentHtml += `<span class="text">${escapeHtml(msg.content)}</span>`;
            }
        }

        // Time with read receipts for own messages
        if (isOwn) {
            timeHtml = `<span class="message-time">${time}<i class="fas fa-check" title="Sent"></i></span>`;
        } else {
            timeHtml = `<span class="message-time">${time}</span>`;
        }

        div.innerHTML = `
            ${avatarHtml}
            <div class="message-bubble">
                ${senderNameHtml}
                <div class="message-content">
                    ${contentHtml}
                    ${timeHtml}
                </div>
            </div>
        `;

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        
        // Apply RTL direction if message contains Arabic text
        if (typeof applyRTLIfArabic === 'function') {
            applyRTLIfArabic(div);
        }
    }

    function confirmDelivery(messageId) {
        fetch('/chat/message/delivered', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message_id: messageId })
        }).catch(() => {});
    }

    /**
     * Notifications Polling
     * Updates notification badge and list in real-time
     */
    function startNotificationsPolling() {
        if (state.notificationsTimer) {
            return;
        }

        // Initial load
        refreshNotifications();

        // Start polling
        state.notificationsTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                refreshNotifications();
            }
        }, window.RealTimeConfig.notificationsInterval);
    }

    function refreshNotifications() {
        // Build URL with active conversation ID if viewing a chat
        let url = '/api/notifications';
        if (state.activeConversationId) {
            url += '?active_conversation_id=' + encodeURIComponent(state.activeConversationId);
        }

        fetch(url, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                
                // Update notification panel if it exists
                const notifList = document.getElementById('notif-list');
                if (notifList && data.notifications) {
                    // Only update if content changed
                    const currentCount = notifList.querySelectorAll('.notif-item').length;
                    if (currentCount !== data.notifications.length) {
                        updateNotificationList(data.notifications);
                    }
                }
            }
        })
        .catch(err => console.error('Notifications refresh error:', err));
    }

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateNotificationList(notifications) {
        const list = document.getElementById('notif-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>`;
            return;
        }

        list.innerHTML = notifications.map(n => {
            const iconClass = getNotificationIconClass(n.type);
            const notifIcon = getNotificationIcon(n.type);
            const timeAgo = getTimeAgo(n.created_at);
            return `
            <div class="notif-item ${n.read_at ? '' : 'unread'}" id="notif-${n.id}" data-id="${n.id}">
                <div class="notif-icon ${iconClass}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                    <i class="fas ${notifIcon}"></i>
                </div>
                <div class="notif-content ${n.read_at ? '' : 'unread'}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                    <p>${escapeHtml(n.message)}</p>
                    <span class="notif-time">${timeAgo}</span>
                </div>
                <div class="notif-item-actions">
                    ${!n.read_at ? `<button class="notif-item-btn" onclick="event.stopPropagation(); markAsRead(${n.id})" title="Mark as read"><i class="fas fa-check"></i></button>` : ''}
                    <button class="notif-item-btn delete" onclick="event.stopPropagation(); dismissNotification(${n.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `}).join('');
    }

    function getNotificationIconClass(type) {
        const classes = {
            'follow': 'follow',
            'like': 'like',
            'comment': 'comment',
            'mention': 'mention',
            'message': 'message'
        };
        return classes[type] || 'default';
    }

    function getNotificationIcon(type) {
        const icons = {
            'follow': 'fa-user-plus',
            'like': 'fa-heart',
            'comment': 'fa-comment',
            'mention': 'fa-at',
            'message': 'fa-envelope',
            'post': 'fa-newspaper',
            'story': 'fa-circle-play'
        };
        return icons[type] || 'fa-bell';
    }

    function getTimeAgo(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        if (seconds < 60) return 'Just now';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + 'm ago';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h ago';
        const days = Math.floor(hours / 24);
        if (days < 7) return days + 'd ago';
        return date.toLocaleDateString();
    }

    /**
     * Online Status Ping (Real-time presence)
     * Updates own online status and checks others
     */
    function startOnlineStatusPing() {
        if (state.onlineStatusTimer) {
            return;
        }

        // Initial ping
        pingOnlineStatus();

        // Ping every 30 seconds to stay marked as online
        state.onlineStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                pingOnlineStatus();
            }
        }, window.RealTimeConfig.onlineStatusInterval);
    }

    function pingOnlineStatus() {
        fetch('/user/online-status', {
            credentials: 'include',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Update own status indicator if exists
                updateOwnOnlineIndicator(true);
            }
        })
        .catch(() => {});
    }

    function setOfflineStatus() {
        // Set user as offline when closing browser/tab
        fetch('/user/online-status/offline', {
            credentials: 'include',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            keepalive: true // Ensures request completes even if page is closed
        })
        .catch(() => {});
    }

    // Listen for page unload to set offline status
    window.addEventListener('beforeunload', function() {
        setOfflineStatus();
    });

    // Also listen for visibility change to handle tab close
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            setOfflineStatus();
        }
    });

    function updateOwnOnlineIndicator(isOnline) {
        // Update own online indicator in header/profile if exists
        const ownIndicator = document.querySelector('.own-online-indicator');
        if (ownIndicator) {
            if (isOnline) {
                ownIndicator.classList.add('online');
                ownIndicator.style.background = 'var(--wa-green)';
            } else {
                ownIndicator.classList.remove('online');
                ownIndicator.style.background = 'var(--wa-text-muted)';
            }
        }
    }

    function checkUserOnlineStatus(userId) {
        return fetch(`/user/${userId}/online-status`, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                return { success: true, is_online: data.is_online, last_active_human: data.last_active_human };
            }
            return { success: false, is_online: false, last_active_human: null };
        })
        .catch(() => ({ success: false, is_online: false, last_active_human: null }));
    }

    function updateUserOnlineIndicator(userId, isOnline, lastActiveHuman = null) {
        const t = window.chatTranslations || {};
        // Update online status text for specific user in chat list
        const statusTexts = document.querySelectorAll(`.online-status-text[data-user-id="${userId}"]`);
        statusTexts.forEach(statusText => {
            if (isOnline) {
                statusText.className = 'online-status-text online';
                statusText.textContent = '• ' + (t.online || 'Online');
                statusText.title = t.online || 'Online';
            } else {
                statusText.className = 'online-status-text offline';
                statusText.textContent = '• ' + (t.offline || 'Offline');
                statusText.title = lastActiveHuman || (t.offline || 'Offline');
            }
        });

        // Also update in chat room header if this user is the other participant
        const chatUserStatus = document.getElementById(`chat-user-status`);
        if (chatUserStatus && chatUserStatus.getAttribute('data-user-id') == userId) {
            const statusDot = chatUserStatus.querySelector('.status-dot');
            const statusText = chatUserStatus.querySelector('.status-text');
            if (isOnline) {
                chatUserStatus.classList.add('online');
                chatUserStatus.style.color = 'var(--wa-green)';
                if (statusDot) statusDot.style.background = 'var(--wa-green)';
                if (statusText) statusText.textContent = t.online || 'Online';
            } else {
                chatUserStatus.classList.remove('online');
                chatUserStatus.style.color = 'var(--wa-text-muted)';
                if (statusDot) statusDot.style.background = 'var(--wa-text-muted)';
                if (statusText) statusText.textContent = lastActiveHuman || (t.offline || 'Offline');
            }
        }
    }

    function refreshAllOnlineStatuses() {
        // Get all user IDs from chat list
        const userIds = [];
        document.querySelectorAll('.conversation-item[data-user-id]').forEach(item => {
            const userId = item.getAttribute('data-user-id');
            if (userId) userIds.push(userId);
        });

        if (userIds.length === 0) return;

        // Batch check online status
        fetch('/user/online-status/batch', {
            credentials: 'include',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.statuses) {
                for (const [userId, status] of Object.entries(data.statuses)) {
                    updateUserOnlineIndicator(userId, status.is_online, status.last_active_human);
                }
            }
        })
        .catch(() => {});
    }

    /**
     * Account Status Check (Security Monitoring)
     * Checks for: suspension, deletion, concurrent login
     */
    function startAccountStatusCheck() {
        state.accountStatusTimer = setInterval(() => {
            if (state.isVisible && window.RealTimeConfig.active) {
                checkAccountStatus();
            }
        }, window.RealTimeConfig.accountStatusInterval);
    }

    function checkAccountStatus() {
        fetch('/user/check-account-status', {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => {
            // Check if response is HTML (redirect)
            const contentType = r.headers.get('content-type');
            if (contentType && contentType.includes('text/html')) {
                // Server returned HTML redirect - follow it
                window.location.href = r.url;
                throw new Error('Redirecting...');
            }

            if (!r.ok) {
                return r.json().then(data => {
                    throw { status: r.status, data: data };
                });
            }
            return r.json();
        })
        .then(data => {
            if (data.status === 'active') {
                // Account is fine, continue normally
                return;
            }

            // Handle different account states
            handleAccountStatusChange(data);
        })
        .catch((err) => {
            if (err.status === 403 && err.data) {
                handleAccountStatusChange(err.data);
            }
            // For other errors (including redirect), silently fail
        });
    }

    function handleAccountStatusChange(data) {
        const { status, message, redirect } = data;

        // Stop all polling
        window.RealTimeConfig.active = false;

        // Determine message and toast type based on status
        let toastMessage = message || window.chatTranslations.account_status_changed;
        let toastType = 'error';

        switch (status) {
            case 'suspended':
                toastMessage = window.chatTranslations.account_suspended_message;
                break;
            case 'unverified':
                toastMessage = window.chatTranslations.please_verify_email_message;
                toastType = 'warning';
                break;
            case 'concurrent_login':
                toastMessage = window.chatTranslations.concurrent_login_message;
                toastType = 'warning';
                break;
            case 'logged_out':
                toastMessage = window.chatTranslations.logged_out_message;
                break;
            case 'deleted':
                toastMessage = window.chatTranslations.account_deleted_message;
                break;
        }

        // Show message to user
        if (typeof showToast === 'function') {
            showToast(toastMessage, toastType, 5000);
        } else {
            alert(toastMessage);
        }

        // Redirect after short delay
        setTimeout(() => {
            if (redirect) {
                window.location.href = redirect;
            } else {
                window.location.href = '/login';
            }
        }, 2000);
    }

    /**
     * Accept group invite from chat message
     */
    function acceptGroupInvite(inviteLink) {
        const btn = event.target.closest('.accept-btn');
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`/groups/accept-invite/${inviteLink}`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Joined';
                btn.classList.add('joined');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Join';
                alert(data.message || 'Failed to join group');
            }
        })
        .catch(err => {
            console.error('Error accepting invite:', err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Join';
        });
    }

    /**
     * Utilities
     */
    function handleVisibilityChange() {
        state.isVisible = document.visibilityState === 'visible';

        if (state.isVisible) {
            // Resume polling when page becomes visible
            if (window.location.pathname === '/chat' || window.location.pathname === '/chat/') {
                refreshChatList();
            } else if (window.location.pathname.startsWith('/chat/')) {
                refreshChatList();
                checkForNewMessages();
            }
            // Refresh notifications when page becomes visible
            refreshNotifications();
        }
    }

    function cleanup() {
        window.RealTimeConfig.active = false;
        state.activeConversationId = null;  // Clear active conversation ID

        if (state.chatListTimer) {
            clearInterval(state.chatListTimer);
            state.chatListTimer = null;
        }

        if (state.chatRoomTimer) {
            clearInterval(state.chatRoomTimer);
            state.chatRoomTimer = null;
        }

        if (state.accountStatusTimer) {
            clearInterval(state.accountStatusTimer);
            state.accountStatusTimer = null;
        }

        if (state.notificationsTimer) {
            clearInterval(state.notificationsTimer);
            state.notificationsTimer = null;
        }

        if (state.onlineStatusTimer) {
            clearInterval(state.onlineStatusTimer);
            state.onlineStatusTimer = null;
        }

        if (state.readReceiptTimer) {
            clearInterval(state.readReceiptTimer);
            state.readReceiptTimer = null;
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // Mark messages as read in current chat (called when receiving new messages)
    function markMessagesAsReadInCurrentChat() {
        if (!state.conversationSlug) return;

        fetch(`/chat/${state.conversationSlug}/read`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
            }
        })
        .catch(err => console.error('RealTime: Mark read error:', err));
    }

    // Expose a public wrapper so pages can request marking messages as read
    function markMessagesAsRead() {
        return markMessagesAsReadInCurrentChat();
    }

    /**
     * Update sidebar conversation preview when new message is sent/received
     */
    function updateSidebarConversation(message) {
        // Support both ID naming conventions
        const conversationsList = document.getElementById('sidebarConvList') || document.getElementById('conversationsList');
        if (!conversationsList || !message) return;

        const items = conversationsList.querySelectorAll('.conversation-item');
        let currentItem = null;

        // Find the current conversation item
        items.forEach(item => {
            const href = item.getAttribute('href') || '';
            const slug = item.getAttribute('data-conversation-slug');
            
            if (state.conversationSlug && (slug === state.conversationSlug || href.includes(`/chat/${state.conversationSlug}`))) {
                currentItem = item;
            }
        });

        // If conversation item doesn't exist in DOM, it's a new conversation
        // Refresh the entire chat list to fetch it
        if (!currentItem) {
            refreshChatList();
            return;
        }

        // Update preview text with status icon
        const preview = currentItem.querySelector('.conv-preview');
        const convTime = currentItem.querySelector('.conv-time');

        if (preview) {
            const isOwn = message.sender_id == window.RealTimeConfig.userId;
            let previewText = '';

            if (['image', 'video', 'audio', 'document', 'gif', 'sticker'].includes(message.type)) {
                previewText = getMediaPreviewText(message.type, isOwn);
            } else if (message.content) {
                previewText = (isOwn ? 'You: ' : '') + message.content;
            }

            // Build preview HTML with status icon for own messages
            let statusIcon = '';
            if (isOwn) {
                statusIcon = message.read_at 
                    ? '<i class="fas fa-check-double read-status read"></i> ' 
                    : '<i class="fas fa-check read-status sent"></i> ';
            }

            const previewTextEl = preview.querySelector('.preview-text');
            if (previewTextEl) {
                previewTextEl.textContent = previewText.substring(0, 40);
            }
            
            // Update or add status icon
            let statusEl = preview.querySelector('.read-status');
            if (statusEl) {
                statusEl.remove();
            }
            
            if (statusIcon) {
                const iconEl = document.createElement('span');
                iconEl.innerHTML = statusIcon;
                preview.insertBefore(iconEl.firstElementChild, preview.firstChild);
            }
        }

        // Update time
        if (convTime && message.created_at) {
            const date = new Date(message.created_at);
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            convTime.textContent = `${hours}:${minutes}`;
        }

        // Move this conversation to top
        if (conversationsList && currentItem.parentNode) {
            conversationsList.insertBefore(currentItem, conversationsList.firstChild);
        }
    }

    function getMediaPreviewText(type, isOwn) {
        const t = window.chatTranslations || {};
        const prefix = (t.you || 'You') + ': ';
        switch(type) {
            case 'image': return prefix + (t.sent_an_image || 'Sent an image');
            case 'video': return prefix + (t.sent_a_video || 'Sent a video');
            case 'audio': return prefix + (t.sent_an_audio || 'Sent an audio');
            case 'document': return prefix + (t.sent_a_document || 'Sent a document');
            case 'gif': return prefix + (t.sent_a_gif || 'Sent a GIF');
            case 'sticker': return prefix + (t.sent_a_sticker || 'Sent a sticker');
            case 'story_reply': return prefix + (t.replied_to_story || 'Replied to story');
            default: return '';
        }
    }


    /**
     * Update the status icon in the sidebar for the current conversation.
     * We look at the last message sent by the user in the DOM and mirror its
     * read/delivered icon onto the conversation preview. This avoids needing
     * to re-fetch the entire conversation or modify updateSidebarConversation.
     */
    function updateSidebarStatusFromDOM() {
        if (!state.conversationSlug) return;

        // Find sidebar item for this conversation
        const convItem = document.querySelector(`.conversation-item[data-conversation-slug="${state.conversationSlug}"]`);
        if (!convItem) return;

        const statusEl = convItem.querySelector('.read-status');
        if (!statusEl) return;

        // Determine status based on last own message icon in chat view
        const ownMsgs = Array.from(document.querySelectorAll('.message.own[data-message-id]'));
        if (ownMsgs.length === 0) return;
        const lastOwn = ownMsgs[ownMsgs.length - 1];
        const icon = lastOwn.querySelector('.message-time i');
        if (!icon) return;

        // Mirror icon classes on sidebar status element
        statusEl.className = icon.className + ' read-status';
        // ensure tooltip/title matches
        statusEl.title = icon.title || '';
    }

    // Public API
    window.RealTime = {
        init: init,
        stop: cleanup,
        refreshChatList: refreshChatList,
        checkForNewMessages: checkForNewMessages,
        checkAccountStatus: checkAccountStatus,
        startAccountStatusCheck: startAccountStatusCheck,
        refreshNotifications: refreshNotifications,
        startNotificationsPolling: startNotificationsPolling,
        updateNotificationBadge: updateNotificationBadge,
        updateSidebarConversation: updateSidebarConversation,
        getMediaPreviewText: getMediaPreviewText,
        updateReadReceipts: updateReadReceipts,
        markMessagesAsRead: markMessagesAsRead
    };

    // Expose group invite function globally
    window.acceptGroupInvite = acceptGroupInvite;

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
