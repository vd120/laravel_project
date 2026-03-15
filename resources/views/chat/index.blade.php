@extends('layouts.app')

@section('title', __('chat.messages'))

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat-index.css') }}">
@vite(['resources/js/legacy/chat-index.js'])
<script>
window.currentUserId = {{ auth()->id() }};
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
</script>

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
@endsection
