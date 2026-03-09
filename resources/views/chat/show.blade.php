@extends('layouts.app')

@php
$chatTitle = $conversation->is_group
    ? ($conversation->display_name ?? 'Group Chat')
    : (($conversation->other_user->username ?? 'Chat'));
@endphp

@section('title', $chatTitle)

@section('content')
<style>
/* Hide layout mobile nav on chat page */
.mobile-nav { display: none !important; }

/* Override layout constraints for full width chat */
.app-layout, .main-content {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
}
.chat-page {
    padding-top: 64px;
}

@media (min-width: 901px) {
    .chat-page {
        padding-top: 68px;
    }
}
</style>
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
                                            {{ $conv->other_user->is_online && $conv->other_user->last_active && $conv->other_user->last_active->diffInSeconds(now()) < 120 ? '• Online' : '' }}
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

<style>
/* Hide main layout mobile nav on chat pages */
.chat-page ~ .mobile-nav,
body:has(.chat-page) .mobile-nav {
    display: none !important;
}

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
    --wa-message-out: #005c4b;
    --wa-message-in: #202c33;
}

.chat-page {
    height: calc(100vh - 64px);
    background: var(--wa-bg);
    overflow: hidden;
    position: relative;
}

@media (min-width: 901px) {
    .chat-page {
        height: calc(100vh - 68px);
        padding-top: 1px;
    }
}

/* Override layout constraints for full width */
.chat-page ~ .app-layout,
.chat-page ~ .main-content {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
}

.chat-layout {
    display: flex;
    height: 100%;
    width: 100%;
    max-width: 100%;
    margin: 0;
}

/* Sidebar - Fixed width on left */
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

/* Conversations List */
.conversations-list {
    flex: 1;
    overflow-y: auto;
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

.conversation-item.active {
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

.avatar-fallback {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
    border-radius: 50%;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--wa-text-muted);
    border: 2px solid var(--wa-panel);
    transition: background 0.3s;
}

.online-indicator.online {
    background: var(--wa-green);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.avatar-fallback.group {
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
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

.conv-title-container {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
}

.conv-title {
    font-size: 15px;
    font-weight: 500;
    color: var(--wa-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    animation: none; /* ensure offline status never bounces */
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
    flex-shrink: 0;
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

/* Static Header - Fixed on mobile, static on desktop */
.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
    height: 64px;
    flex-shrink: 0;
}

/* Mobile - fixed header */
@media (max-width: 900px) {
    .chat-header {
        position: fixed;
        top: 64px;
        left: 0;
        right: 0;
        z-index: 100;
        height: 56px;
        padding: 10px 14px;
    }
}

.back-btn-mobile {
    background: none;
    border: none;
    color: var(--wa-text-muted);
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    display: none;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    flex-shrink: 0;
}

.back-btn-mobile:hover { color: var(--wa-text); }

/* Chat user info and actions */
.chat-header .chat-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    overflow: hidden;
    padding-top: 10px;
}

.chat-header .chat-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

/* Static Input Area - Fixed on mobile, static on desktop */
.chat-input-area {
    padding: 12px 16px;
    background: var(--wa-panel);
    border-top: 1px solid var(--wa-border);
    flex-shrink: 0;
}

/* Mobile - fixed input */
@media (max-width: 900px) {
    .chat-input-area {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        padding: 10px 14px;
    }
}

/* Messages area - with padding to account for fixed header */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Main content wrapper */
.chat-main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Desktop - add padding for project header */
@media (min-width: 901px) {
    .chat-main-content {
        padding-top: 15px;
    }
}

/* Mobile - add padding for fixed header/input */
@media (max-width: 900px) {
    .chat-main-content {
        padding-top: 10px;
        padding-bottom: 70px;
    }

    .chat-messages {
        padding-top: 70px;
    }
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
}

.sidebar-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.user-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
}

.user-avatar-sm img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-username {
    font-size: 14px;
    font-weight: 600;
    color: var(--wa-text);
}

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

.back-btn:hover { color: var(--wa-text); }

.sidebar-actions {
    margin-left: auto;
    display: flex;
    gap: 6px;
}

.icon-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    color: var(--wa-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 15px;
}

.icon-btn:hover { background: var(--wa-panel-hover); }

.sidebar-search {
    padding: 10px 12px;
    border-bottom: 1px solid var(--wa-border);
}

.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--wa-text-muted);
    font-size: 13px;
}

