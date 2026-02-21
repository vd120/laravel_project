@extends('layouts.app')

@section('title', 'Chat with ' . $conversation->other_user->name)

@section('content')
<div class="chat-page">
    <div class="chat-container">
        <!-- Minimized Sidebar - Only visible when toggled -->
        <div class="chat-sidebar minimized">
            <div class="chat-header">
                <button class="back-btn" onclick="window.location.href='{{ route('chat.index') }}'">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2>Messages</h2>
                <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle conversations">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="conversations-list">
                @php
                    $conversations = \App\Models\Conversation::where('user1_id', auth()->id())
                        ->orWhere('user2_id', auth()->id())
                        ->with(['user1', 'user2', 'latestMessage.sender'])
                        ->orderBy('last_message_at', 'desc')
                        ->get();
                @endphp

                @forelse($conversations as $conv)
                <a href="{{ route('chat.show', $conv) }}" class="conversation-item {{ $conv->id === $conversation->id ? 'active' : '' }} {{ $conv->unread_count > 0 ? 'unread' : '' }}">
                    <div class="conversation-avatar">
                        @if($conv->other_user->profile && $conv->other_user->profile->avatar)
                            <img src="{{ asset('storage/' . $conv->other_user->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>

                    <div class="conversation-info">
                        <div class="conversation-name">{{ $conv->other_user->name }}</div>
                        <div class="conversation-preview">
                            @if($conv->latestMessage)
                                <span class="last-message">
                                    {{ $conv->latestMessage->sender_id === auth()->id() ? 'You: ' : '' }}
                                    {{ Str::limit($conv->latestMessage->content, 30) }}
                                </span>
                            @else
                                <span class="no-messages">No messages yet</span>
                            @endif
                        </div>
                    </div>

                    <div class="conversation-meta">
                        @if($conv->last_message_at)
                            <div class="last-time">{{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() }}</div>
                        @endif
                        @if($conv->unread_count > 0)
                            <div class="unread-badge">{{ $conv->unread_count }}</div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="no-conversations">
                    <i class="fas fa-comments"></i>
                    <p>No conversations yet</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="chat-main">
            <div class="chat-header-main">
                <button class="sidebar-toggle-main" onclick="window.location.href='{{ route('chat.index') }}'" title="Back to conversations">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="chat-user-info">
                    <div class="chat-avatar">
                        @if($conversation->other_user->profile && $conversation->other_user->profile->avatar)
                            <img src="{{ asset('storage/' . $conversation->other_user->profile->avatar) }}" alt="Avatar">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="chat-user-details">
                        <h3>{{ $conversation->other_user->name }}</h3>
                        <span class="user-status"><i class="fas fa-circle" style="font-size: 10px; color: var(--text-muted);"></i> Loading...</span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="clear-chat-btn" onclick="clearChat()" title="Delete all messages in this chat">
                        <i class="fas fa-trash-alt"></i>
                        Clear Chat
                    </button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                @forelse($messages as $message)
                <div class="message {{ $message->is_mine ? 'own' : 'other' }} {{ $message->trashed() ? 'deleted' : '' }}" data-message-id="{{ $message->id }}">
                    <div class="message-content">
                        @if($message->trashed())
                            <em>message deleted</em>
                        @else
                            @if($message->type === 'image' && $message->media_path)
                                <div class="message-media">
                                    <img src="{{ asset('storage/' . $message->media_path) }}" alt="Image" class="message-image" onclick="openMediaViewer(this.src)">
                                </div>
                            @elseif($message->type === 'video' && $message->media_path)
                                <div class="message-media">
                                    <video src="{{ asset('storage/' . $message->media_path) }}" controls class="message-video"></video>
                                </div>
                            @endif
                            @if($message->content)
                                {{ $message->content }}
                            @endif
                        @endif
                    </div>
                    <div class="message-meta">
                        <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                        @if($message->is_mine && !$message->trashed())
                        <button class="message-delete" onclick="deleteMessage({{ $message->id }})" title="Delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="no-messages-chat">
                    <p>Start a conversation with {{ $conversation->other_user->name }}</p>
                </div>
                @endforelse
            </div>

            <!-- Media Viewer Modal -->
            <div id="mediaViewer" class="media-viewer" onclick="closeMediaViewer()">
                <button class="media-viewer-close" onclick="closeMediaViewer()">
                    <i class="fas fa-times"></i>
                </button>
                <img id="mediaViewerImage" src="" alt="Full size image">
            </div>

            <div class="chat-input">
                <form id="messageForm" onsubmit="sendMessage(event)">
                    <!-- Media preview area -->
                    <div id="mediaPreview" class="media-preview" style="display: none;">
                        <div class="media-preview-container">
                            <img id="imagePreview" src="" alt="Preview" style="display: none;">
                            <video id="videoPreview" controls style="display: none;"></video>
                            <button type="button" class="remove-media-btn" onclick="clearMediaPreview()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="input-row">
                        <label for="mediaInput" class="media-btn" title="Attach photo or video">
                            <i class="fas fa-image"></i>
                        </label>
                        <input type="file" id="mediaInput" accept="image/*,video/*" onchange="handleMediaSelect(event)" style="display: none;">
                        <input type="text" id="messageInput" placeholder="Type a message..." maxlength="1000">
                        <button type="submit" id="sendButton" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.chat-page {
    height: 100vh;
    background: var(--twitter-light);
    /* Make main header static on chat pages - use calc to account for header */
    padding-top: 64px;
    box-sizing: border-box;
}

