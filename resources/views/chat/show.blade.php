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
                            case 'voice':
                                $messageIcon = '🎤 ';
                                $messagePreview = $isOwn ? __('chat.you_sent_voice_message') : __('chat.sent_voice_message');
                                break;
                            default:
                                $messagePreview = $content;
                                break;
                        }

                        // Add "You: " prefix for own messages (except story replies)
                        if ($isOwn && $latestMessage->type !== 'story_reply' && !in_array($latestMessage->type, ['image', 'video', 'audio', 'voice', 'document', 'gif', 'sticker', 'group_invite'])) {
                            $messagePreview = __('chat.you').': ' . $messagePreview;
                        }

                        // For group chats, prefix non-self messages with sender username
                        if ($isGroup && !$isOwn && $latestMessage->sender) {
                            $messagePreview = $latestMessage->sender->username . ': ' . $messagePreview;
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
                                                    <div class="media-item">
                                                        <img src="{{ asset('storage/' . $media['path']) }}"
                                                             alt="Image"
                                                             onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 0)">
                                                    </div>
                                                @elseif($media['type'] === 'video')
                                                    <div class="media-item video">
                                                        <video src="{{ asset('storage/' . $media['path']) }}" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 0)"></video>
                                                        <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, 0)">
                                                            <i class="fas fa-play"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($displayCount === 2)
                                            {{-- Two images - side by side --}}
                                            <div class="media-grid-two">
                                                @foreach(array_slice($mediaItems, 0, 2) as $index => $media)
                                                    @if($media['type'] === 'image')
                                                        <div class="media-item">
                                                            <img src="{{ asset('storage/' . $media['path']) }}"
                                                                 alt="Image"
                                                                 onclick="openMediaViewerFromAlbum(this, {{ $message->id }}, {{ $index }})">
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
                                        @elseif($message->type === 'voice' && $message->media_path)
                                            <div class="voice-message-simple" data-audio-url="{{ asset('storage/' . $message->media_path) }}">
                                                <button class="voice-play-btn-simple" onclick="toggleVoiceMessage(this)">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <div class="voice-info">
                                                    <span class="voice-label">Voice Message</span>
                                                    <span class="voice-duration-simple">{{ $message->duration ?? 0 }}s</span>
                                                </div>
                                                <button class="voice-speed-btn-simple" onclick="toggleVoiceSpeed(this)">1x</button>
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
                    <span class="typing-text">
                        @if($isGroup)
                            <span id="groupTypingText">{{ __('chat.users_typing') }}</span>
                        @else
                            <span id="singleTypingText">{{ __('chat.is_typing', ['user' => $conversation->other_user->username ?? __('chat.user')]) }}</span>
                        @endif
                    </span>
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
                        <button type="button" id="voiceRecordBtn" class="voice-record-btn" title="{{ __('chat.record_voice') }}" onclick="toggleVoiceRecording()"><i class="fas fa-microphone"></i></button>
                        <input type="file" id="mediaInput" accept="image/*,video/*" multiple onchange="handleMediaSelect(event)" style="display: none;">
                        <input type="text" id="messageInput" placeholder="{{ __('chat.type_a_message') }}" maxlength="1000" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <button type="submit" id="sendButton" class="send-btn" title="{{ __('chat.send') }}"><i class="fas fa-paper-plane"></i></button>
                    </div>
                    
                    {{-- Voice Recording Overlay --}}
                    <div id="voiceRecordingOverlay" class="voice-recording-overlay" style="display: none;">
                        <div class="recording-content">
                            <div class="recording-timer" id="recordingTimer">00:00</div>
                            <div class="recording-waveform" id="recordingWaveform"></div>
                            <div class="recording-controls">
                                <button class="recording-btn cancel" onclick="cancelVoiceRecording()" title="{{ __('chat.cancel') }}"><i class="fas fa-times"></i></button>
                                <button class="recording-btn" id="recordToggleBtn" onclick="toggleVoiceRecord()" title="{{ __('chat.start_recording') }}"><i class="fas fa-microphone"></i></button>
                                <button class="recording-btn send" id="sendVoiceBtn" title="{{ __('chat.send') }}" disabled onclick="console.log('🎤 Send clicked!'); sendVoiceMessage();"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
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
    color: #6b7280;
    font-size: 13px;
}

.message.deleted .message-content {
    opacity: 1;
    background: rgba(0, 0, 0, 0.08) !important;
}

.message.deleted .text {
    color: #4b5563;
    font-style: italic;
}

.message.deleted .message-time {
    color: #9ca3af;
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
    max-width: 320px;
}

/* Single image - full width */
.media-grid-single {
    width: 100%;
    max-width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.media-grid-single img,
.media-grid-single video {
    width: 100%;
    height: auto;
    max-height: 250px;
    object-fit: contain;
    border-radius: 8px;
    cursor: pointer;
    display: block;
}

/* Two images - side by side */
.media-grid-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 320px;
    width: fit-content;
}

.media-grid-two .media-item {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
}

.media-grid-two .media-item img,
.media-grid-two .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    display: block;
}

/* Three images - WhatsApp triangle layout */
.media-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 320px;
    width: fit-content;
}

.media-grid-3 .media-item {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
}

.media-grid-3 .media-item:first-child {
    grid-row: 1 / 3;
    grid-column: 1;
}

.media-grid-3 .media-item:first-child img,
.media-grid-3 .media-item:first-child video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.media-grid-3 .media-item:nth-child(2),
.media-grid-3 .media-item:nth-child(3) {
    grid-column: 2;
}

.media-grid-3 .media-item:nth-child(2) img,
.media-grid-3 .media-item:nth-child(2) video,
.media-grid-3 .media-item:nth-child(3) img,
.media-grid-3 .media-item:nth-child(3) video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Four images - WhatsApp grid */
.media-grid-4 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
    max-width: 320px;
    width: fit-content;
}

.media-grid-4 .media-item {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
}

.media-grid-4 .media-item img,
.media-grid-4 .media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    display: block;
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

/* Voice Message Styles */
.voice-message {
    min-width: 260px;
    max-width: 100%;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    margin: 4px 0;
    width: fit-content;
}