.search-wrapper input {
    width: 100%;
    padding: 9px 12px 9px 38px;
    background: var(--wa-bg);
    border: none;
    border-radius: 8px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
}

.search-wrapper input:focus { box-shadow: 0 0 0 2px var(--wa-accent); }

.conv-list {
    flex: 1;
    overflow-y: auto;
}

.conv-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--wa-border);
    text-decoration: none;
    transition: background 0.2s;
}

.conv-item:hover { background: var(--wa-panel-hover); }
.conv-item.active { background: var(--wa-panel-hover); }
.conv-item.unread { background: rgba(0, 168, 132, 0.08); }

.conv-avatar {
    margin-right: 12px;
    flex-shrink: 0;
}

.conv-avatar img,
.conv-avatar .avatar-fallback {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-fallback {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
    border-radius: 50%;
}

.conv-body { flex: 1; min-width: 0; }

.conv-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.conv-name {
    font-size: 15px;
    font-weight: 500;
    color: var(--wa-text);
}

.conv-time {
    font-size: 12px;
    color: var(--wa-text-muted);
}

.conv-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.conv-preview {
    font-size: 13px;
    color: var(--wa-text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.conv-preview i { font-size: 10px; color: var(--wa-blue); }
.conv-preview em { font-style: italic; opacity: 0.7; }

.badge {
    background: var(--wa-accent);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 10px;
}

.empty-sidebar {
    padding: 50px 20px;
    text-align: center;
    color: var(--wa-text-muted);
}

.empty-sidebar i { font-size: 48px; margin-bottom: 12px; opacity: 0.3; }
.empty-sidebar p { margin: 0; }

/* Main Chat */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--wa-bg);
    position: relative;
    overflow: hidden;
}

.chat-main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Group chat clickable links */
.chat-avatar-link,
.chat-details-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: opacity 0.2s;
}

.chat-avatar-link:hover,
.chat-details-link:hover {
    opacity: 0.8;
}

.chat-avatar-link:hover .chat-avatar,
.chat-details-link:hover h3 {
    opacity: 0.8;
}

.chat-avatar img, .chat-avatar .avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-details h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
    color: var(--wa-text);
    transition: color 0.2s;
}

.chat-details-link:hover h3 {
    color: var(--wa-accent);
}

.status {
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--wa-text-muted);
}

.status.online {
    color: var(--wa-green);
}

.status .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.status.online .status-dot {
    animation: pulse 2s infinite;
}

/* Online indicator for chat list */
.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--wa-text-muted);
    border: 2px solid var(--wa-panel);
    transition: background 0.3s;
}

