// Sidebar toggle for mobile
function toggleSidebar() {
    document.getElementById('chatSidebar').classList.toggle('active');
}

// Filter sidebar conversations
function filterSidebarConversations(q) {
    const items = document.querySelectorAll('#sidebarConvList .conversation-item');
    const query = q.toLowerCase();
    items.forEach(item => {
        const name = item.getAttribute('data-name')?.toLowerCase() || '';
        item.style.display = name.includes(query) ? 'flex' : 'none';
    });
}

// User search modal
function showUserSearch() {
    document.getElementById('userSearchModal').style.display = 'flex';
    setTimeout(() => document.getElementById('userSearch').focus(), 100);
}

function hideUserSearch() {
    document.getElementById('userSearchModal').style.display = 'none';
}

// User search
document.getElementById('userSearch')?.addEventListener('input', function() {
    const query = this.value.trim();
    const results = document.getElementById('userResults');
    if (query.length < 2) { results.innerHTML = ''; return; }

    fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
        credentials: 'include',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.users.length) {
            results.innerHTML = data.users.map(u => `
                <div class="result-item" onclick="startChat(${u.id})">
                    <img src="${escapeHtml(u.avatar_url)}">
                    <div class="result-info">
                        <div class="result-name">${escapeHtml(u.username)}</div>
                        ${u.name && u.name !== u.username ? `<div class="result-fullname">${escapeHtml(u.name)}</div>` : ''}
                    </div>
                </div>
            `).join('');
        }
    });
});

function escapeHtml(t) {
    const d = document.createElement('div');
    d.textContent = t || '';
    return d.innerHTML;
}

function startChat(id) { window.location.href = '/chat/start/' + id; }

// Send message
function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    const hasMedia = selectedFiles.length > 0;

    if (!content && !hasMedia) return;

    input.disabled = true;
    document.getElementById('sendButton').disabled = true;

    // If has media, send as FormData
    if (hasMedia) {
        sendMediaMessage(content, null);
        return;
    }

    // Send text message
    fetch(window.chatStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const message = {
                id: data.message.id,
                content: data.message.content,
                created_at: data.message.created_at,
                type: data.message.type,
                media_path: data.message.media_path,
                sender_id: window.currentUserId,
                sender: {
                    username: window.currentUsername,
                    avatar_url: window.currentUserAvatarUrl
                },
                read_at: null
            };

            addMessage(message);
            if (window.RealTime && typeof window.RealTime.updateSidebarConversation === 'function') {
                window.RealTime.updateSidebarConversation(message);
            }

            myLastMessageId = data.message.id;
            lastMessageId = data.message.id;
            input.value = '';

            // Don't confirm delivery - sender can't confirm their own message
            // Recipient will confirm delivery when they receive the message
        }
    })
    .catch(() => {})
    .finally(() => {
        input.disabled = false;
        document.getElementById('sendButton').disabled = false;
        // Don't refocus - keep keyboard state as is
    });
}

// Send media message (supports multiple files in one message)
function sendMediaMessage(content, mediaFile) {
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    // Create FormData with all files
    const formData = new FormData();
    if (content) {
        formData.append('content', content);
    }

    // Append ALL selected files
    selectedFiles.forEach((file) => {
        formData.append('media[]', file); // Array of files
    });

    fetch(window.chatStoreUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.message) {
            const message = {
                id: data.message.id,
                content: data.message.content || '',
                created_at: data.message.created_at,
                type: data.message.type,
                media_path: data.message.media_path,
                sender_id: window.currentUserId,
                sender: {
                    username: window.currentUsername,
                    avatar_url: window.currentUserAvatarUrl
                },
                read_at: null
            };

            // Add the message with all media
            addMessage(message);
            if (window.RealTime && typeof window.RealTime.updateSidebarConversation === 'function') {
                window.RealTime.updateSidebarConversation(message);
            }

            myLastMessageId = data.message.id;
            lastMessageId = data.message.id;
            input.value = '';
            clearMediaPreview();
        } else {
            showToast(data.error || window.chatTranslations?.failed_to_send_media || window.chatTranslations?.failed_to_send_media_msg || 'Failed to send media', 'error');
        }
    })
    .catch(err => {
        console.error('Error sending media:', err);
        showToast(window.chatTranslations?.error_sending_media || window.chatTranslations?.error_sending_media_msg || 'Error sending media', 'error');
    })
    .finally(() => {
        input.disabled = false;
        sendButton.disabled = false;
        // Don't refocus - keep keyboard state as is
    });
}

