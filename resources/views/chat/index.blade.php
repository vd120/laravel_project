@extends('layouts.app')

@section('title', __('chat.messages'))

@section('content')
<style>
/* Override layout constraints for full width chat */
.app-layout, .main-content {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
}
.chat-page {
    max-width: 100% !important;
    margin-top: 0;
}
</style>
<div class="chat-page">
    <div class="chat-layout">
        {{-- Sidebar - Chat List --}}
        <aside class="chat-sidebar" id="chatSidebar">
            {{-- Header --}}
            <header class="sidebar-header">
                <div class="header-left">
                    <div class="user-avatar-large">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}">
                    </div>
                    <span class="username-text">{{ auth()->user()->username }}</span>
                </div>
                <div class="header-actions">
                    <a href="{{ route('groups.create') }}" class="icon-btn" title="{{ __('chat.new_group') }}">
                        <i class="fas fa-users"></i>
                    </a>
                    <button class="icon-btn" onclick="showUserSearch()" title="{{ __('chat.new_message') }}">
                        <i class="fas fa-message"></i>
                    </button>
                </div>
            </header>

            {{-- Search --}}
            <div class="search-bar">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="{{ __('chat.search_or_start_chat') }}" id="sidebarSearch" oninput="filterSidebarConversations(this.value)">
                </div>
            </div>

            {{-- Conversations List --}}
            <div class="conversations-list" id="sidebarConvList">
                @forelse($conversations as $conv)
                @php
                    $latestMessage = $conv->latestMessage;
                    $isGroup = $conv->is_group;
                    $displayName = $isGroup ? $conv->display_name : ($conv->other_user->username ?? 'User');
                    $avatarUrl = $isGroup
                        ? ($conv->group && $conv->group->avatar ? asset('storage/' . $conv->group->avatar) : null)
                        : ($conv->other_user ? $conv->other_user->avatar_url : null);

                    // Message preview logic
                    $messagePreview = '';
                    $messageIcon = '';
                    $isOwn = false;
                    if ($latestMessage) {
                        $isOwn = $latestMessage->sender_id === auth()->id();
                        $content = strip_tags($latestMessage->content);

                        switch ($latestMessage->type) {
                            case 'image':
                                $messageIcon = '📷 ';
                                $messagePreview = $isOwn ? __('chat.you_sent_photo') : __('chat.sent_photo');
                                break;
                            case 'video':
                                $messageIcon = '🎥 ';
                                $messagePreview = $isOwn ? __('chat.you_sent_video') : __('chat.sent_video');
                                break;
                            case 'audio':
                                $messageIcon = '🎤 ';
                                $messagePreview = $isOwn ? __('chat.you_sent_audio') : __('chat.sent_audio');
                                break;
                            case 'document':
                                $messageIcon = '📎 ';
                                $messagePreview = $isOwn ? __('chat.you_sent_document') : __('chat.sent_document');
                                break;
                            case 'gif':
                                $messageIcon = 'GIF ';
                                $messagePreview = $isOwn ? __('chat.you_sent_gif') : __('chat.sent_gif');
                                break;
                            case 'sticker':
                                $messageIcon = '⭐ ';
                                $messagePreview = $isOwn ? __('chat.you_sent_sticker') : __('chat.sent_sticker');
                                break;
                            case 'story_reply':
                                $messageIcon = '📸 ';
                                $content = trim(str_replace('📸 Reply to your story:', '', $content));
                                $messagePreview = $isOwn ? __('chat.you_replied_to_story') : __('chat.replied_to_story');
                                if (!empty($content)) {
                                    $messagePreview .= ': ' . Str::limit($content, 25);
                                }
                                break;
                            default:
                                $messagePreview = $content;
                                break;
                        }

                        // Add "You: " prefix for own messages (except story replies)
                        if ($isOwn && $latestMessage->type !== 'story_reply') {
                            $messagePreview = __('chat.you').': ' . $messagePreview;
                        }
                    }

                    if (empty($messagePreview)) {
                        $messagePreview = __('chat.start_a_conversation');
                    }
                @endphp
                <a href="{{ route('chat.show', $conv) }}" class="conversation-item {{ $conv->id === ($conversation->id ?? null) ? 'active' : '' }} {{ $conv->unread_count > 0 ? 'unread' : '' }}" data-name="{{ $displayName }}" data-user-id="{{ $isGroup ? '' : ($conv->other_user?->id ?? '') }}" data-conversation-slug="{{ $conv->slug }}">
                    <div class="conv-avatar">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                        @elseif($isGroup)
                            <div class="avatar-fallback group"><i class="fas fa-users"></i></div>
                        @else
                            <div class="avatar-fallback">{{ substr($displayName, 0, 1) }}</div>
                        @endif
                        @if(!$isGroup && $conv->other_user)
                            <span class="online-indicator {{ $conv->other_user->is_online && $conv->other_user->last_active && $conv->other_user->last_active->diffInSeconds(now()) < 120 ? 'online' : '' }}" data-user-id="{{ $conv->other_user->id }}"></span>
                        @endif
                    </div>
                    <div class="conv-content">
                        <div class="conv-header">
                            <div class="conv-title-container">
                                <span class="conv-title">
                                    {{ $displayName }}
                                    @if(!$isGroup && $conv->other_user)
                                        <span class="online-status-text {{ $conv->other_user->is_online && $conv->other_user->last_active && $conv->other_user->last_active->diffInSeconds(now()) < 120 ? 'online' : 'offline' }}"
                                              data-user-id="{{ $conv->other_user->id }}">
                                            @if($conv->other_user->is_online && $conv->other_user->last_active && $conv->other_user->last_active->diffInSeconds(now()) < 120)
                                                • {{ __('chat.online') }}
                                            @endif
                                        </span>
                                    @endif
                                </span>
                                @if(!$isGroup && $conv->other_user)
                                    <span class="typing-indicator-inline" style="display: none; color: #25d366; font-size: 11px; font-style: italic; margin-left: 6px;">{{ __('chat.typing') }}</span>
                                @endif
                            </div>
                            <span class="conv-time">@if($conv->last_message_at){{ \Carbon\Carbon::parse($conv->last_message_at)->format('H:i') }}@endif</span>
                        </div>
                        <div class="conv-footer">
                            <p class="conv-preview {{ $conv->unread_count > 0 ? 'unread-text' : '' }}">
                                @if($latestMessage && $latestMessage->sender_id === auth()->id())
                                    <i class="fas {{ $latestMessage->read_at ? 'fa-check-double read' : 'fa-check sent' }}"></i>
                                @endif
                                @if($latestMessage)
                                    @if($latestMessage->type === 'image')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_an_image') }}</span>
                                    @elseif($latestMessage->type === 'video')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_a_video') }}</span>
                                    @elseif($latestMessage->type === 'audio')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_an_audio') }}</span>
                                    @elseif($latestMessage->type === 'document')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_a_document') }}</span>
                                    @elseif($latestMessage->type === 'gif')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_a_gif') }}</span>
                                    @elseif($latestMessage->type === 'sticker')
                                        <span class="preview-text">{{ $isOwn ? __('chat.you').': ' : '' }}{{ __('chat.sent_a_sticker') }}</span>
                                    @elseif($latestMessage->type === 'story_reply')
                                        <span class="preview-text">{{ $messagePreview }}</span>
                                    @else
                                        <span class="preview-text">{{ $messageIcon }}{{ $messagePreview }}</span>
                                    @endif
                                @else
                                    <span class="preview-text">{{ __('chat.start_a_conversation') }}</span>
                                @endif
                            </p>
                            @if($conv->unread_count > 0)
                                <span class="unread-pill">{{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-comments"></i></div>
                    <h3>{{ __('chat.no_messages_yet') }}</h3>
                    <p>{{ __('chat.start_new_conversation') }}</p>
                </div>
                @endforelse
            </div>
        </aside>

        {{-- Main Content - Welcome Screen --}}
        <main class="chat-welcome">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <svg viewBox="0 0 24 24" width="120" height="120">
                        <path fill="currentColor" d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 1.752.451 3.397 1.244 4.848L2.001 21.998l5.298-1.392c1.396.761 2.987 1.196 4.702 1.196 5.522 0 9.999-4.477 9.999-9.999s-4.477-9.999-9.999-9.999zm0 18.181c-1.496 0-2.896-.394-4.114-1.086l-.294-.168-3.049.802.815-2.972-.192-.305c-.762-1.212-1.166-2.613-1.166-4.053 0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
                    </svg>
                </div>
                <h1>{{ __('chat.nexus_web') }}</h1>
                <p>{{ __('chat.welcome_message') }}</p>
                <p class="small-text">{{ __('chat.end_to_end_encrypted') }}</p>
            </div>
            <div class="welcome-footer">
                <button class="icon-btn large" onclick="showUserSearch()" title="{{ __('chat.start_chat') }}">
                    <i class="fas fa-message"></i>
                    <span>{{ __('chat.start_chat') }}</span>
                </button>
            </div>
        </main>
    </div>

    {{-- Search Modal --}}
    <div id="userSearchModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) hideUserSearch()">
        <div class="modal-container">
            <div class="modal-header">
                <button class="back-btn" onclick="hideUserSearch()"><i class="fas fa-arrow-left"></i></button>
                <h3>{{ __('chat.new_chat') }}</h3>
                <div class="modal-spacer"></div>
            </div>
            <div class="modal-body">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="userSearch" placeholder="{{ __('chat.search_contacts') }}" class="search-field">
                </div>
                <div id="userResults" class="search-results"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Use layout CSS variables for theme support */
:root {
    --wa-bg: var(--bg, #111b21);
    --wa-panel: var(--surface, #202c33);
    --wa-panel-hover: var(--surface-hover, #2a3942);
    --wa-border: var(--border, #2f3b43);
    --wa-text: var(--text, #e9edef);
    --wa-text-muted: var(--text-muted, #8696a0);
    --wa-accent: var(--primary, #00a884);
    --wa-blue: var(--primary, #53bdeb);
    --wa-green: var(--success, #25d366);
    --wa-red: var(--danger, #f15c6d);
    --wa-yellow: var(--warning, #f7b928);
}

* { box-sizing: border-box; }

.chat-page {
    height: calc(100vh - 64px);
    background: var(--wa-bg);
    overflow: hidden;
}

.chat-layout {
    display: flex;
    height: 100%;
    width: 100%;
}

/* Sidebar */
.chat-sidebar {
    width: 100%;
    max-width: none;
    min-width: 320px;
    background: var(--wa-panel);
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--wa-border);
}

/* Desktop - make sidebar wider */
@media (min-width: 900px) {
    .chat-sidebar {
        max-width: 450px;
    }
}

@media (min-width: 1200px) {
    .chat-sidebar {
        max-width: 500px;
    }
}

@media (min-width: 1400px) {
    .chat-sidebar {
        max-width: 550px;
    }
}

/* Header */
.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
}

.header-left { display: flex; align-items: center; gap: 10px; }

.user-avatar-large {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    font-weight: 600;
    flex-shrink: 0;
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.username-text {
    font-size: 14px;
    font-weight: 600;
    color: var(--wa-text);
}

.header-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    color: var(--wa-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
    font-size: 16px;
}

.icon-btn:hover {
    background: var(--wa-panel-hover);
    color: var(--wa-text);
}

.icon-btn.large {
    width: auto;
    height: 44px;
    padding: 0 20px;
    border-radius: 22px;
    background: var(--wa-accent);
    color: white;
    gap: 8px;
}

.icon-btn.large:hover {
    background: var(--wa-accent);
    opacity: 0.9;
}

/* Search */
.search-bar {
    padding: 8px 12px;
    background: var(--wa-panel);
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input-wrapper i {
    position: absolute;
    left: 14px;
    color: var(--wa-text-muted);
    font-size: 14px;
}

.search-input-wrapper input {
    width: 100%;
    padding: 10px 14px 10px 44px;
    background: var(--wa-bg);
    border: none;
    border-radius: 8px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
}

.search-input-wrapper input:focus {
    box-shadow: 0 0 0 2px var(--wa-accent);
}

/* Stories Section */
.stories-section {
    padding: 12px 0;
    border-bottom: 1px solid var(--wa-border);
}

.stories-header {
    padding: 0 16px;
    margin-bottom: 10px;
    font-size: 13px;
    color: var(--wa-text-muted);
    font-weight: 500;
}

.stories-scroll {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding: 0 12px;
    scrollbar-width: thin;
    scrollbar-color: var(--wa-border) transparent;
}

.stories-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.stories-scroll::-webkit-scrollbar-thumb {
    background: var(--wa-border);
    border-radius: 3px;
}

.story-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    min-width: 64px;
}

.story-ring {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    padding: 2px;
    border: 3px solid var(--wa-accent);
    margin-bottom: 6px;
}

.story-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    background: var(--wa-bg);
}

.story-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 600;
    font-size: 16px;
    border-radius: 50%;
}

.avatar-fallback.group {
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
}

.story-label {
    font-size: 11px;
    color: var(--wa-text-muted);
    text-align: center;
    max-width: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.empty-stories {
    padding: 10px 16px;
    color: var(--wa-text-muted);
    font-size: 13px;
}

/* Conversations List */
.conversations-list {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--wa-border) transparent;
}

.conversations-list::-webkit-scrollbar {
    width: 6px;
}

.conversations-list::-webkit-scrollbar-thumb {
    background: var(--wa-border);
    border-radius: 3px;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid var(--wa-border);
    text-decoration: none;
}

.conversation-item:hover {
    background: var(--wa-panel-hover);
}

.conversation-item.unread {
    background: rgba(0, 168, 132, 0.08);
}

.conv-avatar {
    margin-right: 14px;
    flex-shrink: 0;
    position: relative;
}

.conv-avatar .avatar-fallback,
.conv-avatar img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.conv-avatar img {
    border-radius: 50%;
}

.conv-content {
    flex: 1;
    min-width: 0;
}

.conv-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.conv-title {
    font-size: 15px;
    font-weight: 500;
    color: var(--wa-text);
}

.online-status-text {
    font-size: 12px;
    margin-left: 6px;
    font-weight: 500;
    position: relative;
    display: inline-block;
}

.online-status-text.online {
    color: #25d366;
    animation: neon-pulse 2s ease-in-out infinite;
}

.online-status-text.offline {
    color: #667781;
    animation: none; /* prevent bounce fallback in case class isn't removed immediately */
}

@keyframes neon-pulse {
    0%, 100% {
        text-shadow: 0 0 2px rgba(37, 211, 102, 0.6);
    }
    50% {
        text-shadow: 0 0 12px rgba(37, 211, 102, 1);
    }
}

.conv-time {
    font-size: 12px;
    color: var(--wa-text-muted);
}

.conv-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.conv-preview {
    margin: 0;
    font-size: 13px;
    color: var(--wa-text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 260px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
}

.conv-preview.unread-text {
    color: var(--wa-text);
    font-weight: 500;
}

.conv-preview i.read-status {
    font-size: 14px;
    flex-shrink: 0;
}

.conv-preview i.read-status.read,
.conv-preview i.read {
    color: #53bdeb;
}

.conv-preview i.read-status.sent,
.conv-preview i.sent {
    color: #8696a3;
}

.conv-preview .preview-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    min-width: 0;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.empty-preview {
    font-style: italic;
    opacity: 0.7;
}

.unread-pill {
    background: var(--wa-accent);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    min-width: 20px;
    text-align: center;
    flex-shrink: 0;
    white-space: nowrap;
    margin-left: 8px;
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 64px;
    color: var(--wa-text-muted);
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state h3 {
    margin: 0 0 8px;
    font-size: 18px;
    color: var(--wa-text);
}

.empty-state p {
    margin: 0 0 24px;
    color: var(--wa-text-muted);
    font-size: 14px;
}

.start-chat-btn {
    background: var(--wa-accent);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.start-chat-btn:hover {
    background: var(--wa-accent);
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Welcome Screen */
.chat-welcome {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--wa-bg);
    border-bottom: 6px solid var(--wa-accent);
}

.welcome-content {
    text-align: center;
    padding: 40px;
    max-width: 500px;
}

.welcome-icon {
    color: var(--wa-text-muted);
    margin-bottom: 24px;
    opacity: 0.5;
}

.welcome-content h1 {
    margin: 0 0 16px;
    font-size: 32px;
    font-weight: 300;
    color: var(--wa-text);
}

.welcome-content p {
    margin: 0 0 8px;
    font-size: 14px;
    color: var(--wa-text-muted);
    line-height: 1.5;
}

.small-text {
    font-size: 12px;
    opacity: 0.6;
}

.welcome-footer {
    margin-top: 40px;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    background: var(--wa-panel);
    width: 100%;
    max-width: 450px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 500;
    color: var(--wa-text);
    flex: 1;
    text-align: center;
}

.modal-spacer { width: 38px; }

.back-btn {
    background: none;
    border: none;
    color: var(--wa-text-muted);
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
}

.modal-body {
    padding: 16px;
}

.search-box {
    position: relative;
    margin-bottom: 16px;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--wa-text-muted);
}

.search-field {
    width: 100%;
    padding: 12px 14px 12px 44px;
    background: var(--wa-bg);
    border: none;
    border-radius: 8px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
}

.search-field:focus {
    box-shadow: 0 0 0 2px var(--wa-accent);
}

.search-results {
    max-height: 350px;
    overflow-y: auto;
}

.result-user {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.result-user:hover {
    background: var(--wa-panel-hover);
}

.result-user img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.result-user .avatar-fallback {
    width: 42px;
    height: 42px;
    margin-right: 12px;
    font-size: 16px;
}

.result-user {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.result-user:hover {
    background: var(--wa-panel-hover);
}

.result-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.result-user-name {
    font-size: 14px;
    color: var(--wa-text);
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.result-user-fullname {
    font-size: 12px;
    color: var(--wa-text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 900px) {
    .chat-sidebar {
        width: 100%;
        min-width: 100%;
    }

    .chat-welcome {
        display: none;
    }
}

@media (max-width: 600px) {
    .chat-page {
        height: calc(100vh - 56px);
    }

    .chat-sidebar {
        width: 100%;
    }

    .sidebar-header {
        padding: 10px 12px;
    }

    .header-actions {
        gap: 4px;
    }

    .icon-btn {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }

    .story-ring {
        width: 50px;
        height: 50px;
    }

    .conversation-item {
        padding: 10px 12px;
    }

    .conv-avatar img,
    .conv-avatar .avatar-fallback {
        width: 42px;
        height: 42px;
    }

    .conv-title {
        font-size: 14px;
        max-width: 140px;
    }

    .conv-preview {
        max-width: 180px;
        font-size: 12px;
    }

    .conv-time {
        font-size: 11px;
    }

    .unread-pill {
        font-size: 10px;
        padding: 1px 6px;
        min-width: 18px;
    }
}

@media (max-width: 400px) {
    .conv-title {
        font-size: 13px;
        max-width: 120px;
    }

    .conv-preview {
        max-width: 150px;
        font-size: 11px;
    }

    .conv-time {
        font-size: 10px;
    }
}
</style>

<script>
    window.currentUserId = {{ auth()->id() }};

    function showUserSearch() {
        document.getElementById('userSearchModal').style.display = 'flex';
        setTimeout(() => document.getElementById('userSearch').focus(), 100);
    }

    function hideUserSearch() {
        document.getElementById('userSearchModal').style.display = 'none';
    }

    function filterSidebarConversations(query) {
        const items = document.querySelectorAll('#sidebarConvList .conversation-item');
        const q = query.toLowerCase();
        items.forEach(item => {
            const name = item.getAttribute('data-name')?.toLowerCase() || '';
            item.style.display = name.includes(q) ? 'flex' : 'none';
        });
    }

    document.getElementById('userSearch').addEventListener('input', function() {
        const query = this.value.trim();
        const resultsDiv = document.getElementById('userResults');
        if (query.length < 2) { resultsDiv.innerHTML = ''; return; }

        fetch(`/api/search-users?q=${encodeURIComponent(query)}`, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                resultsDiv.innerHTML = data.users.map(u => `
                    <div class="result-user" onclick="startChat(${u.id})">
                        <img src="${escapeHtml(u.avatar_url)}">
                        <div class="result-user-info">
                            <div class="result-user-name">${escapeHtml(u.username)}</div>
                            ${u.name && u.name !== u.username ? `<div class="result-user-fullname">${escapeHtml(u.name)}</div>` : ''}
                        </div>
                    </div>
                `).join('');
            }
        });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function startChat(userId) { window.location.href = '/chat/start/' + userId; }
    function startChatWithUser(userId) { window.location.href = '/chat/start/' + userId; }

    // Translation strings for JavaScript (realtime.js)
    window.chatTranslations = {
        you: '{{ __('chat.you') }}',
        online: '{{ __('chat.online') }}',
        offline: '{{ __('chat.offline') }}',
        last_active: '{{ __('chat.last_active') }}',
        you_sent_photo: '{{ __('chat.you_sent_photo') }}',
        you_sent_video: '{{ __('chat.you_sent_video') }}',
        you_sent_audio: '{{ __('chat.you_sent_audio') }}',
        you_sent_document: '{{ __('chat.you_sent_document') }}',
        you_sent_gif: '{{ __('chat.you_sent_gif') }}',
        you_sent_sticker: '{{ __('chat.you_sent_sticker') }}',
        you_replied_to_story: '{{ __('chat.you_replied_to_story') }}',
        sent_photo: '{{ __('chat.sent_photo') }}',
        sent_video: '{{ __('chat.sent_video') }}',
        sent_audio: '{{ __('chat.sent_audio') }}',
        sent_document: '{{ __('chat.sent_document') }}',
        sent_gif: '{{ __('chat.sent_gif') }}',
        sent_sticker: '{{ __('chat.sent_sticker') }}',
        replied_to_story: '{{ __('chat.replied_to_story') }}',
        sent_an_image: '{{ __('chat.sent_an_image') }}',
        sent_a_video: '{{ __('chat.sent_a_video') }}',
        sent_an_audio: '{{ __('chat.sent_an_audio') }}',
        sent_a_document: '{{ __('chat.sent_a_document') }}',
        sent_a_gif: '{{ __('chat.sent_a_gif') }}',
        sent_a_sticker: '{{ __('chat.sent_a_sticker') }}',
        start_a_conversation: '{{ __('chat.start_a_conversation') }}',
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Realtime.js will auto-initialize
    });
</script>
@endsection