/* Make the main website header static on chat pages */
.chat-page ~ header,
.chat-page header,
header {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 10000 !important;
    background: var(--card-bg) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border-bottom: 1px solid var(--border-color) !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important;
}

.chat-container {
    display: flex;
    height: 100%;
    width: 100%;
}

.chat-sidebar {
    width: min(350px, 25vw);
    min-width: 280px;
    background: var(--card-bg);
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 1001;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
}

.chat-sidebar.open {
    transform: translateX(0);
}

.chat-sidebar.minimized {
    display: none;
}

.chat-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-btn {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.back-btn:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.chat-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--twitter-dark);
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
    background: var(--card-bg);
}

.chat-header-main {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--card-bg);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-toggle-main {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.sidebar-toggle-main:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 16px;
    color: var(--twitter-gray);
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.sidebar-toggle:hover {
    background: var(--hover-bg);
    color: var(--twitter-dark);
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-actions {
    display: flex;
    gap: 12px;
}

.clear-chat-btn {
    background: var(--error-color);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.3);
}

.clear-chat-btn:hover {
    background: #E0245E;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(244, 33, 46, 0.4);
}

.clear-chat-btn i {
    font-size: 12px;
}

.chat-avatar {
    width: 40px;
    height: 40px;
}

.chat-avatar img,
.chat-avatar .avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.chat-avatar .avatar-placeholder {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
}

.chat-user-details h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.user-status {
    font-size: 12px;
    color: var(--success-color);
}

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: var(--twitter-light);
        min-height: 0; /* Allow flex shrinking */
    }

.message {
    display: flex;
    flex-direction: column;
    max-width: 70%;
    animation: messageSlideIn 0.3s ease-out;
    position: relative;
}

/* Limit message width on very wide screens for better readability */
@media (min-width: 1200px) {
    .message {
        max-width: 60%;
    }
}

@media (min-width: 1600px) {
    .message {
        max-width: 50%;
    }
}

@media (min-width: 2000px) {
    .message {
        max-width: 40%;
    }
}

.message.own {
    align-self: flex-end;
    align-items: flex-end;
}

.message.other {
    align-self: flex-start;
    align-items: flex-start;
}

.message-delete {
    background: transparent;
    color: rgba(255, 255, 255, 0.6);
    border: none;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 11px;
    opacity: 0.6;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
    border-radius: 4px;
}

.message-delete:hover {
    opacity: 1;
    color: #ff4757;
    background: rgba(255, 71, 87, 0.1);
}