.message.own .voice-message {
    background: rgba(0, 0, 0, 0.2);
}

.message.other .voice-message {
    background: rgba(255, 255, 255, 0.1);
}

/* Simple Voice Message Design */
.voice-message-simple {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    min-width: 280px;
    max-width: 100%;
    width: fit-content;
}

.message.own .voice-message-simple {
    background: rgba(0, 0, 0, 0.3);
}

.voice-play-btn-simple {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--wa-accent);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
    font-size: 16px;
}

.voice-play-btn-simple:hover {
    transform: scale(1.08);
    background: var(--wa-accent-hover, #1a73e8);
}

.voice-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.voice-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--wa-text);
}

.voice-duration-simple {
    font-size: 12px;
    color: var(--wa-text-muted);
    font-weight: 500;
}

.voice-speed-btn-simple {
    padding: 4px 10px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: var(--wa-text-muted);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}

.voice-speed-btn-simple:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--wa-text);
}

.voice-message-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 200px;
}

.voice-play-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--wa-accent);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
    font-size: 14px;
}

.voice-play-btn:hover {
    transform: scale(1.08);
    background: var(--wa-accent-hover, #1a73e8);
}

.voice-play-btn:active {
    transform: scale(0.95);
}

.voice-play-btn.playing {
    background: rgba(255, 255, 255, 0.3);
    animation: pulse-playing 1.5s infinite;
}

@keyframes pulse-playing {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
}

.voice-waveform {
    flex: 1;
    height: 40px;
    background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.2) 100%);
    border-radius: 20px;
    overflow: hidden;
    cursor: pointer;
    min-width: 120px;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}

.voice-waveform canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/* Make waveform cursor more visible */
.voice-waveform wave {
    cursor: pointer;
}

/* Add hover effect */
.voice-waveform:hover {
    background: linear-gradient(180deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.25) 100%);
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.4), 0 0 10px rgba(74, 222, 128, 0.2);
}

/* Playing state glow */
.voice-message:has(.voice-play-btn.playing) .voice-waveform {
    border-color: rgba(74, 222, 128, 0.3);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3), 0 0 15px rgba(74, 222, 128, 0.3);
}

/* Waveform cursor styling */
.voice-waveform wave[part="progress"] {
    background: #22c55e !important;
}

.voice-duration {
    font-size: 11px;
    color: var(--wa-text-muted);
    min-width: 35px;
    text-align: center;
    font-weight: 500;
    font-variant-numeric: tabular-nums;
}

.voice-speed-btn {
    padding: 3px 8px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--wa-text-muted);
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.voice-speed-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--wa-text);
    border-color: rgba(255, 255, 255, 0.2);
}

.voice-speed-btn:active {
    transform: scale(0.95);
}

/* Voice Recording Overlay */
.voice-recording-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.recording-content {
    text-align: center;
    padding: 24px;
    position: relative;
    z-index: 100;
    pointer-events: none; /* Allow clicks to pass through to children */
}

.recording-content > * {
    pointer-events: auto; /* Re-enable clicks on direct children */
}

.recording-timer {
    font-size: 48px;
    font-weight: 700;
    color: var(--wa-text);
    margin-bottom: 24px;
    font-family: monospace;
}

.recording-timer.recording {
    color: #ef4445;
    /* Removed pulse animation - keep timer static */
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.recording-waveform {
    width: 300px;
    height: 80px;
    margin: 0 auto 24px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    overflow: hidden;
    pointer-events: none; /* Allow clicks to pass through */
}

.recording-waveform canvas {
    width: 100% !important;
    height: 100% !important;
}

.recording-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    position: relative;
    z-index: 100;
    pointer-events: none; /* Allow container to not block, children will override */
}

.recording-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.2s;
    position: relative;
    z-index: 110;
    pointer-events: auto; /* Enable clicks on buttons */
}

.recording-btn.cancel {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4445;
}

.recording-btn.cancel:hover {
    background: rgba(239, 68, 68, 0.3);
}

.recording-btn:not(.cancel):not(.send) {
    background: var(--wa-accent);
    color: white;
}

.recording-btn:not(.cancel):not(.send):hover {
    transform: scale(1.1);
}

.recording-btn:not(.cancel):not(.send).recording {
    background: #ef4445;
    animation: pulse-btn 1.5s infinite;
}

@keyframes pulse-btn {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    50% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
}

.recording-btn.send {
    background: var(--wa-accent) !important;
    color: white !important;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
}

.recording-btn.send:hover:not(:disabled) {
    transform: scale(1.1);
}

.recording-btn.send:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed;
    pointer-events: none !important;
}

.recording-btn.send:not(:disabled) {
    cursor: pointer !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    background: var(--wa-accent) !important;
}

/* Voice record button in input area */
.voice-record-btn {
    background: none;
    border: none;
    color: var(--wa-text-muted);
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.voice-record-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--wa-accent);
}

.voice-record-btn.recording {
    color: #ef4445;
    animation: pulse-icon 1s infinite;
}

@keyframes pulse-icon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
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
    
    /* Fix input row on mobile */
    .input-row {
        gap: 8px;
        min-width: 0;
    }
    
    /* Fix message input on mobile */
    #messageInput {
        min-width: 0;
        flex: 1;
        padding: 10px 14px;
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    /* Fix send button on mobile */
    .send-btn {
        width: 40px;
        height: 40px;
        min-width: 40px;
        flex-shrink: 0;
    }
    
    /* Fix attach and voice buttons on mobile */
    .attach-btn, .voice-record-btn {
        flex-shrink: 0;
        width: 40px;
        min-width: 40px;
    }
    
    /* Voice recording overlay on mobile */
    .voice-recording-overlay {
        position: fixed;
    }
    
    .recording-timer {
        font-size: 36px;
    }
    
    .recording-waveform {
        width: 250px;
        height: 60px;
    }
    
    .recording-btn {
        width: 48px;
        height: 48px;
    }
    
    /* Voice messages on mobile */
    .voice-message {
        min-width: 160px;
        max-width: 100%;
        padding: 4px 8px;
    }

    .voice-message-controls {
        gap: 4px;
        min-width: 120px;
    }

    .voice-play-btn {
        width: 26px;
        height: 26px;
        font-size: 9px;
        flex-shrink: 0;
    }

    .voice-waveform {
        height: 24px;
        min-width: 60px;
        flex: 1;
        border-radius: 12px;
    }

    .voice-duration {
        font-size: 8px;
        min-width: 24px;
        flex-shrink: 0;
    }

    .voice-speed-btn {
        padding: 2px 4px;
        font-size: 7px;
        flex-shrink: 0;
    }

    /* Simple voice messages on mobile */
    .voice-message-simple {
        padding: 8px 10px;
        min-width: 180px;
        gap: 8px;
        border-radius: 18px;
    }

    .voice-play-btn-simple {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }

    .voice-label {
        font-size: 11px;
    }

    .voice-duration-simple {
        font-size: 10px;
    }
    
    /* Voice recording overlay on small screens */
    .recording-waveform {
        width: 200px;
        height: 50px;
    }

    .recording-btn {
        width: 44px;
        height: 44px;
    }
}