.online-indicator.online {
    background: var(--wa-green);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.chat-actions {
    display: flex;
    gap: 8px;
}

/* Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-btn {
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
    font-size: 16px;
}

.action-btn:hover { background: var(--wa-panel-hover); color: var(--wa-text); }

.message {
    display: flex;
    gap: 8px;
    max-width: 75%;
    animation: msgIn 0.2s ease;
}

@keyframes msgIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.own {
    align-self: flex-end;
    flex-direction: row-reverse;
    justify-self: flex-end;
}

.message.other {
    align-self: flex-start;
    justify-self: flex-start;
}

.message-avatar {
    flex-shrink: 0;
}

.message-avatar img, .message-avatar .avatar-fallback {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 2px;
}

.message-bubble {
    display: flex;
    flex-direction: column;
    max-width: 100%;
    align-items: flex-end;
    word-break: break-word;
}

.message.own .message-bubble {
    align-items: flex-end;
}

.message.other .message-bubble {
    align-items: flex-start;
}

.sender-name {
    font-size: 12px;
    font-weight: 600;
    color: #53bdeb;
    margin-bottom: 3px;
    padding: 0 12px;
}

.message-content {
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.4;
    position: relative;
    min-width: fit-content;
    max-width: 100%;
    display: flex;
    flex-direction: column;
    gap: 4px;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
}

.message.own .message-content {
    background: var(--wa-message-out);
    color: #e9edef;
    border-top-right-radius: 4px;
    border: 1px solid rgba(0, 168, 132, 0.2);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message.other .message-content {
    background: var(--wa-message-in);
    color: #e9edef;
    border-top-left-radius: 4px;
    border: 1px solid rgba(83, 189, 235, 0.2);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-content .text {
    word-wrap: break-word;
    display: block;
    color: inherit;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Noto Sans Arabic', 'Tahoma', 'Arial', sans-serif;
    unicode-bidi: embed;
    line-height: 1.6;
}

/* Arabic and RTL text support */
.message-content .text:lang(ar),
.message-content .text[dir="rtl"],
.message-content .text[lang="ar"] {
    direction: rtl;
    text-align: right;
}

.message-content .deleted-text {
    font-style: italic;
    color: rgba(233, 237, 239, 0.5);
    font-size: 13px;
}

.message.deleted .message-content {
    opacity: 0.7;
    background: rgba(0, 0, 0, 0.1) !important;
}

/* Story Reply Message Style */
.story-reply-message {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.story-reply-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--wa-text-muted);
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.story-reply-header i {
    font-size: 11px;
    color: var(--wa-accent);
}

.story-reply-label {
    font-weight: 500;
    color: #53bdeb;
}

.story-reply-content {
    font-size: 14px;
    line-height: 1.4;
    color: #e9edef;
}

.message.own .story-reply-header {
    border-bottom-color: rgba(0, 0, 0, 0.1);
}

/* Media album grid for multiple files - WhatsApp style */
.message-media-album {
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 8px;
    max-width: 100%;
}

/* Single image - full width */
.media-grid-single {
    width: 100%;
    max-width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.media-grid-single img {
    width: 100%;
    height: auto;
    max-height: 250px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
}

/* Two images - side by side */
.media-grid-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 100%;
}

.media-grid-two img,
.media-grid-two video {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
}

/* Three images - WhatsApp triangle layout */
.media-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 100%;
}

.media-grid-3 .media-item:first-child {
    grid-row: 1 / 3;
    grid-column: 1;
}

.media-grid-3 .media-item:first-child img {
    height: 100%;
    aspect-ratio: 1;
}

.media-grid-3 .media-item:nth-child(2),
.media-grid-3 .media-item:nth-child(3) {
    grid-column: 2;
}

/* Four images - WhatsApp grid */
.media-grid-4 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 100%;
}

.media-grid-4 .media-item {
    position: relative;
    aspect-ratio: 1;
}

.media-grid-4 .media-item img,
.media-grid-4 .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
}

/* Media item container */
.media-item {
    position: relative;
    overflow: hidden;
}

.media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

/* Overlay for videos and +N counter */
.media-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.media-overlay:hover {
    background: rgba(0, 0, 0, 0.5);
}

.media-overlay i {
    color: white;
    font-size: 32px;
}