.message-delete i {
    font-size: 10px;
}

/* For other user's messages */
.message.other .message-delete {
    color: rgba(0, 0, 0, 0.5);
}

.message.other .message-delete:hover {
    color: #ff4757;
}

.message-content {
    background: var(--twitter-blue);
    color: white;
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.4;
    box-shadow: 0 2px 8px rgba(29, 161, 242, 0.2);
}

.message.own .message-content {
    background: var(--twitter-blue);
    border-bottom-right-radius: 4px;
}

.message.other .message-content {
    background: var(--card-bg);
    color: var(--twitter-dark);
    border: 1px solid var(--border-color);
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message.deleted .message-content {
    background: var(--hover-bg);
    color: var(--twitter-gray);
    font-style: italic;
    opacity: 0.7;
    border: 1px solid var(--border-color);
}

.message.deleted .message-content em {
    font-style: italic;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.message-time {
    font-size: 11px;
    color: var(--twitter-gray);
}

.no-messages-chat {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--twitter-gray);
}

.no-messages-chat p {
    margin: 0;
    font-size: 16px;
    color: var(--twitter-dark);
}

.chat-input {
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    background: var(--card-bg);
}

.chat-input form {
    display: flex;
    gap: 12px;
    align-items: center;
}

#messageInput {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: 25px;
    font-size: 14px;
    background: var(--input-bg);
    color: var(--twitter-dark);
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#messageInput:focus {
    border-color: var(--focus-border);
    background: var(--card-bg);
    box-shadow: 0 0 0 4px rgba(29, 161, 242, 0.15);
    transform: translateY(-1px);
}

.send-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: var(--twitter-blue);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);
}

.send-btn:hover {
    background: #1A91DA;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(29, 161, 242, 0.4);
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}



/* Enhanced Responsive Design */
@media (max-width: 1024px) {
    .chat-sidebar {
        width: 320px;
    }

    .chat-header-main {
        padding: 14px 16px;
    }

    .chat-user-info {
        gap: 10px;
    }

    .chat-user-details h3 {
        font-size: 15px;
    }

    .chat-actions {
        gap: 10px;
    }

    .clear-chat-btn {
        padding: 6px 14px;
        font-size: 13px;
    }

    .clear-chat-btn i {
        font-size: 11px;
    }
}