@media (max-width: 600px) {
    /* Smaller voice messages on very small screens */
    .voice-message {
        min-width: 150px;
        padding: 3px 6px;
    }

    .voice-message-controls {
        gap: 3px;
        min-width: 110px;
    }

    .voice-play-btn {
        width: 24px;
        height: 24px;
        font-size: 9px;
    }

    .voice-waveform {
        height: 22px;
        min-width: 50px;
    }

    .voice-duration {
        font-size: 7px;
        min-width: 22px;
    }

    .voice-speed-btn {
        display: none; /* Hide speed control on very small screens */
    }

    /* Simple voice messages on very small screens */
    .voice-message-simple {
        padding: 6px 8px;
        min-width: 160px;
        gap: 6px;
        border-radius: 16px;
    }

    .voice-play-btn-simple {
        width: 26px;
        height: 26px;
        font-size: 11px;
    }

    .voice-label {
        font-size: 10px;
    }

    .voice-duration-simple {
        font-size: 9px;
    }

    /* Voice recording overlay on very small screens */
    .recording-waveform {
        width: 180px;
        height: 45px;
    }

    .recording-btn {
        width: 40px;
        height: 40px;
    }
}

/* Small screens styles */
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
    
    /* Fix input row on small screens */
    .input-row {
        gap: 6px;
    }
    
    /* Smaller buttons on small screens */
    .send-btn {
        width: 38px;
        height: 38px;
        min-width: 38px;
    }
    
    .attach-btn, .voice-record-btn {
        width: 38px;
        min-width: 38px;
        padding: 6px;
    }
    
    /* Voice messages on small screens */
    .voice-message {
        min-width: 160px;
        padding: 4px 8px;
    }

    .voice-message-controls {
        gap: 4px;
        min-width: 120px;
    }

    .voice-play-btn {
        width: 26px;
        height: 26px;
        font-size: 9px;
    }

    .voice-waveform {
        height: 24px;
        min-width: 60px;
    }

    .voice-duration {
        font-size: 8px;
        min-width: 24px;
    }

    .voice-speed-btn {
        display: none; /* Hide speed control on very small screens */
    }

    /* Simple voice messages on small screens */
    .voice-message-simple {
        padding: 8px 10px;
        min-width: 170px;
        gap: 8px;
        border-radius: 18px;
    }

    .voice-play-btn-simple {
        width: 28px;
        height: 28px;
        font-size: 11px;
    }

    .voice-label {
        font-size: 11px;
    }

    .voice-duration-simple {
        font-size: 10px;
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
    .media-grid-single img,
    .media-grid-single video {
        max-height: 180px;
        max-width: 100%;
    }

    .media-grid-two,
    .media-grid-3,
    .media-grid-4 {
        max-width: 280px;
        width: fit-content;
    }

    .media-grid-two .media-item,
    .media-grid-3 .media-item,
    .media-grid-4 .media-item {
        width: 100%;
        aspect-ratio: 1;
        overflow: hidden;
    }

    .media-grid-two .media-item img,
    .media-grid-two .media-item video,
    .media-grid-3 .media-item img,
    .media-grid-3 .media-item video,
    .media-grid-4 .media-item img,
    .media-grid-4 .media-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
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

// Message sending queue to prevent race conditions when sending fast
let messageSendQueue = [];
let isSendingMessage = false;
let lastSentMessageId = 0;

// Process message queue sequentially
function processMessageQueue() {
    if (isSendingMessage || messageSendQueue.length === 0) return;

    const messageData = messageSendQueue.shift();
    isSendingMessage = true;

    // Check if this is a media message
    if (messageData.isMedia) {
        processMediaMessage(messageData);
        return;
    }

    fetch(`{{ route('chat.store', $conversation) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content: messageData.content })
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

            // Sync with RealTime state to prevent polling from missing messages
            lastSentMessageId = data.message.id;
            if (window.RealTime && window.RealTime.updateLastMessageId) {
                window.RealTime.updateLastMessageId(data.message.id);
            }

            messageData.resolve(data);
        } else {
            messageData.reject(new Error(data.error || 'Failed to send message'));
        }
    })
    .catch(err => {
        console.error('Send message error:', err);
        messageData.reject(err);
    })
    .finally(() => {
        isSendingMessage = false;
        messageData.input.disabled = false;
        messageData.sendButton.disabled = false;
        messageData.input.value = '';
        // Process next message in queue if any
        if (messageSendQueue.length > 0) {
            setTimeout(processMessageQueue, 50); // Small delay between sends
        }
    });
}

// Send message with queue to prevent race conditions
function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    const hasMedia = selectedFiles.length > 0;

    if (!content && !hasMedia) return;

    const sendButton = document.getElementById('sendButton');

    // Disable input but don't block - queue will handle ordering
    input.disabled = true;
    sendButton.disabled = true;

    // If has media, send as FormData (also queued)
    if (hasMedia) {
        sendMediaMessage(content, null);
        return;
    }

    // Create a promise for this message
    return new Promise((resolve, reject) => {
        // Add to queue
        messageSendQueue.push({
            content: content,
            input: input,
            sendButton: sendButton,
            resolve: resolve,
            reject: reject
        });

        // Process queue
        processMessageQueue();
    });
}

// Send media message (supports multiple files in one message) with queue
function sendMediaMessage(content, mediaFile) {
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    // Disable input
    input.disabled = true;
    sendButton.disabled = true;

    // Create a promise for this media message
    return new Promise((resolve, reject) => {
        // Add to queue
        messageSendQueue.push({
            content: content,
            input: input,
            sendButton: sendButton,
            resolve: resolve,
            reject: reject,
            isMedia: true
        });

        // Process queue
        processMessageQueue();
    });
}

// Process media message from queue
function processMediaMessage(messageData) {
    const formData = new FormData();
    if (messageData.content) {
        formData.append('content', messageData.content);
    }

    // Append ALL selected files
    selectedFiles.forEach((file) => {
        formData.append('media[]', file);
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

            // Sync with RealTime state
            lastSentMessageId = data.message.id;
            if (window.RealTime && window.RealTime.updateLastMessageId) {
                window.RealTime.updateLastMessageId(data.message.id);
            }

            messageData.input.value = '';
            clearMediaPreview();
            messageData.resolve(data);
        } else {
            alert(data.error || window.chatTranslations.failed_to_send_media);
            messageData.reject(new Error(data.error || 'Failed to send media'));
        }
    })
    .catch(err => {
        console.error('Error sending media:', err);
        alert(window.chatTranslations.error_sending_media);
        messageData.reject(err);
    })
    .finally(() => {
        isSendingMessage = false;
        messageData.input.disabled = false;
        messageData.sendButton.disabled = false;
        // Process next message in queue if any
        if (messageSendQueue.length > 0) {
            setTimeout(processMessageQueue, 50);
        }
    });
}

// Add message to chat - make it globally accessible
window.addMessage = function(msg) {
    const container = document.getElementById('chatMessages');
    if (!container) {
        console.error('addMessage: chatMessages container not found');
        return;
    }
    
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
                            <div class="media-item">
                                <img src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, 0)">
                            </div>
                        </div></div>`;
                    } else if (media.type === 'video') {
                        contentHtml += `<div class="message-media-album"><div class="media-grid-single">
                            <div class="media-item video">
                                <video src="/storage/${escapeHtml(media.path)}"></video>
                                <div class="media-overlay" onclick="openMediaViewerFromAlbum(this, ${msg.id}, 0)">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div></div>`;
                    }
                } else if (displayCount === 2) {
                    contentHtml += `<div class="message-media-album"><div class="media-grid-two">`;
                    mediaItems.slice(0, 2).forEach((media, index) => {
                        if (media.type === 'image') {
                            contentHtml += `<div class="media-item">
                                <img src="/storage/${escapeHtml(media.path)}" onclick="openMediaViewerFromAlbum(this, ${msg.id}, ${index})">
                            </div>`;
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
    } else if (msg.type === 'voice' && msg.media_path) {
        // Voice message - simple design
        const duration = msg.duration || 0;
        contentHtml += `<div class="voice-message-simple" data-audio-url="/storage/${escapeHtml(msg.media_path)}">
            <button class="voice-play-btn-simple" onclick="toggleVoiceMessage(this)"><i class="fas fa-play"></i></button>
            <div class="voice-info">
                <span class="voice-label">Voice Message</span>
                <span class="voice-duration-simple">${duration}s</span>
            </div>
            <button class="voice-speed-btn-simple" onclick="toggleVoiceSpeed(this)" title="${escapeHtml(window.chatTranslations.playback_speed)}">1x</button>
        </div>`;
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
let mediaListsInitialized = false;

// Lazy initialization of media lists (only when first clicked)
function initializeMediaLists() {
    if (mediaListsInitialized) return;
    mediaListsInitialized = true;

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
}

function openMediaViewerFromAlbum(element, messageId, index = 0) {
    // Initialize media lists on first click (lazy loading)
    initializeMediaLists();
    
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

// Accept group invite
function acceptGroupInvite(inviteLink) {
    if (!inviteLink) return;
    
    // Make POST request to accept invite
    fetch('/groups/accept-invite/' + encodeURIComponent(inviteLink), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to group chat
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                // Fallback: reload to show updated status
                window.location.reload();
            }
        } else {
            // Show error message
            alert(data.message || 'Failed to join group');
        }
    })
    .catch(error => {
        console.error('Error accepting invite:', error);
        alert('Failed to join group');
    });
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

    // Scroll to bottom after a short delay to ensure all content is rendered
    const container = document.getElementById('chatMessages');
    if (container) {
        // Wait for images to load
        const images = container.querySelectorAll('img');
        if (images.length > 0) {
            let loaded = 0;
            images.forEach(img => {
                if (img.complete) {
                    loaded++;
                } else {
                    img.addEventListener('load', () => {
                        loaded++;
                        if (loaded === images.length) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                }
            });
            // If all images already loaded
            if (loaded === images.length) {
                container.scrollTop = container.scrollHeight;
            }
        } else {
            // No images, scroll immediately
            container.scrollTop = container.scrollHeight;
        }
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
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        navigator.sendBeacon('/user/online-status/offline', formData);
    });

    
});

// Delivery confirmations and read-marking delegated to RealTime.js

// Translation strings for JavaScript
window.chatTranslations = {
    you: '{{ __('chat.you') }}',
    online: '{{ __('chat.online') }}',
    offline: '{{ __('chat.offline') }}',
    typing: '{{ __('chat.typing') }}',
    and: '{{ __('chat.and') }}',
    are_typing: '{{ __('chat.are_typing') }}',
    users_typing: '{{ __('chat.users_typing') }}',
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

// Typing indicator - sending only (receiving handled by realtime.js for both DM and group)
let typingTimeout;
let isTyping = false;

document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            if (!isTyping) {
                isTyping = true;
                sendTypingStatus(true);
            }

            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
                sendTypingStatus(false);
            }, 2000);
        });
    }
});

