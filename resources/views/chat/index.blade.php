@extends('layouts.app')

@section('title', 'Messages - Chat')

@section('content')
<div class="chat-page">
    <div class="chat-container">
        <div class="chat-sidebar">
            <div class="chat-header">
                <h2>Messages</h2>
                <button class="new-chat-btn" onclick="showUserSearch()">
                    <i class="fas fa-plus"></i>
                    New Chat
                </button>
            </div>

            
            <div class="followers-section">
                <h3>Start Chat with Followers</h3>
                <div class="followers-list">
                    @php
                        $followers = \App\Models\User::whereHas('follows', function($query) {
                            $query->where('followed_id', auth()->id());
                        })->with('profile')->take(10)->get();
                    @endphp
                    @forelse($followers as $follower)
                    <div class="follower-item" onclick="startChatWithUser({{ $follower->id }})">
                        @if($follower->profile && $follower->profile->avatar)
                            <img src="{{ asset('storage/' . $follower->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <span>{{ $follower->name }}</span>
                    </div>
                    @empty
                    <p class="no-followers">No followers yet</p>
                    @endforelse
                </div>
            </div>

            <div class="conversations-list">
                @forelse($conversations as $conversation)
                <a href="{{ route('chat.show', $conversation) }}" class="conversation-item {{ $conversation->unread_count > 0 ? 'unread' : '' }}" data-conversation-id="{{ $conversation->id }}">
                    <div class="conversation-avatar">
                        @if($conversation->other_user->profile && $conversation->other_user->profile->avatar)
                            <img src="{{ asset('storage/' . $conversation->other_user->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>

                    <div class="conversation-info">
                        <div class="conversation-name">{{ $conversation->other_user->name }}</div>
                        <div class="conversation-preview">
                            @if($conversation->latestMessage)
                                <span class="last-message">
                                    {{ $conversation->latestMessage->sender_id === auth()->id() ? 'You: ' : '' }}
                                    {{ Str::limit($conversation->latestMessage->content, 30) }}
                                </span>
                            @else
                                <span class="no-messages">No messages yet</span>
                            @endif
                        </div>
                    </div>

                    <div class="conversation-meta">
                        @if($conversation->last_message_at)
                            <div class="last-time">{{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}</div>
                        @endif
                        @if($conversation->unread_count > 0)
                            <div class="unread-badge">{{ $conversation->unread_count }}</div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="no-conversations">
                    <i class="fas fa-comments"></i>
                    <p>No conversations yet</p>
                    <p>Start a chat by clicking "New Chat"</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="chat-main">
            <div class="chat-placeholder">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose a conversation from the sidebar to start chatting</p>
            </div>
        </div>
    </div>

    
    <div id="userSearchModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Start New Conversation</h3>
                <button class="close-btn" onclick="hideUserSearch()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="userSearch" placeholder="Search users..." class="search-input">
                <div id="userResults" class="user-results"></div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-page {
    height: calc(100vh - 80px);
    background: var(--twitter-light);
}

.chat-container {
    display: flex;
    height: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.chat-sidebar {
    width: 350px;
    background: var(--card-bg);
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.new-chat-btn {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: background 0.2s;
}

.new-chat-btn:hover {
    background: #1a91da;
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    box-shadow: 0 1px 0 rgba(0,0,0,0.05);
}

.conversation-item:hover,
.conversation-item.active {
    background: var(--hover-bg);
    transform: translateX(2px);
}

.conversation-item.unread {
    background: rgba(29, 161, 242, 0.08);
    border-left: 3px solid var(--twitter-blue);
}

.conversation-avatar {
    margin-right: 12px;
}

.conversation-avatar img,
.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 4px;
}

.conversation-preview {
    font-size: 14px;
    color: var(--twitter-gray);
}

.last-message {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.no-messages {
    font-style: italic;
}

.conversation-meta {
    text-align: right;
    font-size: 12px;
    color: var(--twitter-gray);
}

.last-time {
    margin-bottom: 4px;
}

.unread-badge {
    background: var(--twitter-blue);
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(29, 161, 242, 0.3);
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--twitter-gray);
}

.chat-placeholder i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.chat-placeholder h3 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: var(--twitter-dark);
}

.chat-placeholder p {
    margin: 0;
    font-size: 16px;
}

.no-conversations {
    padding: 40px 20px;
    text-align: center;
    color: var(--twitter-gray);
}

.no-conversations i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.no-conversations p {
    margin: 8px 0;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--twitter-dark);
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--twitter-gray);
    transition: color 0.2s ease;
}