@media (max-width: 768px) {
    .chat-page {
        height: 100vh;
        padding-top: 56px;
    }

    .chat-container {
        flex-direction: column;
        height: calc(100vh - 56px);
    }

    .chat-sidebar {
        display: none;
        position: fixed;
        top: 56px;
        left: 0;
        width: 100%;
        height: calc(100vh - 56px);
        z-index: 1000;
        background: var(--card-bg);
        border-right: none;
        border-top: 1px solid var(--border-color);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .chat-sidebar.mobile-open {
        transform: translateX(0);
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* FIXED HEADER - Always below main header */
    .chat-header-main {
        position: fixed !important;
        top: 56px !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 9998 !important;
        padding: 12px 16px;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        flex-shrink: 0 !important;
        min-height: 56px;
        width: 100% !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important;
    }

    .chat-user-info {
        margin-left: 48px;
        gap: 10px;
    }

    .chat-user-details h3 {
        font-size: 16px;
        font-weight: 600;
    }

    .user-status {
        font-size: 12px;
    }

    .chat-actions {
        margin-left: auto;
        gap: 8px;
    }

    .clear-chat-btn {
        padding: 6px 12px;
        font-size: 12px;
        min-height: 32px;
    }

    .chat-messages {
        flex: 1;
        padding: 12px 16px;
        overflow-y: auto;
        background: var(--twitter-light);
        margin-top: 56px;
        height: calc(100vh - 180px);
        min-height: 200px;
    }

    .message {
        max-width: 85%;
        margin-bottom: 8px;
    }

    .message-content {
        font-size: 14px;
        padding: 10px 14px;
        border-radius: 16px;
    }

    .message-time {
        font-size: 10px;
        margin-top: 2px;
    }

    .message-delete {
        width: 20px;
        height: 20px;
        font-size: 8px;
    }

    .chat-input {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        padding: 12px 16px !important;
        padding-bottom: max(12px, env(safe-area-inset-bottom)) !important;
        border-top: 1px solid var(--border-color) !important;
        background: var(--card-bg) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        z-index: 9999 !important;
        box-shadow: 0 -6px 20px rgba(0,0,0,0.15) !important;
        width: 100% !important;
    }

    #messageInput {
        padding: 12px 16px;
        font-size: 16px;
        border-radius: 20px;
    }

    #messageInput:focus {
        border-color: var(--focus-border);
    }

    .send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    .back-btn {
        display: none;
    }

    .no-messages-chat {
        padding: 40px 20px;
        text-align: center;
    }

    .no-messages-chat p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .chat-page {
        height: calc(100vh - 56px);
    }

    .chat-sidebar {
        top: 56px;
        height: calc(100vh - 56px);
    }

    .chat-header-main {
        padding: 10px 12px;
    }

    .chat-header-main::before {
        left: 12px;
        width: 28px;
        height: 28px;
    }

    .chat-user-info {
        margin-left: 40px;
    }

    .chat-user-details h3 {
        font-size: 15px;
    }

    .chat-actions {
        gap: 6px;
    }

    .clear-chat-btn {
        padding: 5px 10px;
        font-size: 11px;
    }

    .chat-messages {
        padding: 10px 12px;
    }

    .message {
        max-width: 90%;
        margin-bottom: 6px;
    }

    .message-content {
        font-size: 13px;
        padding: 8px 12px;
    }

    .message-time {
        font-size: 9px;
    }

    .chat-input {
        padding: 10px 12px;
    }

    #messageInput {
        padding: 10px 14px;
        font-size: 16px;
    }

    .send-btn {
        width: 36px;
        height: 36px;
    }

    .no-messages-chat {
        padding: 30px 15px;
    }

    .no-messages-chat p {
        font-size: 13px;
    }
}

@media (max-width: 360px) {
    .chat-header-main {
        padding: 8px 10px;
    }

    .chat-header-main::before {
        left: 10px;
        width: 24px;
        height: 24px;
    }

    .chat-user-info {
        margin-left: 34px;
    }

    .chat-user-details h3 {
        font-size: 14px;
    }

    .chat-actions {
        gap: 4px;
    }

    .clear-chat-btn {
        padding: 4px 8px;
        font-size: 10px;
    }

    .chat-messages {
        padding: 8px 10px;
    }

    .message {
        max-width: 95%;
    }

    .message-content {
        font-size: 12px;
        padding: 6px 10px;
    }

    .chat-input {
        padding: 8px 10px;
    }

    #messageInput {
        padding: 8px 12px;
    }

    .send-btn {
        width: 32px;
        height: 32px;
    }
}

/* Touch-friendly interactions for mobile */
@media (hover: none) and (pointer: coarse) {
    .conversation-item {
        min-height: 60px;
        padding: 12px 16px;
    }

    .message:hover .message-delete {
        display: none; /* Disable hover effects on touch devices */
    }

    .message-delete {
        display: block; /* Always show delete button on touch devices */
        opacity: 0.7;
    }

    .clear-chat-btn:hover {
        transform: none;
    }

    .send-btn:hover {
        transform: none;
    }
}

/* Landscape orientation adjustments */
@media (max-height: 500px) and (orientation: landscape) {
    .chat-sidebar {
        height: 100vh;
        top: 0;
    }

    .chat-header-main {
        padding: 8px 12px;
    }

    .chat-messages {
        padding: 8px 12px;
        max-height: calc(100vh - 120px);
    }

    .chat-input {
        padding: 8px 12px;
    }

    .message {
        max-width: 80%;
        margin-bottom: 4px;
    }

    .message-content {
        font-size: 13px;
        padding: 6px 10px;
    }
}