function sendTypingStatus(isTyping) {
    fetch(`/chat/{{ $conversation->slug }}/typing`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_typing: isTyping })
    })
    .catch(error => console.error('Send typing error:', error));
}

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

// Note: All polling (messages, typing, online status, etc.) is handled by resources/js/legacy/realtime.js

// ============================================
// Voice Message Recording and Playback
// ============================================

// Import WaveSurfer dynamically
let WaveSurfer = null;
let waveSurferLoading = false;

const loadWaveSurfer = async () => {
    if (WaveSurfer) {
        return WaveSurfer;
    }
    
    if (waveSurferLoading) {
        // Wait for existing load
        while (waveSurferLoading) {
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        return WaveSurfer;
    }
    
    waveSurferLoading = true;
    try {
        const module = await import('https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.esm.js');
        WaveSurfer = module.default;
        console.log('WaveSurfer loaded successfully');
    } catch (error) {
        console.error('Failed to load WaveSurfer:', error);
    } finally {
        waveSurferLoading = false;
    }
    return WaveSurfer;
};

// Voice recording state
let voiceRecordingState = {
    isRecording: false,
    isPaused: false,
    mediaRecorder: null,
    audioChunks: [],
    startTime: null,
    pausedTime: 0,
    totalPausedDuration: 0,
    timerInterval: null,
    waveform: null,
    audioBlob: null,
};

// Initialize recording waveform
async function initRecordingWaveform() {
    // Clear any existing waveform
    const container = document.getElementById('recordingWaveform');
    if (container) {
        container.innerHTML = '';
    }
    
    // We'll use canvas-based visualization during recording
    // No need to initialize WaveSurfer for recording
    console.log('Recording waveform initialized (canvas-based)');
}

// Toggle voice recording overlay
function toggleVoiceRecording() {
    const overlay = document.getElementById('voiceRecordingOverlay');
    
    // Check permissions before opening overlay
    if (overlay.style.display !== 'flex') {
        checkMicrophonePermission().then(granted => {
            if (granted) {
                overlay.style.display = 'flex';
                initRecordingWaveform();
                resetRecordingState();
            }
        }).catch(err => {
            console.error('Permission check failed:', err);
        });
    } else {
        overlay.style.display = 'none';
        cancelVoiceRecording();
    }
}

// Check microphone permissions
async function checkMicrophonePermission() {
    try {
        // Check if browser supports permissions API
        if (!navigator.permissions) {
            console.log('Permissions API not supported, will request access directly');
            return true;
        }
        
        const result = await navigator.permissions.query({ name: 'microphone' });
        console.log('Microphone permission state:', result.state);
        
        if (result.state === 'denied') {
            alert('Microphone permission was denied. Please enable it in your browser settings:\n\n' +
                  'Chrome/Edge: Click the lock icon → Microphone → Allow\n' +
                  'Firefox: Click the lock icon → Permissions → Microphone → Allow\n' +
                  'Safari: Settings → Websites → Microphone → Allow\n\n' +
                  'Then refresh the page.');
            return false;
        }
        
        return true;
    } catch (error) {
        console.error('Error checking microphone permission:', error);
        // If we can't check, still try to request access
        return true;
    }
}

function resetRecordingState() {
    const existingWaveform = voiceRecordingState.waveform;
    
    voiceRecordingState = {
        isRecording: false,
        isPaused: false,
        mediaRecorder: null,
        audioChunks: [],
        startTime: null,
        pausedTime: 0,
        totalPausedDuration: 0,
        timerInterval: null,
        waveform: existingWaveform,
        audioBlob: null,
    };

    document.getElementById('recordingTimer').textContent = '00:00';
    document.getElementById('recordingTimer').classList.remove('recording');
    document.getElementById('recordToggleBtn').innerHTML = '<i class="fas fa-microphone"></i>';
    document.getElementById('recordToggleBtn').classList.remove('recording');
    document.getElementById('recordToggleBtn').title = '{{ __('chat.start_recording') }}';
    
    const sendBtn = document.getElementById('sendVoiceBtn');
    sendBtn.disabled = true;
    sendBtn.setAttribute('disabled', 'disabled');
}

// Start/stop recording
async function toggleVoiceRecord() {
    console.log('Toggle voice record clicked, state:', {
        isRecording: voiceRecordingState.isRecording,
        isPaused: voiceRecordingState.isPaused,
        recorderState: voiceRecordingState.mediaRecorder?.state
    });
    
    if (!voiceRecordingState.isRecording && !voiceRecordingState.isPaused) {
        await startRecording();
    } else if (voiceRecordingState.isRecording) {
        // Stop recording (not pause)
        stopRecording();
    } else if (voiceRecordingState.isPaused) {
        resumeRecording();
    } else {
        // Recorder is inactive, close overlay
        console.log('Recorder inactive, closing overlay');
        document.getElementById('voiceRecordingOverlay').style.display = 'none';
        resetRecordingState();
    }
}

async function startRecording() {
    try {
        // Check if browser supports getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Your browser does not support audio recording. Please use a modern browser like Chrome, Firefox, or Edge.');
        }
        
        // Check if page is served over HTTPS or localhost
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            console.warn('Warning: Microphone access requires HTTPS in production. Current protocol:', window.location.protocol);
        }
        
        console.log('Requesting microphone access...');
        
        // Get available audio devices to select proper microphone
        let audioConstraints = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                sampleRate: 44100,
                autoGainControl: true
            }
        };
        
        // Try to find a real microphone (not monitor/output devices)
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const audioInputs = devices.filter(device => device.kind === 'audioinput');
            console.log('Available microphones:', audioInputs.map(d => ({ label: d.label || '(hidden by browser)', id: d.deviceId })));
            
            if (audioInputs.length > 0) {
                // Find a microphone that's not a monitor device
                const realMic = audioInputs.find(input => 
                    !input.label.toLowerCase().includes('monitor') && 
                    !input.label.toLowerCase().includes('output') &&
                    !input.label.toLowerCase().includes('dummy') &&
                    input.label.trim() !== ''
                );
                
                if (realMic && realMic.label) {
                    console.log('Selected microphone:', realMic.label);
                    audioConstraints.audio = {
                        deviceId: { ideal: realMic.deviceId },
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100,
                        autoGainControl: true
                    };
                } else {
                    // Use first available microphone with ideal (not exact) constraint
                    console.log('Using first available microphone (label hidden by browser)');
                    audioConstraints.audio.deviceId = { ideal: audioInputs[0].deviceId };
                }
            }
        } catch (err) {
            console.warn('Could not enumerate devices, using default microphone:', err);
        }
        
        const stream = await navigator.mediaDevices.getUserMedia(audioConstraints);

        console.log('Microphone access granted!');
        console.log('Audio tracks:', stream.getAudioTracks().map(t => ({ label: t.label, enabled: t.enabled, muted: t.muted })));

        voiceRecordingState.mediaRecorder = new MediaRecorder(stream, {
            mimeType: MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 
                      MediaRecorder.isTypeSupported('audio/ogg') ? 'audio/ogg' : 
                      MediaRecorder.isTypeSupported('audio/mp4') ? 'audio/mp4' : ''
        });
        
        console.log('Using MIME type:', voiceRecordingState.mediaRecorder.mimeType);
        
        voiceRecordingState.audioChunks = [];

        voiceRecordingState.mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                voiceRecordingState.audioChunks.push(event.data);
                console.log('Audio chunk received, size:', event.data.size);
            }
        };

        // Store mimeType before onstop fires
        const recordedMimeType = voiceRecordingState.mediaRecorder.mimeType || 'audio/webm';
        console.log('Using MIME type:', recordedMimeType);

        voiceRecordingState.mediaRecorder.onstop = () => {
            console.log('MediaRecorder stopped event fired');
            console.log('Audio chunks collected:', voiceRecordingState.audioChunks.length);

            if (voiceRecordingState.audioChunks.length > 0) {
                // Use the actual recorded MIME type
                voiceRecordingState.audioBlob = new Blob(voiceRecordingState.audioChunks, { type: recordedMimeType });
                
                console.log('✅ Recording created, blob size:', voiceRecordingState.audioBlob.size, 'bytes');
                console.log('✅ Blob type:', voiceRecordingState.audioBlob.type);
                
                // Enable send button
                enableSendButton();
            } else {
                console.error('❌ No audio chunks collected!');
                alert('No audio was recorded. Please try again and make sure to speak into the microphone.');
            }
        };

        voiceRecordingState.mediaRecorder.onerror = (event) => {
            console.error('MediaRecorder error:', event.error);
            alert('Recording error: ' + event.error.name + ' - ' + event.error.message);
        };

        voiceRecordingState.mediaRecorder.start(100);
        voiceRecordingState.isRecording = true;
        voiceRecordingState.isPaused = false;
        voiceRecordingState.startTime = Date.now();

        // Update UI
        document.getElementById('recordingTimer').classList.add('recording');
        document.getElementById('recordToggleBtn').innerHTML = '<i class="fas fa-pause"></i>';
        document.getElementById('recordToggleBtn').classList.add('recording');
        document.getElementById('recordToggleBtn').title = '{{ __('chat.stop_recording') }}';

        // Start timer
        clearInterval(voiceRecordingState.timerInterval);
        voiceRecordingState.timerInterval = setInterval(updateRecordingTimer, 1000);

        // Connect to waveform (visual feedback) - start canvas visualization
        const analyser = createAudioAnalyser(stream);
        updateRecordingWaveform(analyser);
        
        console.log('Recording started with canvas waveform');

    } catch (error) {
        console.error('Error accessing microphone:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        
        let errorMessage = '{{ __('chat.microphone_access_denied') }}\n\n';
        
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Permission was denied. To enable microphone access:\n';
            errorMessage += '1. Click the lock icon in your browser address bar\n';
            errorMessage += '2. Find "Microphone" or "Permissions"\n';
            errorMessage += '3. Set to "Allow"\n';
            errorMessage += '4. Refresh the page and try again';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'No microphone was found. Please connect a microphone and try again.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Your microphone is being used by another application. Please close other apps and try again.';
        } else if (error.name === 'OverconstrainedError') {
            errorMessage += 'Your microphone does not support the required audio settings.\n\n';
            errorMessage += 'Tip: Try selecting a different microphone in your browser settings.';
        } else if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            errorMessage += '\n\n⚠️ IMPORTANT: Microphone access requires HTTPS.\n';
            errorMessage += 'You are currently using: ' + window.location.protocol + '\n';
            errorMessage += 'Please use HTTPS or localhost for microphone access.';
        }
        
        alert(errorMessage);
        cancelVoiceRecording();
    }
}