.media-overlay .overlay-text {
    color: white;
    font-size: 24px;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

/* Video play icon */
.media-item.video .media-overlay i {
    background: rgba(0, 0, 0, 0.6);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.message-media {
    margin-bottom: 6px;
    border-radius: 8px;
    overflow: hidden;
    max-width: 100%;
}

.message-media img, .message-media video {
    max-width: 100%;
    width: auto;
    max-height: 250px;
    display: block;
}

.message-time {
    font-size: 11px;
    color: rgba(233, 237, 239, 0.7);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    flex-wrap: nowrap;
}

.message-time i.read {
    color: #53bdeb;
    /* WhatsApp blue for seen messages */
}

.message.own .message-time {
    justify-content: flex-end;
}

.message.other .message-time {
    justify-content: flex-start;
}

.message.own .message-time i {
    font-size: 10px;
    flex-shrink: 0;
}

.delete-btn {
    background: transparent;
    border: none;
    color: var(--wa-text-muted);
    cursor: pointer;
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 6px;
    margin-top: 4px;
    align-self: flex-end;
}

.delete-btn:hover { background: var(--wa-red); color: white; }

.message.deleted .message-content { opacity: 0.6; }
.deleted-text { font-style: italic; }

/* Group Invite */
.invite-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(0, 0, 0, 0.2);
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 6px;
}

.invite-icon {
    width: 42px;
    height: 42px;
    background: var(--wa-accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.invite-content { flex: 1; }
.invite-title { 
    font-weight: 600; 
    margin-bottom: 3px;
    color: #e9edef;
}
.invite-text { 
    font-size: 12px; 
    opacity: 0.8;
    color: #e9edef;
}

.accept-btn {
    background: var(--wa-accent);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.accept-btn:hover { background: var(--wa-green); }
.accept-btn.joined { background: var(--wa-text-muted); }

/* System Message */
.system-message {
    align-self: center;
    background: rgba(0, 0, 0, 0.2);
    padding: 6px 14px;
    border-radius: 12px;
    text-align: center;
    margin: 10px 0;
}

.system-text { font-size: 12px; color: var(--wa-text-muted); }
.system-time { font-size: 10px; color: var(--wa-text-muted); opacity: 0.7; display: block; margin-top: 3px; }

/* No Messages */
.no-messages {
    align-self: center;
    text-align: center;
    color: var(--wa-text-muted);
    margin-top: 60px;
}

.no-messages i { font-size: 56px; margin-bottom: 16px; opacity: 0.2; }
.no-messages p { margin: 0; font-size: 15px; }

/* Input Area - styles only (position is fixed in earlier CSS) */
#messageForm { display: flex; flex-direction: column; gap: 8px; }

/* Typing Indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    margin-bottom: 4px;
    color: var(--wa-text-muted);
    font-size: 13px;
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.typing-indicator.hiding {
    opacity: 0;
    transform: translateY(5px);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.typing-dots {
    display: flex;
    gap: 3px;
    align-items: center;
}

.typing-dots .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--wa-text-muted);
    animation: typingBounce 1.4s infinite ease-in-out both;
}

.typing-dots .dot:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots .dot:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes typingBounce {
    0%, 80%, 100% {
        transform: scale(0.6);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

.media-preview {
    background: var(--wa-bg);
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 8px;
}

/* Carousel Preview */
.preview-carousel {
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

.carousel-arrow {
    background: var(--wa-panel);
    border: 1px solid var(--wa-border);
    color: var(--wa-text);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.carousel-arrow:hover {
    background: var(--wa-accent);
    border-color: var(--wa-accent);
}

.carousel-arrow:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.preview-slides {
    flex: 1;
    overflow: hidden;
    position: relative;
    height: 200px;
}

.preview-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-slide.active {
    opacity: 1;
    z-index: 1;
}

.preview-slide img,
.preview-slide video {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    object-fit: contain;
}

.preview-slide .slide-number {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.preview-slide .remove-slide {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255, 59, 48, 0.9);
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
}

.preview-slide .remove-slide:hover {
    background: rgba(255, 59, 48, 1);
    transform: scale(1.1);
}

.preview-indicators {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 10px;
}

.preview-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--wa-border);
    cursor: pointer;
    transition: all 0.2s;
}

.preview-indicator.active {
    background: var(--wa-accent);
    width: 24px;
    border-radius: 4px;
}

.preview-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--wa-border);
}

.preview-info #previewCount {
    font-size: 12px;
    color: var(--wa-text-muted);
}

.clear-all {
    background: transparent;
    border: none;
    color: var(--wa-red);
    cursor: pointer;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.clear-all:hover {
    background: rgba(255, 59, 48, 0.1);
    border-radius: 6px;
}

.input-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attach-btn, .emoji-btn {
    background: transparent;
    border: none;
    color: var(--wa-text-muted);
    cursor: pointer;
    font-size: 18px;
    padding: 8px;
}

.attach-btn:hover, .emoji-btn:hover { color: var(--wa-text); }

#messageInput {
    flex: 1;
    padding: 12px 16px;
    background: var(--wa-bg);
    border: none;
    border-radius: 24px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
}

#messageInput {
    flex: 1;
    padding: 12px 16px;
    background: var(--wa-bg);
    border: none;
    border-radius: 20px;
    color: var(--wa-text);
    font-size: 15px;
    outline: none;
}