.no-conversations {
    padding: 40px 20px;
    text-align: center;
    color: #6c757d;
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

/* Media Upload Styles */
.input-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
}

.media-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    color: var(--twitter-blue);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 20px;
}

.media-btn:hover {
    background: rgba(29, 161, 242, 0.1);
    transform: scale(1.05);
}

.media-preview {
    margin-bottom: 12px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--hover-bg);
}

.media-preview-container {
    position: relative;
    display: inline-block;
    max-width: 200px;
    max-height: 200px;
}

.media-preview-container img,
.media-preview-container video {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    object-fit: cover;
}

.remove-media-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border: none;
    background: var(--error-color);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.remove-media-btn:hover {
    background: #E0245E;
    transform: scale(1.1);
}

/* Message Media Styles */
.message-media {
    margin-bottom: 8px;
    border-radius: 12px;
    overflow: hidden;
}

.message-image {
    max-width: 100%;
    max-height: 300px;
    min-width: 150px;
    cursor: pointer;
    border-radius: 12px;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.message-image:hover {
    transform: scale(1.02);
}

.message-video {
    max-width: 100%;
    max-height: 300px;
    min-width: 200px;
    border-radius: 12px;
    background: #000;
}

/* Media Viewer Modal */
.media-viewer {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.media-viewer.active {
    display: flex;
}

.media-viewer-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 44px;
    height: 44px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.2s ease;
}

.media-viewer-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.media-viewer img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 8px;
}