function createAudioAnalyser(stream) {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const source = audioContext.createMediaStreamSource(stream);
    const analyser = audioContext.createAnalyser();
    analyser.fftSize = 256;
    source.connect(analyser);
    return analyser;
}

function updateRecordingWaveform(analyser) {
    if (!voiceRecordingState.isRecording || voiceRecordingState.isPaused) return;
    
    const dataArray = new Uint8Array(analyser.frequencyBinCount);
    analyser.getByteFrequencyData(dataArray);
    
    // Always use canvas-based visualization (more reliable)
    drawWaveformOnCanvas(dataArray);
    
    requestAnimationFrame(() => updateRecordingWaveform(analyser));
}

function drawWaveformOnCanvas(dataArray) {
    const container = document.querySelector('#recordingWaveform');
    if (!container) return;

    let canvas = container.querySelector('canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        container.innerHTML = '';
        container.appendChild(canvas);
    }

    const ctx = canvas.getContext('2d');
    const width = canvas.width = container.offsetWidth || 300;
    const height = canvas.height = container.offsetHeight || 80;

    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#4ade80';

    const barWidth = (width / dataArray.length) * 2.5;
    let x = 0;

    for (let i = 0; i < dataArray.length; i++) {
        const barHeight = (dataArray[i] / 255) * height;
        ctx.fillRect(x, (height - barHeight) / 2, barWidth - 1, barHeight);
        x += barWidth + 1;
    }
}

