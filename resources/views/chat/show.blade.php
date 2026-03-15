@extends('layouts.app')

@php
$chatTitle = $conversation->is_group
    ? ($conversation->display_name ?? 'Group Chat')
    : (($conversation->other_user->username ?? 'Chat'));
@endphp

@section('title', $chatTitle)

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat-show.css') }}">
@vite(['resources/js/legacy/chat-show.js'])
<script>
// Blade dynamic variables for JavaScript
window.chatStoreUrl = `{{ route('chat.store', $conversation) }}`;
window.chatClearUrl = `{{ route('chat.clear', $conversation) }}`;
window.currentUserId = {{ auth()->id() }};
window.currentUsername = '{{ auth()->user()->username }}';
window.currentUserAvatarUrl = '{{ auth()->user()->avatar_url }}';
window.conversationIsGroup = {{ $conversation->is_group ? 'true' : 'false' }};
@if(!$conversation->is_group && $conversation->other_user)
window.currentChatUserId = {{ $conversation->other_user->id }};
@endif
window.activeConversationId = {{ $conversation->id }};
window.chatTranslations = {
    you: '{{ __('chat.you') }}',
    online: '{{ __('chat.online') }}',
    offline: '{{ __('chat.offline') }}',
    typing: '{{ __('chat.typing') }}',
    sent_an_image: '{{ __('chat.sent_an_image') }}',
    sent_a_video: '{{ __('chat.sent_a_video') }}',
    sent_an_audio: '{{ __('chat.sent_an_audio') }}',
    sent_a_document: '{{ __('chat.sent_a_document') }}',
    sent_a_gif: '{{ __('chat.sent_a_gif') }}',
    sent_a_sticker: '{{ __('chat.sent_a_sticker') }}',
    replied_to_story: '{{ __('chat.replied_to_story') }}',
    story_reply: '{{ __('chat.story_reply') }}',
    message_deleted: '{{ __('chat.message_deleted') }}',
    failed_to_send_media: '{{ __('chat.failed_to_send_media') }}',
    error_sending_media: '{{ __('chat.error_sending_media') }}',
    group: '{{ __('chat.group') }}',
    invited_you_to_join: '{{ __('chat.invited_you_to_join') }}',
    join: '{{ __('chat.join') }}',
    sent: '{{ __('chat.sent') }}',
    confirm_delete: '{{ __('chat.confirm_delete') }}',
    failed_to_send_media_msg: '{{ __('messages.failed_to_send_media') }}',
    error_sending_media_msg: '{{ __('messages.error_sending_media') }}'
};
</script>
<div class="chat-page">
    <div class="chat-layout">
        {{-- Sidebar --}}
        <aside class="chat-sidebar" id="chatSidebar">
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

            <div class="search-bar">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="{{ __('chat.search_or_start_chat') }}" id="sidebarSearch" oninput="filterSidebarConversations(this.value)">
                </div>
            </div>
            <div class="conversations-list" id="sidebarConvList">
                @php
                    $conversations = \App\Models\Conversation::where('user1_id', auth()->id())
                        ->orWhere('user2_id', auth()->id())
                        ->with(['user1', 'user2', 'latestMessage.sender'])
                        ->orderBy('last_message_at', 'desc')
                        ->limit(50)
                        ->get();
                @endphp
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
                <a href="{{ route('chat.show', $conv) }}" class="conversation-item {{ $conv->id === $conversation->id ? 'active' : '' }} {{ $conv->unread_count > 0 ? 'unread' : '' }}" data-name="{{ $displayName }}" data-user-id="{{ $isGroup ? '' : ($conv->other_user?->id ?? '') }}" data-conversation-slug="{{ $conv->slug }}">
                    <div class="conv-avatar">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                        @elseif($isGroup)
                            <div class="avatar-fallback group"><i class="fas fa-users"></i></div>
                        @else
                            <div class="avatar-fallback">{{ substr($displayName, 0, 1) }}</div>
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
                                        <span class="preview-text">{{ $messagePreview }}</span>
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

        {{-- Main Chat Area --}}
        <main class="chat-main">
            <header class="chat-header">
                <button class="back-btn-mobile" onclick="window.location.href='{{ route('chat.index') }}'">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="chat-user-info">
                    @if($conversation->is_group)
                        <a href="{{ route('groups.show', $conversation->group) }}" class="chat-avatar-link">
                            <div class="chat-avatar">
                                @if($conversation->group && $conversation->group->avatar)
                                    <img src="{{ asset('storage/' . $conversation->group->avatar) }}" alt="{{ __('chat.group') }}">
                                @else
                                    <div class="avatar-fallback"><i class="fas fa-users"></i></div>
                                @endif
                            </div>
                        </a>
                        <a href="{{ route('groups.show', $conversation->group) }}" class="chat-details-link">
                            <div class="chat-details">
                                <h3>{{ $conversation->group->name ?? $conversation->display_name ?? __('chat.group') }}</h3>
                                <span class="status">{{ __('chat.member_count', ['count' => $conversation->group->members->count() ?? 0]) }}</span>
                            </div>
                        </a>
                    @else
                        <div class="chat-avatar">
                            @if($conversation->other_user)
                                <img src="{{ $conversation->other_user->avatar_url }}" alt="Avatar">
                            @else
                                <div class="avatar-fallback">{{ substr('U', 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="chat-details">
                            <h3>{{ $conversation->other_user->username ?? __('chat.user') }}</h3>
                            <span class="status" id="chat-user-status" data-user-id="{{ $conversation->other_user->id ?? '' }}">
                                <span class="status-dot"></span>
                                <span class="status-text">{{ __('chat.offline') }}</span>
                            </span>
                        </div>
                    @endif
                </div>
                <div class="chat-actions">
                    @if($conversation->is_group)
                        <a href="{{ route('groups.show', $conversation->group) }}" class="action-btn" title="{{ __('chat.group') }}"><i class="fas fa-info-circle"></i></a>
                    @else
                        <button class="action-btn" onclick="clearChat()" title="{{ __('chat.clear_chat') }}"><i class="fas fa-trash"></i></button>
                    @endif
                </div>
            </header>

            <div class="chat-main-content">
                <div class="chat-messages" id="chatMessages">
                @forelse($messages as $message)
                    @if($message->type === 'system')
                        <div class="system-message">
                            <span class="system-text">{{ $message->content }}</span>
                            <span class="system-time">{{ $message->created_at->format('H:i') }}</span>
                        </div>
                    @elseif($message->type === 'group_invite')
                        @php $inviteData = json_decode($message->media_path, true); @endphp
                        <div class="message {{ $message->is_mine ? 'own' : 'other' }} group-invite" data-message-id="{{ $message->id }}">
                            @if(!$message->is_mine && $message->sender)
                                <div class="message-avatar">
                                    <img src="{{ $message->sender->avatar_url }}" alt="{{ $message->sender->username }}">
                                </div>
                            @endif
                            <div class="message-bubble">
                                @if(!$message->is_mine && $message->sender)
                                    <div class="sender-name">{{ $message->sender->username ?? $message->sender->name }}</div>
                                @endif
                                <div class="invite-card">
                                    <div class="invite-icon"><i class="fas fa-users"></i></div>
                                    <div class="invite-content">
                                        <div class="invite-title">{{ $inviteData['group_name'] ?? __('chat.group') }}</div>
                                        <div class="invite-text">{{ $message->sender->username ?? $message->sender->name ?? __('chat.someone') }} {{ __('chat.invited_you_to_join') }}</div>
                                    </div>
                                    @if(!$message->is_mine && ($inviteData['invite_link'] ?? null))
                                        <button class="accept-btn" onclick="acceptGroupInvite('{{ $inviteData['invite_link'] }}')"><i class="fas fa-check"></i> {{ __('chat.join') }}</button>
                                    @endif
                                </div>
                                <span class="message-time">
                                    {{ $message->created_at->format('H:i') }}
                                    @if($message->is_mine)
                                        @if($message->read_at)
                                            <i class="fas fa-check-double read" title="{{ __('chat.seen') }}"></i>
                                        @else
                                            <i class="fas fa-check" title="{{ __('chat.sent') }}"></i>
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="message {{ $message->is_mine ? 'own' : 'other' }} {{ $message->trashed() ? 'deleted' : '' }}" data-message-id="{{ $message->id }}">
                            @if(!$message->is_mine && $message->sender)
                                <div class="message-avatar">
                                    <img src="{{ $message->sender->avatar_url }}" alt="{{ $message->sender->username }}">
                                </div>
                            @endif
                            <div class="message-bubble">
                                @if(!$message->is_mine && $message->sender)
                                    <div class="sender-name">{{ $message->sender->username ?? $message->sender->name }}</div>
                                @endif
                                <div class="message-content">
                                    @if($message->trashed())
                                        <em class="deleted-text">{{ __('chat.message_deleted') }}</em>
                                    @else
                                        @php
                                            // Handle multiple media files (stored as JSON)
                                            $mediaItems = null;
                                            if ($message->media_path && str_starts_with($message->media_path, '[')) {
                                                $mediaItems = json_decode($message->media_path, true);
                                            }
                                        @endphp

                                        @if($mediaItems && is_array($mediaItems))
                                            {{-- Multiple media files - WhatsApp-style grid with max 4 items --}}
                                            <div class="message-media-album" data-message-id="{{ $message->id }}">
                                                {{-- Store all media paths in a script tag --}}
                                                <script type="application/json" class="media-data">
                                                    @json($mediaItems)
                                                </script>
                                                @php
                                            $displayCount = min(count($mediaItems), 4);
                                            $remainingCount = count($mediaItems) - $displayCount;
                                        @endphp

                                        @if($displayCount === 1)
                                            {{-- Single image - full width --}}
                                            <div class="media-grid-single">
                                                @php $media = $mediaItems[0]; @endphp
                                                @if($media['type'] === 'image')
                                                    <img src="{{ asset('storage/' . $media['path']) }}"
                                                         alt="Image"
                                                         class="message-image"
                                                         onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 0)">
                                                @elseif($media['type'] === 'video')
                                                    <video src="{{ asset('storage/' . $media['path']) }}" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 0)"></video>
                                                @endif
                                            </div>
                                        @elseif($displayCount === 2)
                                            {{-- Two images - side by side --}}
                                            <div class="media-grid-two">
                                                @foreach(array_slice($mediaItems, 0, 2) as $index => $media)
                                                    @if($media['type'] === 'image')
                                                        <img src="{{ asset('storage/' . $media['path']) }}"
                                                             alt="Image"
                                                             class="message-image"
                                                             onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, {{ $index }})">
                                                    @elseif($media['type'] === 'video')
                                                        <div class="media-item video">
                                                            <video src="{{ asset('storage/' . $media['path']) }}"></video>
                                                            <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, {{ $index }})">
                                                                <i class="fas fa-play"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            {{-- 3 or 4 images - WhatsApp grid --}}
                                            <div class="media-grid-{{ $displayCount }}">
                                                @foreach(array_slice($mediaItems, 0, $displayCount) as $index => $media)
                                                    @if($media['type'] === 'image')
                                                        <div class="media-item {{ $media['type'] }}">
                                                            <img src="{{ asset('storage/' . $media['path']) }}"
                                                                 alt="Image"
                                                                 onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, {{ $index }})">
                                                            @if($index === 3 && $remainingCount > 0)
                                                                <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 4)">
                                                                    <span class="overlay-text">+{{ $remainingCount }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @elseif($media['type'] === 'video')
                                                        <div class="media-item video">
                                                            <video src="{{ asset('storage/' . $media['path']) }}"></video>
                                                            <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, {{ $index }})">
                                                                <i class="fas fa-play"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                            </div>
                                        @elseif($message->type === 'image' && $message->media_path)
                                            <div class="message-media">
                                                <img src="{{ asset('storage/' . $message->media_path) }}" alt="Image" onclick="openMediaViewer(this.src)">
                                            </div>
                                        @elseif($message->type === 'video' && $message->media_path)
                                            <div class="message-media">
                                                <video src="{{ asset('storage/' . $message->media_path) }}" controls></video>
                                            </div>
                                        @endif
                                        @if($message->content && $message->content !== '' && $message->type !== 'group_invite')
                                            @php
                                                // Check if this is a story reply message
                                                $isStoryReply = str_starts_with($message->content, '📸 Reply to your story:');
                                                $storyReplyContent = $isStoryReply ? trim(str_replace('📸 Reply to your story:', '', $message->content)) : null;
                                            @endphp
                                            @if($isStoryReply)
                                                <div class="story-reply-message">
                                                    <div class="story-reply-header">
                                                        <span class="story-reply-label">{{ __('chat.story_reply') }}</span>
                                                    </div>
                                                    <div class="story-reply-content">{{ $storyReplyContent }}</div>
                                                </div>
                                            @else
                                                <span class="text">{{ $message->content }}</span>
                                            @endif
                                        @endif
                                    @endif
                                    <span class="message-time">
                                        {{ $message->created_at->format('H:i') }}
                                        @if($message->is_mine)
                                            @if($message->read_at)
                                                {{-- 2 blue checks - message read/seen --}}
                                                <i class="fas fa-check-double read" title="{{ __('chat.seen') }}"></i>
                                            @else
                                                {{-- 1 gray check - message sent but not delivered --}}
                                                <i class="fas fa-check" title="{{ __('chat.sent') }}"></i>
                                            @endif
                                        @endif
                                    </span>
                                </div>
                                @if($message->is_mine && !$message->trashed())
                                    <button class="delete-btn" onclick="deleteMessage({{ $message->id }})" title="{{ __('chat.delete_message') }}"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="no-messages">
                        <i class="fas fa-comments"></i>
                        <p>{{ __('chat.no_messages_in_chat', ['user' => $conversation->other_user->username ?? __('chat.user')]) }}</p>
                    </div>
                @endforelse
            </div>
            </div>

            <div class="chat-input-area">
                <div class="typing-indicator" id="typingIndicator" style="display: none;">
                    <span class="typing-dots">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </span>
                    <span class="typing-text">{{ __('chat.is_typing', ['user' => $conversation->other_user->username ?? __('chat.user')]) }}</span>
                </div>
                <form id="messageForm" onsubmit="sendMessage(event)">
                    <div id="mediaPreview" class="media-preview" style="display: none;">
                        <div class="preview-carousel">
                            <button type="button" class="carousel-arrow left" onclick="movePreview(-1)" title="{{ __('chat.previous') }}">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="preview-slides" id="previewSlides">
                                <!-- Slides will be added here -->
                            </div>
                            <button type="button" class="carousel-arrow right" onclick="movePreview(1)" title="{{ __('chat.next') }}">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="preview-indicators" id="previewIndicators">
                            <!-- Dots will be added here -->
                        </div>
                        <div class="preview-info">
                            <span id="previewCount">1 / 1</span>
                            <button type="button" class="clear-all" onclick="clearMediaPreview()" title="{{ __('chat.remove_all') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-row">
                        <label for="mediaInput" class="attach-btn" title="{{ __('chat.attach') }}"><i class="fas fa-paperclip"></i></label>
                        <input type="file" id="mediaInput" accept="image/*,video/*" multiple onchange="handleMediaSelect(event)" style="display: none;">
                        <input type="text" id="messageInput" placeholder="{{ __('chat.type_a_message') }}" maxlength="1000" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <button type="submit" id="sendButton" class="send-btn" title="{{ __('chat.send') }}"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    {{-- Media Viewer Modal --}}
    <div id="mediaViewer" class="media-viewer" onclick="closeMediaViewerOnOverlay(event)">
        <button class="viewer-close" onclick="closeMediaViewer()" title="{{ __('chat.close') }}"><i class="fas fa-times"></i></button>
        <button class="viewer-arrow left" onclick="navigateMedia(-1, event)" title="{{ __('chat.previous') }}"><i class="fas fa-chevron-left"></i></button>
        <button class="viewer-arrow right" onclick="navigateMedia(1, event)" title="{{ __('chat.next') }}"><i class="fas fa-chevron-right"></i></button>
        <div class="viewer-content">
            <img id="viewerImage" src="" alt="Full size">
            <video id="viewerVideo" src="" controls style="display: none; max-width: 90%; max-height: 90vh;"></video>
        </div>
        <div class="viewer-counter" id="viewerCounter">1 / 1</div>
    </div>

    {{-- User Search Modal --}}
    <div id="userSearchModal" class="modal-overlay" style="display: none;" onclick="if(event.target===this)hideUserSearch()">
        <div class="modal-box">
            <div class="modal-header">
                <button class="back-btn" onclick="hideUserSearch()"><i class="fas fa-arrow-left"></i></button>
                <h3>{{ __('chat.new_chat') }}</h3>
                <div class="spacer"></div>
            </div>
            <div class="modal-body">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="userSearch" placeholder="{{ __('chat.search_contacts') }}" class="search-input">
                </div>
                <div id="userResults" class="results-list"></div>
            </div>
        </div>
    </div>

    {{-- Delete Message Modal --}}
    <div id="deleteMessageModal" class="modal-overlay" style="display: none;" onclick="if(event.target===this)closeDeleteModal()">
        <div class="modal-box delete-modal">
            <div class="modal-header">
                <h3>{{ __('chat.delete_message') }}</h3>
                <button class="close-btn" onclick="closeDeleteModal()" title="{{ __('chat.close') }}"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p class="delete-description">{{ __('chat.delete_message_desc') }}</p>
                <button class="delete-option" onclick="confirmDelete('everyone')">
                    <div class="delete-option-icon"><i class="fas fa-users"></i></div>
                    <div class="delete-option-content">
                        <div class="delete-option-title">{{ __('chat.delete_for_everyone') }}</div>
                        <div class="delete-option-desc">{{ __('chat.delete_for_everyone_desc') }}</div>
                    </div>
                </button>
                <button class="delete-option" onclick="confirmDelete('me')">
                    <div class="delete-option-icon"><i class="fas fa-user"></i></div>
                    <div class="delete-option-content">
                        <div class="delete-option-title">{{ __('chat.delete_for_me') }}</div>
                        <div class="delete-option-desc">{{ __('chat.delete_for_me_desc') }}</div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