/* Media upload progress */
.upload-progress {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.upload-progress-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Mobile styles for media */
@media (max-width: 768px) {
    .media-btn {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .media-preview-container {
        max-width: 150px;
        max-height: 150px;
    }

    .media-preview-container img,
    .media-preview-container video {
        max-width: 150px;
        max-height: 150px;
    }

    .message-image,
    .message-video {
        max-height: 250px;
    }

    .media-viewer-close {
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
    }
}

@media (max-width: 480px) {
    .media-btn {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .media-preview-container {
        max-width: 120px;
        max-height: 120px;
    }

    .media-preview-container img,
    .media-preview-container video {
        max-width: 120px;
        max-height: 120px;
    }

    .message-image,
    .message-video {
        max-height: 200px;
        min-width: 120px;
    }
}
</style>

<script>
// Initialize polling for new messages
document.addEventListener('DOMContentLoaded', function() {
    console.log('Real-time messaging using polling');

    // Update online status when user is active
    updateOnlineStatus();
    setInterval(updateOnlineStatus, 3000); // Update every 3 seconds

    // Start polling for new messages
    startMessagePolling();

    // Scroll to bottom with a slight delay for mobile rendering
    setTimeout(() => {
        scrollToBottom(false); // Immediate scroll on load for mobile
    }, 100);

    // Focus on input only on desktop (avoid auto-zoom on mobile)
    const messageInput = document.getElementById('messageInput');
    const isMobile = window.innerWidth <= 768;
    if (messageInput && !isMobile) {
        messageInput.focus();
    }

    // Add mobile menu toggle functionality to the header (pseudo-element can't be selected directly)
    const chatHeader = document.querySelector('.chat-header-main');
    if (chatHeader) {
        // Check if click is within the menu button area (left side)
        chatHeader.addEventListener('click', function(event) {
            const rect = chatHeader.getBoundingClientRect();
            const clickX = event.clientX - rect.left;

            // Menu button area is approximately the left 48px
            if (clickX <= 48 && window.innerWidth <= 768) {
                event.preventDefault();
                toggleMobileSidebar();
                return;
            }

            // Let other clicks pass through normally
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.chat-sidebar');
        const chatHeader = document.querySelector('.chat-header-main');

        if (window.innerWidth <= 768 &&
            sidebar &&
            sidebar.classList.contains('mobile-open') &&
            !sidebar.contains(event.target) &&
            !chatHeader.contains(event.target)) {
            toggleMobileSidebar();
        }
    });
});

function sendMessage(event) {
    event.preventDefault();

    const input = document.getElementById('messageInput');
    const content = input.value.trim();

    if (!content) {
        input.focus();
        return;
    }

    // Disable input while sending
    input.disabled = true;
    document.getElementById('sendButton').disabled = true;

    fetch(`{{ route('chat.store', $conversation) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add message immediately to chat
            addMessageToChat({
                id: data.message.id,
                content: data.message.content,
                created_at: new Date().toISOString(),
                user: {
                    id: {{ auth()->id() }},
                    name: '{{ auth()->user()->name }}',
                    avatar: '{{ auth()->user()->profile?->avatar }}'
                }
            });
            input.value = '';
        } else {
            alert('Failed to send message. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message. Please try again.');
    })
    .finally(() => {
        input.disabled = false;
        document.getElementById('sendButton').disabled = false;
        // Focus input only on desktop after sending (avoid mobile keyboard)
        const isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            input.focus();
        }
    });
}

function addMessageToChat(event) {
    const messagesContainer = document.getElementById('chatMessages');

    const messageDiv = document.createElement('div');
    const isOwnMessage = event.user.id === {{ auth()->id() }};
    messageDiv.className = `message ${isOwnMessage ? 'own' : 'other'}`;
    messageDiv.setAttribute('data-message-id', event.id);

    let deleteButtonHtml = '';
    if (isOwnMessage) {
        deleteButtonHtml = `<button class="message-delete" onclick="deleteMessage(${event.id})" title="Delete">
            <i class="fas fa-trash"></i> Delete
        </button>`;
    }

    messageDiv.innerHTML = `
        <div class="message-content">${event.content}</div>
        <div class="message-meta">
            <span class="message-time">${new Date(event.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            ${deleteButtonHtml}
        </div>
    `;

    messagesContainer.appendChild(messageDiv);
    scrollToBottom();
}

function scrollToBottom(smooth = true) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;

    const scrollOptions = smooth ? { behavior: 'smooth', block: 'end', inline: 'nearest' } : { block: 'end', inline: 'nearest' };

    // Use modern scrollIntoView for better mobile support
    const lastMessage = messagesContainer.lastElementChild;
    if (lastMessage) {
        lastMessage.scrollIntoView(scrollOptions);
    } else {
        // Fallback to traditional scroll
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

function startMessagePolling() {
    // Check for new messages and message updates every 3 seconds
    setInterval(() => {
        fetch(`{{ route('chat.messages', $conversation) }}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Handle new messages
                if (data.messages && data.messages.length > 0) {
                    console.log('New messages received:', data.messages.length);

                    const existingMessageIds = Array.from(document.querySelectorAll('[data-message-id]'))
                        .map(el => parseInt(el.getAttribute('data-message-id')));

                    data.messages.forEach(message => {
                        if (!existingMessageIds.includes(message.id)) {
                            console.log('Adding new message:', message.id);
                            addMessageToChat({
                                id: message.id,
                                content: message.content,
                                created_at: message.created_at,
                                user: message.sender
                            });
                        }
                    });

                    // Mark messages as read after displaying them
                    markMessagesAsRead();
                }

                // Handle message updates (deletions, etc.)
                if (data.message_updates && data.message_updates.length > 0) {
                    console.log('Checking for message updates...');

                    data.message_updates.forEach(update => {
                        const messageElement = document.querySelector(`[data-message-id="${update.id}"]`);
                        if (messageElement && update.deleted) {
                            // Message has been deleted - remove from UI completely
                            console.log('Removing deleted message:', update.id);
                            messageElement.remove();
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error polling messages:', error);
        });
    }, 3000); // Poll every 3 seconds
}

function markMessagesAsRead() {
    fetch(`{{ route('chat.mark-read', $conversation) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Messages marked as read');
        }
    })
    .catch(error => {
        console.error('Error marking messages as read:', error);
    });
}

// Handle enter key to send message
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(e);
    }
});

function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }

    fetch(`/chat/message/${messageId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the message to show "message deleted"
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
            if (messageElement) {
                messageElement.classList.add('deleted');
                const contentElement = messageElement.querySelector('.message-content');
                const deleteButton = messageElement.querySelector('.message-delete');
                if (contentElement) {
                    contentElement.innerHTML = '<em>message deleted</em>';
                }
                if (deleteButton) {
                    deleteButton.remove();
                }
                console.log('Message deleted successfully');
            }
        } else {
            alert('Failed to delete message');
        }
    })
    .catch(error => {
        console.error('Error deleting message:', error);
        alert('Error deleting message');
    });
}

function toggleMobileSidebar() {
    const sidebar = document.querySelector('.chat-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}

function toggleSidebar() {
    const sidebar = document.querySelector('.chat-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

function clearChat() {
    if (!confirm('Are you sure you want to delete ALL messages in this chat? This will remove messages from both you and the other person. This action cannot be undone.')) {
        return;
    }

    fetch(`{{ route('chat.clear', $conversation) }}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove ALL messages from the chat
            const messagesContainer = document.getElementById('chatMessages');
            const allMessages = messagesContainer.querySelectorAll('.message');

            allMessages.forEach(message => {
                message.remove();
            });

            console.log('Full chat cleared successfully');
            // Show success toast
            if (typeof showToast === 'function') {
                showToast('Chat cleared successfully! All messages have been deleted.', 'success');
            } else {
                alert('Chat cleared successfully! All messages have been deleted.');
            }
        } else {
            alert('Failed to clear chat');
        }
    })
    .catch(error => {
        console.error('Error clearing chat:', error);
        alert('Error clearing chat');
    });
}

// Update user's online status
function updateOnlineStatus() {
    fetch('/user/update-online-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Online status updated');
        }
    })
    .catch(error => {
        console.error('Error updating online status:', error);
    });
}

// Poll other user's online status
function startOnlineStatusPolling() {
    setInterval(() => {
        fetch('/user/{{ $conversation->other_user->name }}/online-status', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOnlineIndicator(data.is_online);
            }
        })
        .catch(error => {
            console.error('Error fetching online status:', error);
        });
    }, 3000); // Check every 3 seconds
}

function updateOnlineIndicator(isOnline) {
    const statusElement = document.querySelector('.user-status');
    if (statusElement) {
        if (isOnline) {
            statusElement.innerHTML = '<span style="display: inline-block; width: 8px; height: 8px; background: #22c55e; border-radius: 50%; margin-right: 6px;"></span>Online';
            statusElement.style.color = '#22c55e';
        } else {
            statusElement.textContent = 'Offline';
            statusElement.style.color = 'var(--text-muted)';
        }
    }
}

// Start polling for other user's online status
startOnlineStatusPolling();

// ============ Media Upload Functions ============

let selectedMediaFile = null;

function handleMediaSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Check file size (50MB max)
    const maxSize = 50 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('File size must be less than 50MB');
        event.target.value = '';
        return;
    }

    selectedMediaFile = file;

    const mediaPreview = document.getElementById('mediaPreview');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');

    // Show preview
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            videoPreview.style.display = 'none';
            mediaPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else if (file.type.startsWith('video/')) {
        const url = URL.createObjectURL(file);
        videoPreview.src = url;
        videoPreview.style.display = 'block';
        imagePreview.style.display = 'none';
        mediaPreview.style.display = 'block';
    }
}

function clearMediaPreview() {
    selectedMediaFile = null;
    const mediaPreview = document.getElementById('mediaPreview');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const mediaInput = document.getElementById('mediaInput');

    mediaPreview.style.display = 'none';
    imagePreview.style.display = 'none';
    videoPreview.style.display = 'none';
    imagePreview.src = '';
    videoPreview.src = '';
    mediaInput.value = '';
}

function openMediaViewer(src) {
    const viewer = document.getElementById('mediaViewer');
    const image = document.getElementById('mediaViewerImage');
    image.src = src;
    viewer.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMediaViewer() {
    const viewer = document.getElementById('mediaViewer');
    viewer.classList.remove('active');
    document.body.style.overflow = '';
}

// Override sendMessage to support media
const originalSendMessage = sendMessage;
sendMessage = function(event) {
    event.preventDefault();

    const input = document.getElementById('messageInput');
    const content = input.value.trim();

    // Check if there's content or media to send
    if (!content && !selectedMediaFile) {
        input.focus();
        return;
    }

    // If there's media, use FormData
    if (selectedMediaFile) {
        sendMediaMessage(content, selectedMediaFile);
        return;
    }

    // Otherwise, send text message using the original function
    sendTextMessage(content);
};

function sendTextMessage(content) {
    const input = document.getElementById('messageInput');

    // Disable input while sending
    input.disabled = true;
    document.getElementById('sendButton').disabled = true;

    fetch(`{{ route('chat.store', $conversation) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessageToChat({
                id: data.message.id,
                content: data.message.content,
                created_at: new Date().toISOString(),
                type: data.message.type,
                media_path: data.message.media_path,
                user: {
                    id: {{ auth()->id() }},
                    name: '{{ auth()->user()->name }}',
                    avatar: '{{ auth()->user()->profile?->avatar }}'
                }
            });
            input.value = '';
        } else {
            alert('Failed to send message. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message. Please try again.');
    })
    .finally(() => {
        input.disabled = false;
        document.getElementById('sendButton').disabled = false;
        const isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            input.focus();
        }
    });
}

function sendMediaMessage(content, mediaFile) {
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    // Disable inputs while sending
    input.disabled = true;
    sendButton.disabled = true;

    const formData = new FormData();
    if (content) {
        formData.append('content', content);
    }
    formData.append('media', mediaFile);

    fetch(`{{ route('chat.store', $conversation) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessageToChat({
                id: data.message.id,
                content: data.message.content || '',
                created_at: new Date().toISOString(),
                type: data.message.type,
                media_path: data.message.media_path,
                user: {
                    id: {{ auth()->id() }},
                    name: '{{ auth()->user()->name }}',
                    avatar: '{{ auth()->user()->profile?->avatar }}'
                }
            });
            input.value = '';
            clearMediaPreview();
        } else {
            alert(data.error || 'Failed to send message. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message. Please try again.');
    })
    .finally(() => {
        input.disabled = false;
        sendButton.disabled = false;
        const isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            input.focus();
        }
    });
}

// Override addMessageToChat to support media
const originalAddMessageToChat = addMessageToChat;
addMessageToChat = function(event) {
    const messagesContainer = document.getElementById('chatMessages');

    const messageDiv = document.createElement('div');
    const isOwnMessage = event.user.id === {{ auth()->id() }};
    messageDiv.className = `message ${isOwnMessage ? 'own' : 'other'}`;
    messageDiv.setAttribute('data-message-id', event.id);

    let deleteButtonHtml = '';
    if (isOwnMessage) {
        deleteButtonHtml = `<button class="message-delete" onclick="deleteMessage(${event.id})" title="Delete">
            <i class="fas fa-trash"></i> Delete
        </button>`;
    }

    // Build media HTML if present
    let mediaHtml = '';
    if (event.type === 'image' && event.media_path) {
        mediaHtml = `<div class="message-media">
            <img src="/storage/${event.media_path}" alt="Image" class="message-image" onclick="openMediaViewer(this.src)">
        </div>`;
    } else if (event.type === 'video' && event.media_path) {
        mediaHtml = `<div class="message-media">
            <video src="/storage/${event.media_path}" controls class="message-video"></video>
        </div>`;
    }

    messageDiv.innerHTML = `
        <div class="message-content">
            ${mediaHtml}
            ${event.content ? event.content : ''}
        </div>
        <div class="message-meta">
            <span class="message-time">${new Date(event.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            ${deleteButtonHtml}
        </div>
    `;

    messagesContainer.appendChild(messageDiv);
    scrollToBottom();
};

// Close media viewer on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMediaViewer();
    }
});
</script>
@endsection