function pauseRecording() {
    if (voiceRecordingState.mediaRecorder && voiceRecordingState.isRecording) {
        voiceRecordingState.pausedTime = Date.now();
        voiceRecordingState.mediaRecorder.pause();
        voiceRecordingState.isPaused = true;
        voiceRecordingState.isRecording = false;

        document.getElementById('recordToggleBtn').innerHTML = '<i class="fas fa-microphone"></i>';
        document.getElementById('recordToggleBtn').classList.remove('recording');
        document.getElementById('recordToggleBtn').title = '{{ __('chat.start_recording') }}';
        clearInterval(voiceRecordingState.timerInterval);
    }
}

function resumeRecording() {
    if (voiceRecordingState.mediaRecorder && voiceRecordingState.isPaused) {
        voiceRecordingState.mediaRecorder.resume();
        voiceRecordingState.isPaused = false;
        voiceRecordingState.isRecording = true;
        voiceRecordingState.totalPausedDuration += Date.now() - voiceRecordingState.pausedTime;

        document.getElementById('recordToggleBtn').innerHTML = '<i class="fas fa-pause"></i>';
        document.getElementById('recordToggleBtn').classList.add('recording');
        document.getElementById('recordToggleBtn').title = '{{ __('chat.stop_recording') }}';
        voiceRecordingState.timerInterval = setInterval(updateRecordingTimer, 1000);
    }
}