.close-btn:hover {
    color: var(--twitter-dark);
}

.modal-body {
    padding: 20px;
}

.search-input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: 16px;
    font-size: 16px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    margin-bottom: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-input:focus {
    border-color: var(--focus-border);
    background: var(--card-bg);
    box-shadow: 0 0 0 4px rgba(29, 161, 242, 0.15);
    transform: translateY(-1px);
}

.user-results {
    max-height: 300px;
    overflow-y: auto;
}

.user-result-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
}

.user-result-item:hover {
    background: var(--hover-bg);
    transform: translateX(2px);
}

.user-result-item img,
.user-result-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 12px;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.user-result-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
}

.user-result-name {
    font-weight: 500;
    color: var(--twitter-dark);
}

/* Followers Section */
.followers-section {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
}

.followers-section h3 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--twitter-gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.followers-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.follower-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.follower-item:hover {
    background: var(--hover-bg);
    transform: translateX(2px);
}

.follower-item img,
.follower-item .avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid var(--border-color);
}

.follower-item .avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
    font-size: 12px;
}

.follower-item span {
    font-size: 14px;
    font-weight: 500;
    color: var(--twitter-dark);
    flex: 1;
}

.no-followers {
    text-align: center;
    color: var(--twitter-gray);
    font-size: 12px;
    margin: 8px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .chat-sidebar {
        width: 100%;
        position: absolute;
        z-index: 10;
    }

    .chat-main {
        display: none;
    }

    .modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>

<script>
function showUserSearch() {
    document.getElementById('userSearchModal').style.display = 'flex';
    document.getElementById('userSearch').focus();
}

function hideUserSearch() {
    document.getElementById('userSearchModal').style.display = 'none';
    document.getElementById('userSearch').value = '';
    document.getElementById('userResults').innerHTML = '';
}

// Close modal when clicking outside
document.getElementById('userSearchModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideUserSearch();
    }
});

// Search users
document.getElementById('userSearch').addEventListener('input', function() {
    const query = this.value.trim();
    const resultsDiv = document.getElementById('userResults');

    if (query.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }

    fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultsDiv.innerHTML = data.users.map(user => `
                <div class="user-result-item" onclick="startChat(${user.id})">
                    ${user.avatar ?
                        `<img src="/storage/${user.avatar}" alt="Avatar">` :
                        `<div class="user-result-placeholder"><i class="fas fa-user"></i></div>`
                    }
                    <div class="user-result-name">${user.name}</div>
                </div>
            `).join('');
        }
    })
    .catch(error => console.error('Search error:', error));
});

function startChat(userId) {
    window.location.href = `/chat/start/${userId}`;
}

function startChatWithUser(userId) {
    window.location.href = `/chat/start/${userId}`;
}

// Auto-update conversations list
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing chat index auto-update');

    // Start polling for conversation updates
    startConversationsPolling();

    // Focus on search input if modal is shown
    const modal = document.getElementById('userSearchModal');
    if (modal) {
        modal.addEventListener('shown', function() {
            document.getElementById('userSearch').focus();
        });
    }
});