// Add message to chat
function addMessage(msg) {
    const container = document.getElementById('chatMessages');
    const noMsg = container.querySelector('.no-messages');
    if (noMsg) noMsg.remove();

    const isOwn = msg.sender_id == window.currentUserId;
    const div = document.createElement('div');
    div.className = `message ${isOwn ? 'own' : 'other'}`;
    div.dataset.messageId = msg.id;

    // Format time to match Blade's H:i format (24-hour, e.g., "23:45")
    const date = new Date(msg.created_at);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const time = `${hours}:${minutes}`;

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

    // Handle system messages
    if (msg.type === 'system') {
        div.className = 'system-message';
        div.innerHTML = `
            <span class="system-text">${escapeHtml(msg.content)}</span>
            <span class="system-time">${time}</span>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        void div.offsetWidth;
        return;
    }

    // Handle group invite messages
    if (msg.type === 'group_invite' && msg.media_path) {
        try {
            const inviteData = typeof msg.media_path === 'string' ? JSON.parse(msg.media_path) : msg.media_path;
            div.className = `message ${isOwn ? 'own' : 'other'} group-invite`;
            div.innerHTML = `
                ${!isOwn && msg.sender ? avatarHtml : ''}
                <div class="message-bubble">
                    ${!isOwn && msg.sender ? senderNameHtml : ''}
                    <div class="invite-card">
                        <div class="invite-icon"><i class="fas fa-users"></i></div>
                        <div class="invite-content">
                            <div class="invite-title">${escapeHtml(inviteData.group_name || window.chatTranslations.group)}</div>
                            <div class="invite-text">${escapeHtml(msg.sender?.username || msg.sender?.name || 'Someone')} ${escapeHtml(window.chatTranslations.invited_you_to_join)}</div>
                        </div>
                        ${!isOwn && inviteData.invite_link ? `<button class="accept-btn" onclick="acceptGroupInvite('${escapeHtml(inviteData.invite_link)}')"><i class="fas fa-check"></i> ${escapeHtml(window.chatTranslations.join)}</button>` : ''}
                    </div>
                    <span class="message-time">${time}${isOwn ? '<i class="fas fa-check" title="' + window.chatTranslations.sent + '"></i>' : ''}</span>
                </div>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            void div.offsetWidth;
            return;
        } catch (e) {
            console.error('Error parsing group invite:', e);
        }
    }

    // Handle multiple media files (JSON)
    if (msg.media_path && msg.media_path.startsWith('[')) {
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
    if (msg.content && msg.content.trim()) {
        const isStoryReply = msg.content && msg.content.startsWith('📸 Reply to your story:');
        if (isStoryReply) {
            const storyReplyContent = msg.content.replace('📸 Reply to your story:', '').trim();
            contentHtml += `<div class="story-reply-message">
                <div class="story-reply-header">
                    <span class="story-reply-label">${escapeHtml(window.chatTranslations.story_reply)}</span>
                </div>
                <div class="story-reply-content">${escapeHtml(storyReplyContent)}</div>
            </div>`;
        } else {
            contentHtml += `<span class="text">${escapeHtml(msg.content)}</span>`;
        }
    }

    // Time with read receipts for own messages
    if (isOwn) {
        timeHtml = `<span class="message-time">${time}<i class="fas fa-check" title="${escapeHtml(window.chatTranslations.sent)}"></i></span>`;
    } else {
        timeHtml = `<span class="message-time">${time}</span>`;
    }

    div.innerHTML = `
        ${avatarHtml}
        <div class="message-bubble">
            ${senderNameHtml}
            <div class="message-content">
                ${contentHtml}${timeHtml}
            </div>
            ${isOwn ? `<button class="delete-btn" onclick="deleteMessage(${msg.id})"><i class="fas fa-trash"></i></button>` : ''}
        </div>
    `;

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;

    // Apply RTL direction if message contains Arabic text
    applyRTLIfArabic(div);

    // Store media list for this message if it has multiple media
    if (msg.media_path && msg.media_path.startsWith('[')) {
        try {
            const mediaItems = JSON.parse(msg.media_path);
            if (Array.isArray(mediaItems)) {
                const mediaList = mediaItems.map((media, i) => ({
                    src: `/storage/${media.path}`,
                    type: media.type
                }));
                messageMediaLists.set(msg.id.toString(), mediaList);
            }
        } catch (e) {
            console.error('Error storing media list:', e);
        }
    }

    // Trigger reflow to ensure animation plays
    void div.offsetWidth;
}