function stopRecording() {
    if (voiceRecordingState.mediaRecorder && voiceRecordingState.mediaRecorder.state !== 'inactive') {
        voiceRecordingState.mediaRecorder.stop();
        if (voiceRecordingState.mediaRecorder.stream) {
            voiceRecordingState.mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }

        voiceRecordingState.isRecording = false;
        voiceRecordingState.isPaused = false;

        document.getElementById('recordToggleBtn').innerHTML = '<i class="fas fa-microphone"></i>';
        document.getElementById('recordToggleBtn').classList.remove('recording');
        document.getElementById('recordToggleBtn').title = '{{ __('chat.start_recording') }}';
        clearInterval(voiceRecordingState.timerInterval);
    }
}

function updateRecordingTimer() {
    if (!voiceRecordingState.startTime) return;
    
    // Calculate elapsed time excluding pause duration
    const now = Date.now();
    const totalElapsed = now - voiceRecordingState.startTime;
    const elapsed = Math.floor((totalElapsed - voiceRecordingState.totalPausedDuration) / 1000);
    
    const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
    const seconds = (elapsed % 60).toString().padStart(2, '0');
    document.getElementById('recordingTimer').textContent = `${minutes}:${seconds}`;
    
    // Max 5 minutes
    if (elapsed >= 300) {
        pauseRecording();
    }
}

function cancelVoiceRecording() {
    console.log('Canceling voice recording');
    
    if (voiceRecordingState.mediaRecorder) {
        if (voiceRecordingState.isRecording || voiceRecordingState.isPaused) {
            voiceRecordingState.mediaRecorder.stop();
        }
        // Stop all audio tracks
        if (voiceRecordingState.mediaRecorder.stream) {
            voiceRecordingState.mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }
    }

    clearInterval(voiceRecordingState.timerInterval);

    if (voiceRecordingState.waveform) {
        try {
            voiceRecordingState.waveform.destroy();
        } catch (e) {
            console.error('Error destroying waveform:', e);
        }
        voiceRecordingState.waveform = null;
    }

    document.getElementById('voiceRecordingOverlay').style.display = 'none';
    resetRecordingState();
}