#messageInput:focus {
    box-shadow: none;
}

.send-btn {
    width: 42px;
    height: 42px;
    border: none;
    background: var(--wa-accent);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

.send-btn:active,
.send-btn:focus {
    background: var(--wa-accent) !important;
    outline: none !important;
    -webkit-tap-highlight-color: transparent !important;
}

/* Media Viewer */
.media-viewer {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
}

.media-viewer.active { display: flex; }

.viewer-content {
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 90%;
    max-height: 90vh;
}

#viewerImage,
#viewerVideo {
    max-width: 90%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
}

.viewer-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    font-size: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s;
}

.viewer-close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.viewer-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    font-size: 24px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.viewer-arrow.left { left: 30px; }
.viewer-arrow.right { right: 30px; }

.viewer-arrow:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-50%) scale(1.1);
}

.viewer-arrow:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.viewer-counter {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 99998;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-box {
    background: var(--wa-panel);
    width: 100%;
    max-width: 420px;
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--wa-border);
}

.modal-header h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 500;
    color: var(--wa-text);
    flex: 1;
    text-align: center;
}

.spacer { width: 36px; }

.modal-body { padding: 16px; }

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

.search-input {
    width: 100%;
    padding: 11px 14px 11px 42px;
    background: var(--wa-bg);
    border: none;
    border-radius: 8px;
    color: var(--wa-text);
    font-size: 14px;
    outline: none;
}

.search-input:focus { box-shadow: 0 0 0 2px var(--wa-accent); }

/* Delete Message Modal */
.delete-modal .modal-header h3 {
    text-align: left;
}

.delete-modal .close-btn {
    background: none;
    border: none;
    color: var(--wa-text-muted);
    font-size: 20px;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: 0.2s;
}

.delete-modal .close-btn:hover {
    background: var(--wa-border);
    color: var(--wa-text);
}

.delete-description {
    color: var(--wa-text-muted);
    font-size: 14px;
    margin: 0 0 16px 0;
}

.delete-option {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    width: 100%;
    padding: 16px;
    margin-bottom: 12px;
    background: var(--wa-bg);
    border: 1px solid var(--wa-border);
    border-radius: 10px;
    cursor: pointer;
    transition: 0.2s;
    text-align: left;
}

.delete-option:hover {
    background: var(--wa-border);
    border-color: var(--wa-text-muted);
}

.delete-option:last-child {
    margin-bottom: 0;
}

.delete-option-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--wa-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.delete-option:nth-child(2) .delete-option-icon {
    color: var(--wa-red);
    background: rgba(241, 92, 109, 0.1);
}

.delete-option:nth-child(3) .delete-option-icon {
    color: var(--wa-text-muted);
    background: rgba(134, 150, 160, 0.1);
}

.delete-option-content {
    flex: 1;
    min-width: 0;
}

.delete-option-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--wa-text);
    margin-bottom: 4px;
}

.delete-option-desc {
    font-size: 13px;
    color: var(--wa-text-muted);
    line-height: 1.4;
}

.results-list { max-height: 320px; overflow-y: auto; }

.result-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.result-item:hover { background: var(--wa-panel-hover); }

.result-item img, .result-item .avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 12px;
}