// Media handling - support multiple files with carousel preview
let selectedFiles = [];
let currentPreviewIndex = 0;

function handleMediaSelect(e) {
    const files = Array.from(e.target.files);
    if (!files.length) return;

    // Add to selected files
    selectedFiles = [...selectedFiles, ...files];

    // Show carousel preview
    showCarouselPreview();
}

function showCarouselPreview() {
    const preview = document.getElementById('mediaPreview');
    const slidesContainer = document.getElementById('previewSlides');
    const indicatorsContainer = document.getElementById('previewIndicators');
    const countEl = document.getElementById('previewCount');

    if (!selectedFiles.length) {
        preview.style.display = 'none';
        return;
    }

    preview.style.display = 'block';
    slidesContainer.innerHTML = '';
    indicatorsContainer.innerHTML = '';

    // Create slides
    selectedFiles.forEach((file, index) => {
        const slide = document.createElement('div');
        slide.className = `preview-slide ${index === currentPreviewIndex ? 'active' : ''}`;

        const slideNumber = document.createElement('div');
        slideNumber.className = 'slide-number';
        slideNumber.textContent = `${index + 1} / ${selectedFiles.length}`;

        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-slide';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = () => removePreview(index);

        slide.appendChild(slideNumber);
        slide.appendChild(removeBtn);

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                const img = document.createElement('img');
                img.src = ev.target.result;
                slide.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                const video = document.createElement('video');
                video.src = ev.target.result;
                video.controls = false;
                slide.appendChild(video);
            };
            reader.readAsDataURL(file);
        }

        slidesContainer.appendChild(slide);

        // Create indicator
        const indicator = document.createElement('div');
        indicator.className = `preview-indicator ${index === currentPreviewIndex ? 'active' : ''}`;
        indicator.onclick = () => goToPreview(index);
        indicatorsContainer.appendChild(indicator);
    });

    // Update count
    countEl.textContent = `${currentPreviewIndex + 1} / ${selectedFiles.length}`;

    // Update arrow states
    updateArrowStates();
}

function movePreview(direction) {
    if (!selectedFiles.length) return;

    currentPreviewIndex += direction;

    // Wrap around
    if (currentPreviewIndex < 0) {
        currentPreviewIndex = selectedFiles.length - 1;
    } else if (currentPreviewIndex >= selectedFiles.length) {
        currentPreviewIndex = 0;
    }

    updatePreviewDisplay();
}

function goToPreview(index) {
    currentPreviewIndex = index;
    updatePreviewDisplay();
}

function updatePreviewDisplay() {
    const slides = document.querySelectorAll('.preview-slide');
    const indicators = document.querySelectorAll('.preview-indicator');
    const countEl = document.getElementById('previewCount');

    slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === currentPreviewIndex);
    });

    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentPreviewIndex);
    });

    countEl.textContent = `${currentPreviewIndex + 1} / ${selectedFiles.length}`;

    updateArrowStates();
}

function updateArrowStates() {
    const arrows = document.querySelectorAll('.carousel-arrow');
    if (selectedFiles.length <= 1) {
        arrows.forEach(arrow => arrow.disabled = true);
    } else {
        arrows.forEach(arrow => arrow.disabled = false);
    }
}

function removePreview(index) {
    selectedFiles.splice(index, 1);

    // Adjust current index
    if (currentPreviewIndex >= selectedFiles.length) {
        currentPreviewIndex = Math.max(0, selectedFiles.length - 1);
    }

    if (!selectedFiles.length) {
        clearMediaPreview();
    } else {
        showCarouselPreview();
    }

    if (!selectedFiles.length) {
        document.getElementById('mediaInput').value = '';
    }
}

function clearMediaPreview() {
    selectedFiles = [];
    currentPreviewIndex = 0;
    document.getElementById('mediaPreview').style.display = 'none';
    document.getElementById('previewSlides').innerHTML = '';
    document.getElementById('previewIndicators').innerHTML = '';
    document.getElementById('mediaInput').value = '';
}