// Send voice message
async function sendVoiceMessage() {
    const sendBtn = document.getElementById('sendVoiceBtn');
    if (!sendBtn || sendBtn.disabled) return;

    if (!voiceRecordingState.audioBlob) {
        alert('No recording found. Please try again.');
        return;
    }
    
    // Validate duration (minimum 1 second)
    const elapsed = voiceRecordingState.startTime ? Math.floor((Date.now() - voiceRecordingState.startTime - voiceRecordingState.totalPausedDuration) / 1000) : 0;
    if (elapsed < 1) {
        alert('Recording is too short. Please record at least 1 second.');
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        return;
    }

    // Disable send button
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const formData = new FormData();
    const filename = 'voice-message-' + Date.now() + '.webm';
    formData.append('voice_message', voiceRecordingState.audioBlob, filename);
    formData.append('duration', elapsed > 0 ? elapsed : 1);
    formData.append('waveform_peaks', JSON.stringify(generateWaveformPeaks()));

    try {
        const response = await fetch(`{{ route('chat.store', $conversation) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            if (data.message) {
                appendVoiceMessage(data.message);
                const messagesContainer = document.getElementById('chatMessages');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }
            cancelVoiceRecording();
        } else {
            let errorMsg = 'Failed to send voice message';
            if (data.message) errorMsg += ': ' + data.message;
            alert(errorMsg);
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    } catch (error) {
        alert('Network error. Please check your connection and try again.');
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
}

// Enable send button function - replaces button element entirely
function enableSendButton() {
    const sendBtn = document.getElementById('sendVoiceBtn');
    if (!sendBtn) {
        console.error('❌ Send button not found');
        return;
    }
    
    // Create a new button to replace the disabled one
    const newSendBtn = sendBtn.cloneNode(true);
    newSendBtn.disabled = false;
    newSendBtn.removeAttribute('disabled');
    newSendBtn.style.setProperty('pointer-events', 'auto', 'important');
    newSendBtn.style.setProperty('cursor', 'pointer', 'important');
    newSendBtn.style.setProperty('opacity', '1', 'important');
    newSendBtn.style.setProperty('background', 'var(--wa-accent)', 'important');
    
    // Add click handler
    newSendBtn.onclick = function() {
        console.log('🎤 Send clicked!');
        sendVoiceMessage();
    };
    
    // Replace the old button with the new one
    sendBtn.parentNode.replaceChild(newSendBtn, sendBtn);
    
    console.log('✅ Send button enabled and replaced');
    console.log('✅ New button disabled:', newSendBtn.disabled);
    console.log('✅ New button has disabled attr:', newSendBtn.hasAttribute('disabled'));
}

function generateWaveformPeaks() {
    // Generate realistic-looking waveform peaks
    // Create a pattern that rises and falls like actual audio
    const peaks = [];
    const numPeaks = 50;
    
    // Generate peaks with varying amplitudes
    for (let i = 0; i < numPeaks; i++) {
        // Create a wave-like pattern with some randomness
        const baseAmplitude = Math.sin(i / numPeaks * Math.PI); // Bell curve
        const randomness = Math.random() * 0.5 + 0.5; // 0.5 to 1.0
        const peak = baseAmplitude * randomness;
        peaks.push(Math.max(0.1, peak)); // Ensure minimum visibility
    }
    
    return peaks;
}

function appendVoiceMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    const isOwn = message.sender_id === {{ auth()->id() }};
    const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const audioUrl = '/storage/' + message.media_path;

    const messageHtml = `
        <div class="message ${isOwn ? 'own' : 'other'}" data-message-id="${message.id}">
            ${!isOwn ? `<div class="message-avatar"><img src="${escapeHtml(message.sender.avatar_url)}" alt="${escapeHtml(message.sender.username)}"></div>` : ''}
            <div class="message-bubble">
                ${!isOwn ? `<div class="sender-name">${escapeHtml(message.sender.username)}</div>` : ''}
                <div class="message-content">
                    <div class="voice-message-simple" data-audio-url="${audioUrl}">
                        <button class="voice-play-btn-simple" onclick="toggleVoiceMessage(this)">
                            <i class="fas fa-play"></i>
                        </button>
                        <div class="voice-info">
                            <span class="voice-label">Voice Message</span>
                            <span class="voice-duration-simple">${message.duration || 0}s</span>
                        </div>
                        <button class="voice-speed-btn-simple" onclick="toggleVoiceSpeed(this)">1x</button>
                    </div>
                    <span class="message-time">
                        ${time}
                        ${isOwn ? '<i class="fas fa-check" title="{{ __('chat.sent') }}"></i>' : ''}
                    </span>
                </div>
                ${isOwn ? `<button class="delete-btn" onclick="deleteMessage(${message.id})" title="{{ __('chat.delete_message') }}"><i class="fas fa-trash"></i></button>` : ''}
            </div>
        </div>
    `;

    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
}

// Simple Voice Message Playback
let currentAudio = null;
let currentBtn = null;

async function toggleVoiceMessage(btn) {
    const voiceMessage = btn.closest('.voice-message-simple');
    if (!voiceMessage) return;

    const audioUrl = voiceMessage.dataset.audioUrl || voiceMessage.closest('.message').dataset.audioUrl;
    if (!audioUrl) return;

    // If clicking the same message
    if (currentBtn === btn) {
        if (currentAudio && currentAudio.paused) {
            await currentAudio.play();
            btn.innerHTML = '<i class="fas fa-pause"></i>';
        } else if (currentAudio) {
            currentAudio.pause();
            btn.innerHTML = '<i class="fas fa-play"></i>';
        }
        return;
    }

    // Stop previous audio
    if (currentAudio) {
        currentAudio.pause();
        if (currentBtn) {
            currentBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    }

    // Create new audio
    currentAudio = new Audio(audioUrl);
    currentBtn = btn;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    currentAudio.onplay = () => {
        btn.innerHTML = '<i class="fas fa-pause"></i>';
    };

    currentAudio.onpause = () => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
    };

    currentAudio.onended = () => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentBtn = null;
    };

    currentAudio.onerror = () => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentBtn = null;
    };

    try {
        await currentAudio.play();
    } catch (e) {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentBtn = null;
    }
}

// Draw waveform with progress
function drawWaveform(container, progress, duration) {
    container.innerHTML = '';
    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    container.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    const width = canvas.width = container.offsetWidth || 120;
    const height = canvas.height = container.offsetHeight || 40;
    const playWidth = width * progress;

    // Background
    ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
    ctx.fillRect(0, 0, width, height);

    // Played portion (solid green)
    ctx.fillStyle = '#22c55e';
    ctx.fillRect(0, 0, playWidth, height);

    // Unplayed bars (dim green)
    ctx.fillStyle = 'rgba(74, 222, 128, 0.3)';
    const barCount = Math.floor(width / 5);
    for (let i = 0; i < barCount; i++) {
        const x = i * 5;
        if (x > playWidth) {
            const barHeight = Math.random() * (height * 0.6) + (height * 0.2);
            ctx.fillRect(x, (height - barHeight) / 2, 2, barHeight);
        }
    }

    // Cursor line
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(playWidth, 0);
    ctx.lineTo(playWidth, height);
    ctx.stroke();
}

// Animate waveform
function animateWaveform(container) {
    const canvas = container.querySelector('canvas');
    if (!canvas || !currentAudio) return;

    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const duration = currentAudio.duration;

    function draw() {
        if (currentAudio.paused || currentAudio.ended) return;

        const progress = currentAudio.currentTime / duration;
        const playWidth = width * progress;

        ctx.clearRect(0, 0, width, height);

        // Background
        ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
        ctx.fillRect(0, 0, width, height);

        // Played (solid)
        ctx.fillStyle = '#22c55e';
        ctx.fillRect(0, 0, playWidth, height);

        // Cursor
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(playWidth, 0);
        ctx.lineTo(playWidth, height);
        ctx.stroke();

        animationFrame = requestAnimationFrame(draw);
    }
    draw();
}

// Toggle playback speed
function toggleVoiceSpeed(btn) {
    const speeds = ['1x', '1.25x', '1.5x', '2x'];
    const currentSpeed = parseFloat(btn.textContent);
    const currentIndex = speeds.indexOf(btn.textContent);
    const nextIndex = (currentIndex + 1) % speeds.length;
    const nextSpeed = speeds[nextIndex];

    btn.textContent = nextSpeed;
    console.log('Playback speed changed to:', nextSpeed);

    if (currentVoiceWaveform) {
        currentVoiceWaveform.setPlaybackRate(parseFloat(nextSpeed));
    }
}

// Initialize existing voice messages on page load
document.addEventListener('DOMContentLoaded', async () => {
    // Voice messages will be initialized when clicked
    console.log('Voice message player initialized');
});
</script>
@endsection