function startConversationsPolling() {
    // Check for conversation updates every 5 seconds
    setInterval(() => {
        fetch(`{{ route('chat.conversations') }}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.conversations) {
                updateConversationsList(data.conversations);
            }
        })
        .catch(error => {
            console.error('Error polling conversations:', error);
        });
    }, 5000); // Poll every 5 seconds
}

function updateConversationsList(conversations) {
    const conversationsList = document.querySelector('.conversations-list');
    if (!conversationsList) return;

    // Get existing conversation IDs
    const existingConversationIds = Array.from(document.querySelectorAll('[data-conversation-id]'))
        .map(el => parseInt(el.getAttribute('data-conversation-id')));

    // Update existing conversations and add new ones
    conversations.forEach(conversation => {
        const existingElement = document.querySelector(`[data-conversation-id="${conversation.id}"]`);

        if (existingElement) {
            // Update existing conversation
            updateConversationElement(existingElement, conversation);
        } else {
            // Add new conversation
            const conversationHtml = createConversationHtml(conversation);
            conversationsList.insertBefore(conversationHtml, conversationsList.firstChild);
        }
    });

    // Reorder conversations by last_message_at (most recent first)
    reorderConversations(conversations);
}

function updateConversationElement(element, conversation) {
    // Update unread badge
    const unreadBadge = element.querySelector('.unread-badge');
    if (conversation.unread_count > 0) {
        element.classList.add('unread');
        if (unreadBadge) {
            unreadBadge.textContent = conversation.unread_count;
        } else {
            // Add unread badge if it doesn't exist
            const metaDiv = element.querySelector('.conversation-meta');
            if (metaDiv) {
                metaDiv.insertAdjacentHTML('beforeend', `<div class="unread-badge">${conversation.unread_count}</div>`);
            }
        }
    } else {
        element.classList.remove('unread');
        if (unreadBadge) {
            unreadBadge.remove();
        }
    }

    // Update last message preview
    const previewElement = element.querySelector('.conversation-preview');
    if (previewElement && conversation.latest_message) {
        const senderPrefix = conversation.latest_message.sender_id === {{ auth()->id() }} ? 'You: ' : '';
        const messageText = conversation.latest_message.content.length > 30
            ? conversation.latest_message.content.substring(0, 30) + '...'
            : conversation.latest_message.content;

        previewElement.innerHTML = `<span class="last-message">${senderPrefix}${messageText}</span>`;
    }

    // Update last message time
    const timeElement = element.querySelector('.last-time');
    if (timeElement && conversation.last_message_at) {
        timeElement.textContent = formatTimeAgo(conversation.last_message_at);
    }
}

function createConversationHtml(conversation) {
    const isUnread = conversation.unread_count > 0;
    const senderPrefix = conversation.latest_message && conversation.latest_message.sender_id === {{ auth()->id() }} ? 'You: ' : '';
    const messageText = conversation.latest_message
        ? (conversation.latest_message.content.length > 30
            ? conversation.latest_message.content.substring(0, 30) + '...'
            : conversation.latest_message.content)
        : 'No messages yet';
    const timeAgo = conversation.last_message_at ? formatTimeAgo(conversation.last_message_at) : '';

    const conversationDiv = document.createElement('a');
    conversationDiv.className = `conversation-item ${isUnread ? 'unread' : ''}`;
    conversationDiv.href = `/chat/${conversation.id}`;
    conversationDiv.setAttribute('data-conversation-id', conversation.id);

    conversationDiv.innerHTML = `
        <div class="conversation-avatar">
            ${conversation.other_user.avatar ?
                `<img src="/storage/${conversation.other_user.avatar}" alt="Avatar">` :
                `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`
            }
        </div>
        <div class="conversation-info">
            <div class="conversation-name">${conversation.other_user.name}</div>
            <div class="conversation-preview">
                ${conversation.latest_message ?
                    `<span class="last-message">${senderPrefix}${messageText}</span>` :
                    `<span class="no-messages">No messages yet</span>`
                }
            </div>
        </div>
        <div class="conversation-meta">
            ${timeAgo ? `<div class="last-time">${timeAgo}</div>` : ''}
            ${isUnread ? `<div class="unread-badge">${conversation.unread_count}</div>` : ''}
        </div>
    `;

    return conversationDiv;
}

function reorderConversations(conversations) {
    const conversationsList = document.querySelector('.conversations-list');
    if (!conversationsList) return;

    // Sort conversations by last_message_at (most recent first)
    const conversationElements = Array.from(conversationsList.querySelectorAll('.conversation-item'));
    conversationElements.sort((a, b) => {
        const aId = parseInt(a.getAttribute('data-conversation-id'));
        const bId = parseInt(b.getAttribute('data-conversation-id'));

        const aConversation = conversations.find(c => c.id === aId);
        const bConversation = conversations.find(c => c.id === bId);

        const aTime = aConversation?.last_message_at ? new Date(aConversation.last_message_at).getTime() : 0;
        const bTime = bConversation?.last_message_at ? new Date(bConversation.last_message_at).getTime() : 0;

        return bTime - aTime; // Most recent first
    });

    // Re-append elements in correct order
    conversationElements.forEach(element => {
        conversationsList.appendChild(element);
    });
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

    return date.toLocaleDateString();
}
</script>
@endsection