// Media viewer with album navigation
let currentMediaIndex = 0;
let currentMediaList = [];

// Store media lists by message ID for quick access
const messageMediaLists = new Map();

// Initialize media lists from existing messages
document.addEventListener('DOMContentLoaded', () => {


    document.querySelectorAll('.message-media-album').forEach((album) => {
        const messageId = album.dataset.messageId;

        if (!messageId) {
            return;
        }

        // Get all media from the embedded script tag
        const scriptTag = album.querySelector('script.media-data');
        let mediaList = [];

        if (scriptTag) {
            try {
                const allMedia = JSON.parse(scriptTag.textContent.trim());
                mediaList = allMedia.map((media) => ({
                    src: `/storage/${media.path}`,
                    type: media.type
                }));
            } catch (e) {
                console.error('❌ Failed to parse media JSON:', e);
                console.error('Script content:', scriptTag.textContent);
                return;
            }
        }

        if (mediaList.length > 0) {
            messageMediaLists.set(messageId.toString(), mediaList);
        }
    });


});

function openMediaViewerFromAlbum(element, messageId, index = 0) {
    const mediaList = messageMediaLists.get(messageId.toString());

    if (mediaList && mediaList.length > 0) {
        openMediaViewer(null, mediaList, index);
    } else {
        // Fallback for single image
        if (element && element.tagName === 'IMG') {
            openMediaViewer(element.src);
        }
    }
}

function openMediaViewer(src, mediaList = null, index = 0) {
    const viewer = document.getElementById('mediaViewer');
    const imgEl = document.getElementById('viewerImage');
    const vidEl = document.getElementById('viewerVideo');
    const counterEl = document.getElementById('viewerCounter');



    if (mediaList && mediaList.length > 0) {
        // Opening from album - store the list
        currentMediaList = mediaList;
        currentMediaIndex = index;
    } else {
        // Opening single image
        currentMediaList = [{src: src, type: 'image'}];
        currentMediaIndex = 0;
    }

    showCurrentMedia();
    viewer.classList.add('active');
}

function showCurrentMedia() {
    if (!currentMediaList[currentMediaIndex]) return;

    const media = currentMediaList[currentMediaIndex];
    const imgEl = document.getElementById('viewerImage');
    const vidEl = document.getElementById('viewerVideo');
    const counterEl = document.getElementById('viewerCounter');

    if (media.type === 'video') {
        imgEl.style.display = 'none';
        vidEl.style.display = 'block';
        vidEl.src = media.src;
        vidEl.play();
    } else {
        vidEl.style.display = 'none';
        vidEl.pause();
        imgEl.style.display = 'block';
        imgEl.src = media.src;
    }

    // Update counter
    counterEl.textContent = `${currentMediaIndex + 1} / ${currentMediaList.length}`;
}

function navigateMedia(direction, event) {
    if (event) event.stopPropagation();

    if (currentMediaList.length <= 1) return;

    currentMediaIndex += direction;

    // Wrap around
    if (currentMediaIndex < 0) {
        currentMediaIndex = currentMediaList.length - 1;
    } else if (currentMediaIndex >= currentMediaList.length) {
        currentMediaIndex = 0;
    }

    showCurrentMedia();
}

function closeMediaViewerOnOverlay(event) {
    // Only close if clicking the overlay (not the content)
    if (event && event.target !== event.currentTarget) return;
    closeMediaViewer();
}

function closeMediaViewer() {
    const viewer = document.getElementById('mediaViewer');
    const vidEl = document.getElementById('viewerVideo');

    vidEl.pause();
    viewer.classList.remove('active');
    currentMediaList = [];
    currentMediaIndex = 0;
}

// Keyboard navigation for media viewer
document.addEventListener('keydown', (e) => {
    const viewer = document.getElementById('mediaViewer');
    if (!viewer.classList.contains('active')) return;

    if (e.key === 'ArrowLeft') {
        navigateMedia(-1);
    } else if (e.key === 'ArrowRight') {
        navigateMedia(1);
    } else if (e.key === 'Escape') {
        closeMediaViewer();
    }
});