.result-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.result-name { 
    font-size: 14px; 
    color: var(--wa-text); 
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.result-fullname {
    font-size: 12px;
    color: var(--wa-text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 900px) {
    /* Show back button on mobile */
    .back-btn-mobile {
        display: flex;
    }

    .chat-sidebar {
        position: fixed;
        left: 0;
        top: 64px;
        bottom: 0;
        width: 100%;
        max-width: none;
        z-index: 9999;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        margin-top: 0;
        border-right: 1px solid var(--wa-border);
        background: var(--wa-bg);
    }

    .chat-sidebar.active { transform: translateX(0); }

    /* Hide main website mobile nav on chat pages */
    .mobile-nav, .app-layout ~ .mobile-nav {
        display: none !important;
    }
}

@media (max-width: 600px) {
    .chat-page {
        height: calc(100vh - 56px);
    }

    .chat-header {
        top: 64px;
        height: 56px;
        padding: 10px 14px;
    }

    .chat-sidebar {
        top: 120px;
    }

    .chat-main-content {
        padding-top: 10px;
        padding-bottom: 70px;
    }

    .chat-messages {
        padding: 14px 10px;
    }

    .chat-input-area {
        padding: 10px 12px;
    }

    /* Mobile message sizing */
    .message { 
        max-width: 88%;
    }
    
    /* Ensure messages with short text don't stretch */
    .message-content {
        max-width: 100%;
        padding: 8px 10px;
    }
    
    /* Sender name smaller on mobile */
    .sender-name {
        font-size: 11px;
        padding: 0 10px;
    }
    
    /* Better media sizing on mobile */
    .message-media img, 
    .message-media video,
    .media-grid-single img {
        max-height: 180px;
        max-width: 100%;
    }
    
    .media-grid-two,
    .media-grid-3,
    .media-grid-4 {
        max-width: 100%;
    }
    
    /* Compact time and status on mobile */
    .message-time {
        font-size: 10px;
        margin-top: 2px;
    }
    
    .message.own .message-time i {
        font-size: 9px;
    }
    
    /* Delete button on mobile */
    .delete-btn {
        padding: 4px 8px;
        font-size: 11px;
        margin-top: 2px;
    }

    /* Hide main website mobile nav on chat pages */
    .mobile-nav {
        display: none !important;
    }
}

/* Scrollbar styling */
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb { background: var(--wa-border); border-radius: 3px; }

.conv-list::-webkit-scrollbar { width: 6px; }
.conv-list::-webkit-scrollbar-track { background: transparent; }
.conv-list::-webkit-scrollbar-thumb { background: var(--wa-border); border-radius: 3px; }

/* Tablet view optimization */
@media (min-width: 601px) and (max-width: 900px) {
    .message {
        max-width: 70%;
    }
    
    .message-media img,
    .message-media video,
    .media-grid-single img {
        max-height: 220px;
    }
}
</style>

<script>
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
    fetch(`{{ route('chat.store', $conversation) }}`, {
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
                sender_id: {{ auth()->id() }},
                sender: {
                    username: '{{ auth()->user()->username }}',
                    avatar_url: '{{ auth()->user()->avatar_url }}'
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

    fetch(`{{ route('chat.store', $conversation) }}`, {
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
                sender_id: {{ auth()->id() }},
                sender: {
                    username: '{{ auth()->user()->username }}',
                    avatar_url: '{{ auth()->user()->avatar_url }}'
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
            alert(data.error || window.chatTranslations.failed_to_send_media);
        }
    })
    .catch(err => {
        console.error('Error sending media:', err);
        alert(window.chatTranslations.error_sending_media);
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

    const isOwn = msg.sender_id == {{ auth()->id() }};
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
    if (confirm('{{ __('chat.confirm_delete') }}')) {
        fetch(`{{ route('chat.clear', $conversation) }}`, {
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
    window.conversationIsGroup = {{ $conversation->is_group ? 'true' : 'false' }};

    @if(!$conversation->is_group && $conversation->other_user)
        window.currentChatUserId = {{ $conversation->other_user->id }};
    @endif

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

// Translation strings for JavaScript
window.chatTranslations = {
    you: '{{ __('chat.you') }}',
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
};

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

// Set active conversation ID for notification filtering
window.activeConversationId = {{ $conversation->id }};
</script>
@endsection