// Clear chat
function clearChat() {
    if (confirm(window.chatTranslations.confirm_delete)) {
        fetch(window.chatClearUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(() => location.reload());
    }
}

// Delete message - show modal
let messageToDeleteId = null;

function deleteMessage(id) {
    messageToDeleteId = id;
    document.getElementById('deleteMessageModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteMessageModal').style.display = 'none';
    messageToDeleteId = null;
}

function confirmDelete(type) {
    if (!messageToDeleteId) return;

    // capture the id now so closing the modal (which nulls the variable)
    // doesn't wipe it out before the fetch callback uses it.
    const id = messageToDeleteId;

    closeDeleteModal();

    fetch(`/chat/message/${id}?type=${type}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            handleDeleteMessage(data.deleted_message_id, data.delete_type, data.deleted_for);
        }
    })
    .catch(err => console.error('Delete failed:', err));
}

// Handle message deletion UI update
function handleDeleteMessage(messageId, deleteType, deletedFor) {
    const msgEl = document.querySelector(`.message[data-message-id="${messageId}"]`);
    if (!msgEl) return;

    if (deleteType === 'everyone') {
        // Show "message deleted" for everyone
        msgEl.classList.add('deleted');
        const content = msgEl.querySelector('.message-content');
        if (content) {
            content.innerHTML = `<em class="deleted-text">${window.chatTranslations.message_deleted}</em>`;
        }
        // Remove delete button
        const deleteBtn = msgEl.querySelector('.delete-btn');
        if (deleteBtn) deleteBtn.remove();
    } else {
        // Delete for me only - hide the message
        msgEl.style.display = 'none';
    }
}

// Mark message as deleted in the UI (for realtime.js)
function markMessageAsDeleted(id) {
    const el = document.querySelector(`[data-message-id="${id}"]`);
    if (el) {
        const contentEl = el.querySelector('.message-content');
        if (contentEl) {
            contentEl.innerHTML = `<em class="deleted-text">${window.chatTranslations.message_deleted}</em>`;
            el.classList.add('deleted');
        }
        // Remove delete button
        const deleteBtn = el.querySelector('.delete-btn');
        if (deleteBtn) deleteBtn.remove();
    }
}

// Group invite handling delegated to `public/js/realtime.js` (window.acceptGroupInvite)

// Auto scroll to bottom on load and initialize
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('chatMessages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    // Mark messages as read only when chat is actively viewed (delegated to RealTime)
    if (window.RealTime && typeof window.RealTime.markMessagesAsRead === 'function') {
        window.RealTime.markMessagesAsRead();
    }

    // Mark messages as read when window gains focus
    window.addEventListener('focus', () => {
        if (window.RealTime && typeof window.RealTime.markMessagesAsRead === 'function') {
            window.RealTime.markMessagesAsRead();
        }
    });

    // Update online status when leaving the page
    window.addEventListener('beforeunload', () => {
        navigator.sendBeacon('/user/online-status/offline', JSON.stringify({
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }));
    });


});

// Delivery confirmations and read-marking delegated to RealTime.js

// Get media preview text
function getMediaPreviewText(type, isOwn) {
    const prefix = isOwn ? window.chatTranslations.you + ': ' : '';
    switch(type) {
        case 'image': return prefix + window.chatTranslations.sent_an_image;
        case 'video': return prefix + window.chatTranslations.sent_a_video;
        case 'audio': return prefix + window.chatTranslations.sent_an_audio;
        case 'document': return prefix + window.chatTranslations.sent_a_document;
        case 'gif': return prefix + window.chatTranslations.sent_a_gif;
        case 'sticker': return prefix + window.chatTranslations.sent_a_sticker;
        case 'story_reply': return prefix + window.chatTranslations.replied_to_story;
        default: return '';
    }
}

// Sidebar conversation updates are handled by realtime.js

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeMediaViewer();
        hideUserSearch();
    }
});

// Auto-detect Arabic text in dynamically loaded messages
function applyRTLIfArabic(element) {
    const arabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\u0590-\u05FF]/;
    const textElements = element.querySelectorAll('.text');
    textElements.forEach(el => {
        const text = el.textContent || el.innerText || '';
        if (arabicPattern.test(text)) {
            el.setAttribute('dir', 'rtl');
            el.style.direction = 'rtl';
            el.style.textAlign = 'right';
        }
    });
}
