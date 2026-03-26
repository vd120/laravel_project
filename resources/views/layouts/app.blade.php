<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#111111">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nexus')</title>
    
    {{-- Performance: Preconnect to external resources --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    {{-- Fonts - Load asynchronously --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap"></noscript>
    
    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    {{-- Critical CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/comments.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-header.css') }}">

    {{-- Page-specific styles --}}
    @stack('styles')

    <style>
    /* Mobile message badge */
    .mobile-msg-badge {
        position: absolute;
        top: 2px;
        right: 8px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 600;
        min-width: 16px;
        height: 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
        z-index: 10;
    }
    .mobile-nav-inner a {
        position: relative;
    }
    </style>
</head>
<body id="app-body">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <header class="header">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="logo">Nexus</a>

            @auth
            <nav class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> {{ __('navigation.home') }}</a>
                <a href="{{ route('stories.index') }}" class="{{ request()->routeIs('stories.*') ? 'active' : '' }}"><i class="fas fa-circle-play"></i> {{ __('navigation.stories') }}</a>
                <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="fas fa-message"></i> {{ __('navigation.messages') }}</a>
                <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}"><i class="fas fa-robot"></i> {{ __('navigation.ai_assistant') }}</a>
            </nav>
            @endauth

            <div class="user-actions">
                @guest
                @include('partials.language-switcher')
                @endguest

                <button class="nav-action-btn" onclick="toggleTheme()" title="{{ __('messages.theme') }}">
                    <i class="fas fa-sun" id="theme-icon"></i>
                </button>

                @auth
                <div style="position: relative;">
                    <button class="nav-action-btn" id="notifBtn" onclick="toggleNotifications(event)">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notif-badge" style="display: none;">0</span>
                    </button>
                </div>

                <div style="position: relative;">
                    <button class="nav-user-btn" id="userBtn" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}">
                        </div>
                        <span>{{ auth()->user()->username }}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--text-muted);"></i>
                    </button>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                @else
                <a href="{{ route('login') }}" class="nav-action-btn">{{ __('auth.sign_in') }}</a>
                <a href="{{ route('register') }}" class="nav-action-btn primary">{{ __('auth.sign_up') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="dropdown-overlay" id="dropdownOverlay" onclick="closeAllDropdowns()"></div>

    @auth
    <!-- Notification Dropdown - Simple Modern Design -->
    <div class="dropdown-menu notif-panel" id="notifMenu">
        <div class="notif-header">
            <h3>{{ __('navigation.notifications') }}</h3>
            <div class="notif-header-actions">
                <button class="notif-action-btn" onclick="markAllRead(); return false;" title="{{ __('notifications.mark_all_read') }}">
                    <i class="fas fa-check"></i>
                    <span>{{ __('notifications.mark_all_read') }}</span>
                </button>
                <button class="notif-action-btn danger" onclick="clearAllNotifications(); return false;" title="{{ __('notifications.clear_all') }}">
                    <i class="fas fa-trash"></i>
                    <span>{{ __('notifications.clear_all') }}</span>
                </button>
            </div>
        </div>
        <div class="notif-list" id="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>{{ __('notifications.no_notifications') }}</p>
            </div>
        </div>
        <div class="notif-footer" style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="{{ route('notifications.index') }}" style="display: block; text-align: center; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">
                <i class="fas fa-th-list"></i> {{ __('notifications.view_all') }}
            </a>
        </div>
    </div>

    <!-- User Menu Dropdown - outside header for proper z-index -->
    <div class="dropdown-menu" id="userMenu">
        <a href="{{ route('users.show', auth()->user()) }}"><i class="fas fa-user"></i> {{ __('navigation.profile') }}</a>
        <a href="{{ route('users.saved-posts') }}"><i class="fas fa-bookmark"></i> {{ __('navigation.saved_posts') }}</a>
        <a href="{{ route('explore') }}"><i class="fas fa-compass"></i> {{ __('navigation.explore') }}</a>
        <a href="{{ route('hashtags.index') }}"><i class="fas fa-hashtag"></i> {{ __('hashtags.hashtags') }}</a>
        <a href="{{ route('ai.index') }}"><i class="fas fa-robot"></i> {{ __('navigation.ai_assistant') }}</a>
        @if(auth()->user()->is_admin)
        <a href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt"></i> {{ __('navigation.admin_panel') }}</a>
        @endif
        <div class="divider"></div>

        <!-- Push Notifications Settings -->
        <a href="javascript:void(0)" onclick="closeAllDropdowns(); setTimeout(() => showPushSettings(), 100);">
            <i class="fas fa-bell"></i> {{ __('notifications.enable_push') }}
        </a>

        <!-- Link to Notifications Page -->
        <a href="{{ route('notifications.index') }}">
            <i class="fas fa-bell"></i> {{ __('navigation.notifications') }}
        </a>

        <!-- Link to My Reports Page -->
        <a href="{{ route('reports.my-reports') }}">
            <i class="fas fa-flag"></i> {{ __('messages.my_reports') }}
        </a>

        <div class="divider"></div>
        @php
            $currentLocale = app()->getLocale();
            $supportedLocales = \App\Http\Controllers\LanguageController::getSupportedLocales();
        @endphp
        <div class="language-switcher" style="position: relative; display: block; width: 100%;">
            <button
                type="button"
                class="language-option"
                onclick="toggleUserLanguageDropdown()"
                aria-label="{{ __('messages.language') }}"
                aria-haspopup="true"
                aria-expanded="false"
                style="
                    width: 100%;
                    justify-content: space-between;
                    background: transparent;
                    border: none;
                    cursor: pointer;
                "
            >
                <span style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 18px;">🌐</span>
                    <span>{{ __('messages.language') }}</span>
                </span>
                <span style="opacity: 0.6; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    @if($currentLocale === 'ar')
                        ع
                    @else
                        EN
                    @endif
                    <span style="opacity: 0.5;">|</span>
                    <span style="opacity: 0.7;">
                        @if($currentLocale === 'ar')
                            EN
                        @else
                            ع
                        @endif
                    </span>
                    <svg style="width: 14px; height: 14px; transition: transform 0.2s;" id="user-lang-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            </button>

            {{-- Dropdown Menu --}}
            <div
                id="user-language-dropdown"
                class="language-dropdown"
                style="
                    display: none;
                    position: absolute;
                    bottom: 100%;
                    left: 0;
                    margin-bottom: 8px;
                    min-width: 200px;
                    z-index: 1001;
                    overflow: hidden;
                    padding: 8px;
                    direction: ltr !important;
                "
            >
                <div style="padding: 8px 12px; border-bottom: 1px solid var(--border); margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ __('messages.select_language') }}
                    </span>
                </div>
                @foreach($supportedLocales as $locale => $details)
                    <a
                        href="#"
                        onclick="switchUserLanguage('{{ $locale }}'); return false;"
                        class="language-option {{ $currentLocale === $locale ? 'active' : '' }}"
                        data-locale="{{ $locale }}"
                        style="
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            padding: 12px 14px;
                            border-radius: 8px;
                            text-decoration: none;
                            margin-bottom: 2px;
                        "
                    >
                        <span style="font-size: 18px;">{{ $details['flag'] }}</span>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 14px; font-weight: 500;">{{ $details['native_name'] }}</span>
                            @if($details['name'] !== $details['native_name'])
                                <span style="font-size: 11px; opacity: 0.6;">{{ $details['name'] }}</span>
                            @endif
                        </div>
                        @if($currentLocale === $locale)
                            <svg style="width: 16px; height: 16px; margin-left: auto; color: var(--primary);" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        
        <div class="divider"></div>
        <a href="{{ route('password.change') }}"><i class="fas fa-key"></i> {{ __('messages.change_password') }}</a>
        <button onclick="logout()" class="danger"><i class="fas fa-sign-out-alt"></i> {{ __('navigation.logout') }}</button>
    </div>
    @endauth

    <main class="app-layout">
        <div class="main-content">
            <script>
                // Global chat translations for JavaScript - MUST be before content yield
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
                    message_deleted: '{{ __('chat.message_deleted') }}',
                    failed_to_send_media: '{{ __('chat.failed_to_send_media') }}',
                    is_now_online: '{{ __('messages.is_now_online') }}',
                    error_sending_media: '{{ __('chat.error_sending_media') }}',
                    group: '{{ __('chat.group') }}',
                    invited_you_to_join: '{{ __('chat.invited_you_to_join') }}',
                    join: '{{ __('chat.join') }}',
                    sent: '{{ __('chat.sent') }}',
                    story_reply: '{{ __('chat.story_reply') }}',
                    seen: '{{ __('chat.seen') }}',
                    attach: '{{ __('chat.attach') }}',
                    type_a_message: '{{ __('chat.type_a_message') }}',
                    send: '{{ __('chat.send') }}',
                    close: '{{ __('chat.close') }}',
                    previous: '{{ __('chat.previous') }}',
                    next: '{{ __('chat.next') }}',
                    remove_all: '{{ __('chat.remove_all') }}',
                    delete_message: '{{ __('chat.delete_message') }}',
                    delete_for_everyone: '{{ __('chat.delete_for_everyone') }}',
                    delete_for_me: '{{ __('chat.delete_for_me') }}',
                    delete_message_desc: '{{ __('chat.delete_message_desc') }}',
                    delete_for_everyone_desc: '{{ __('chat.delete_for_everyone_desc') }}',
                    delete_for_me_desc: '{{ __('chat.delete_for_me_desc') }}',
                    confirm_delete: '{{ __('chat.confirm_delete') }}',
                    is_typing: '{{ __('chat.is_typing') }}',
                    typing: '{{ __('chat.typing') }}',
                    user: '{{ __('chat.user') }}',
                    someone: '{{ __('chat.someone') }}',
                    add_photo: '{{ __('chat.add_photo') }}',
                    new_group: '{{ __('chat.new_group') }}',
                    new_message: '{{ __('chat.new_message') }}',
                    search_or_start_chat: '{{ __('chat.search_or_start_chat') }}',
                    search_contacts: '{{ __('chat.search_contacts') }}',
                    no_messages_yet: '{{ __('chat.no_messages_yet') }}',
                    start_new_conversation: '{{ __('chat.start_new_conversation') }}',
                    start_conversation: '{{ __('chat.start_conversation') }}',
                    nexus_web: '{{ __('chat.nexus_web') }}',
                    welcome_message: '{{ __('chat.welcome_message') }}',
                    end_to_end_encrypted: '{{ __('chat.end_to_end_encrypted') }}',
                    start_chat: '{{ __('chat.start_chat') }}',
                    members_count: '{{ __('chat.members_count') }}',
                    member_count: '{{ __('chat.member_count') }}',
                    edit: '{{ __('chat.edit') }}',
                    clear_chat: '{{ __('chat.clear_chat') }}',
                    save_changes: '{{ __('chat.save_changes') }}',
                    cancel: '{{ __('chat.cancel') }}',
                    create: '{{ __('chat.create') }}',
                    reply: '{{ __('chat.reply') }}',
                    write_a_reply: '{{ __('chat.write_a_reply') }}',
                    delete_comment: '{{ __('chat.delete_comment') }}',
                    show_reply: '{{ __('chat.show_reply') }}',
                    show_replies: '{{ __('chat.show_replies') }}',
                    hide_comments: '{{ __('chat.hide_comments') }}',
                    like_comments_prompt: '{{ __('chat.like_comments_prompt') }}',
                    reply_comments_prompt: '{{ __('chat.reply_comments_prompt') }}',
                    delete_post: '{{ __('chat.delete_post') }}',
                    follow: '{{ __('chat.follow') }}',
                    following: '{{ __('chat.following') }}',
                    private: '{{ __('chat.private') }}',
                    public: '{{ __('chat.public') }}',
                    show_more: '{{ __('chat.show_more') }}',
                    show_less: '{{ __('chat.show_less') }}',
                    save_post: '{{ __('chat.save_post') }}',
                    saved_post: '{{ __('chat.saved_post') }}',
                    share: '{{ __('chat.share') }}',
                    write_a_comment: '{{ __('chat.write_a_comment') }}',
                    login_to_comment: '{{ __('chat.login_to_comment') }}',
                    login: '{{ __('chat.login') }}',
                    show_more_comments: '{{ __('chat.show_more_comments') }}',
                    like_posts_prompt: '{{ __('chat.like_posts_prompt') }}',
                    save_posts_prompt: '{{ __('chat.save_posts_prompt') }}',
                    clear_all: '{{ __('chat.clear_all') }}',
                    sent_toast: '{{ __('messages.sent') }}',
                    mark_as_read: '{{ __('messages.mark_as_read') }}',
                    delete_toast: '{{ __('messages.delete') }}',
                    delete_story: '{{ __('messages.delete_story') }}',
                    view_who_watched: '{{ __('messages.view_who_watched') }}',
                    username_validation: '{{ __('messages.username_validation') }}',
                    post_saved_success: '{{ __('messages.post_saved_success') }}',
                    post_removed_from_saved: '{{ __('messages.post_removed_from_saved') }}',
                    no_likes_yet: '{{ __('messages.no_likes_yet') }}',
                    could_not_load_likers: '{{ __('messages.could_not_load_likers') }}',
                    post_link_copied: '{{ __('messages.post_link_copied') }}',
                    failed_to_copy_link: '{{ __('messages.failed_to_copy_link') }}',
                    account_suspended_message: '{{ __('messages.account_suspended_message') }}',
                    please_verify_email_message: '{{ __('messages.please_verify_email_message') }}',
                    concurrent_login_message: '{{ __('messages.concurrent_login_message') }}',
                    logged_out_message: '{{ __('messages.logged_out_message') }}',
                    account_deleted_message: '{{ __('messages.account_deleted_message') }}',
                    account_status_changed: '{{ __('messages.account_status_changed') }}',
                    story_deleted_success: '{{ __('messages.story_deleted_success') }}',
                    failed_to_delete_story: '{{ __('messages.failed_to_delete_story') }}',
                    failed_to_send_message: '{{ __('messages.failed_to_send_message') }}',
                    story_shared_success: '{{ __('messages.story_shared_success') }}',
                    likes: '{{ __('messages.likes') }}',
                    comments_count: '{{ __('messages.comments_count') }}',
                    just_now: '{{ __('messages.just_now') }}',
                    minutes_ago_short: '{{ __('messages.minutes_ago_short') }}',
                    hours_ago_short: '{{ __('messages.hours_ago_short') }}',
                    days_ago_short: '{{ __('messages.days_ago_short') }}',
                    story: '{{ __('messages.story') }}',
                    send_message: '{{ __('messages.send_message') }}',
                    story_deleted_toast: '{{ __('messages.story_deleted_toast') }}',
                    post_deleted: '{{ __('messages.post_deleted') }}',
                    failed_to_delete_post: '{{ __('messages.failed_to_delete_post') }}',
                    account_already_verified: '{{ __('messages.account_already_verified') }}',
                    verification_code_sent: '{{ __('messages.verification_code_sent') }}',
                    enter_6_digit_code: '{{ __('messages.enter_6_digit_code') }}',
                    code_must_be_numbers: '{{ __('messages.code_must_be_numbers') }}',
                    passwords_mismatch: '{{ __('messages.passwords_mismatch') }}',
                    weak_password: '{{ __('messages.weak_password') }}',
                    failed_to_post_comment: '{{ __('messages.failed_to_post_comment') }}',
                    password_strength_weak: '{{ __('messages.password_strength_weak') }}',
                    password_strength_medium: '{{ __('messages.password_strength_medium') }}',
                    password_strength_strong: '{{ __('messages.password_strength_strong') }}',
                    password_strength_very_strong: '{{ __('messages.password_strength_very_strong') }}',
                    passwords_match: '{{ __('messages.passwords_match') }}',
                    passwords_do_not_match: '{{ __('messages.passwords_do_not_match') }}',
                    username_available: '{{ __('messages.username_available') }}',
                    username_taken: '{{ __('messages.username_taken') }}',
                    failed_to_add_member: '{{ __('messages.failed_to_add_member') }}',
                    error_adding_member: '{{ __('messages.error_adding_member') }}',
                    failed_to_remove_member: '{{ __('messages.failed_to_remove_member') }}',
                    error_removing_member: '{{ __('messages.error_removing_member') }}',
                    failed_to_send_media: '{{ __('messages.failed_to_send_media') }}',
                    error_sending_media: '{{ __('messages.error_sending_media') }}',
                    failed_to_join_group: '{{ __('messages.failed_to_join_group') }}',
                };

                // Post translations for posts.js
                window.postTranslations = {
                    delete_post_confirm: '{{ __('messages.delete_post_confirm') }}',
                    delete_comment_confirm: '{{ __('messages.delete_comment_confirm') }}',
                    post_deleted: '{{ __('messages.post_deleted') }}',
                    failed_to_delete_post: '{{ __('messages.failed_to_delete_post') }}',
                    new_posts_loaded: '{{ __('messages.new_posts_loaded') }}',
                    failed_to_load_posts: '{{ __('messages.failed_to_load_posts') }}',
                    load_more: '{{ __('messages.load_more') }}',
                };
            </script>
            @yield('content')
        </div>
    </main>

    @auth
            <nav class="mobile-nav">
                <div class="mobile-nav-inner">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> {{ __('navigation.home') }}</a>
                    <a href="{{ route('stories.index') }}" class="{{ request()->routeIs('stories.*') ? 'active' : '' }}"><i class="fas fa-circle-play"></i> {{ __('navigation.stories') }}</a>
                    <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}">
                        <i class="fas fa-message"></i> {{ __('navigation.messages') }}
                        <span class="mobile-msg-badge" id="mobileMsgBadge" style="display: none;">0</span>
                    </a>
                    <a href="{{ route('users.show', auth()->user()) }}" class="{{ request()->routeIs('users.show') ? 'active' : '' }}"><i class="fas fa-user"></i> {{ __('navigation.profile') }}</a>
                </div>
            </nav>
    @endauth

    <div id="toast-container"></div>

    @auth
        <script>
            window.currentUserId = {{ auth()->id() }};
            window.layoutTranslations = {
                failed_to_join_group: "{{ __('messages.failed_to_join_group') }}"
            };
        </script>
    @endauth

    @vite(['resources/js/app.js', 'resources/js/legacy/ui-utils.js', 'resources/js/legacy/comments.js'])
    @auth
        @vite(['resources/js/legacy/realtime.js'])
    @endauth
    <script>
        function toggleUserMenu(event) {
            event.stopPropagation();
            event.preventDefault();
            const menu = document.getElementById('userMenu');
            const btn = event.currentTarget;
            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                const isRTL = document.documentElement.dir === 'rtl';
                
                // Position dropdown based on language direction
                menu.style.top = (rect.bottom + 8) + 'px';
                if (isRTL) {
                    // Arabic: align to left
                    menu.style.left = rect.left + 'px';
                    menu.style.right = 'auto';
                } else {
                    // English: align to right
                    menu.style.right = (window.innerWidth - rect.right) + 'px';
                    menu.style.left = 'auto';
                }
                menu.classList.add('show');
                document.getElementById('dropdownOverlay').classList.add('active');
            }
        }

        function toggleNotifications(event) {
            event.stopPropagation();
            event.preventDefault();
            const menu = document.getElementById('notifMenu');
            const btn = document.getElementById('notifBtn');
            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                const menuWidth = 380;
                const padding = 16;

                let top = rect.bottom + 8;
                let right = window.innerWidth - rect.right;

                if (right + menuWidth > window.innerWidth - padding) {
                    right = padding;
                }

                menu.style.top = top + 'px';
                menu.style.right = right + 'px';
                menu.style.left = 'auto';
                menu.classList.add('show');
                document.getElementById('dropdownOverlay').classList.add('active');
                loadNotifications();
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
            document.getElementById('dropdownOverlay').classList.remove('active');
            
            // Close user language dropdown
            const userLangDropdown = document.getElementById('user-language-dropdown');
            const userLangArrow = document.getElementById('user-lang-arrow');
            const userLangToggle = document.querySelector('#userMenu .language-option');
            if (userLangDropdown && userLangDropdown.style.display === 'block') {
                userLangDropdown.style.display = 'none';
                if (userLangArrow) userLangArrow.style.transform = 'rotate(0deg)';
                if (userLangToggle) userLangToggle.setAttribute('aria-expanded', 'false');
            }
        }

        function logout() { if (confirm('{{ __('auth.sign_out_confirm') }}')) document.getElementById('logout-form').submit(); }

        function showToast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + message + '</span>';

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 250);
            }, duration);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function loadNotifications() {
            fetch('/api/notifications', {
                credentials: 'include',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                }
                return r.json();
            })
            .then(data => {
                const list = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>{{ __('notifications.no_notifications') }}</p></div>`;
                    return;
                }
                list.innerHTML = data.notifications.map(n => {
                    const iconClass = getNotificationIconClass(n.type);
                    const notifIcon = getNotificationIcon(n.type);
                    const timeAgo = getTimeAgo(n.created_at);
                    const truncatedMessage = n.message.length > 60 ? n.message.substring(0, 60) + '...' : n.message;
                    return `
                    <div class="notif-item ${n.read_at ? '' : 'unread'}" id="notif-${n.id}" data-id="${n.id}">
                        <div class="notif-icon ${iconClass}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                            <i class="fas ${notifIcon}"></i>
                        </div>
                        <div class="notif-content ${n.read_at ? '' : 'unread'}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                            <p>${escapeHtml(truncatedMessage)}</p>
                            <span class="notif-time">${timeAgo}</span>
                        </div>
                        <div class="notif-item-actions">
                            ${!n.read_at ? `<button class="notif-item-btn" onclick="markAsRead(${n.id}); return false;" title="${window.chatTranslations.mark_as_read}"><i class="fas fa-check"></i></button>` : ''}
                            <button class="notif-item-btn delete" onclick="dismissNotification(${n.id}); return false;" title="${window.chatTranslations.delete}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `}).join('');
            })
            .catch(err => {
                console.error('loadNotifications: Error:', err);
                // Silently fail - don't show error to user for notification loading issues
            });
        }

        function markAsRead(id) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItem = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (notifItem) {
                // Remove unread class (this hides the dot)
                notifItem.classList.remove('unread');
                notifItem.querySelector('.notif-content')?.classList.remove('unread');

                // Update actions to show only delete button
                const actionsDiv = notifItem.querySelector('.notif-item-actions');
                if (actionsDiv) {
                    actionsDiv.innerHTML = `<button class="notif-item-btn delete" onclick="dismissNotification(${id}); return false;" title="${window.chatTranslations.delete}"><i class="fas fa-trash"></i></button>`;
                }

                // Update badge immediately
                const badge = document.getElementById('notif-badge');
                if (badge && badge.style.display !== 'none') {
                    const count = parseInt(badge.textContent) || 0;
                    if (count > 1) {
                        badge.textContent = count - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function markAllRead() {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItems = document.querySelectorAll('.notif-item.unread');
            notifItems.forEach(item => {
                // Remove unread class (this hides the dot)
                item.classList.remove('unread');
                item.querySelector('.notif-content')?.classList.remove('unread');

                const id = item.getAttribute('data-id');
                const actionsDiv = item.querySelector('.notif-item-actions');
                if (actionsDiv && id) {
                    actionsDiv.innerHTML = `<button class="notif-item-btn delete" onclick="dismissNotification(${id}); return false;" title="${window.chatTranslations.delete}"><i class="fas fa-trash"></i></button>`;
                }
            });
            document.getElementById('notif-badge').style.display = 'none';

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function getNotificationIconClass(type) {
            const classes = {
                'follow': 'follow',
                'like': 'like',
                'comment': 'comment',
                'mention': 'mention',
                'message': 'message',
                'group_invite': 'group'
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
                'group_invite': 'fa-users',
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

        function handleNotifClick(id, link) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Update badge immediately
            const badge = document.getElementById('notif-badge');
            if (badge && badge.style.display !== 'none') {
                const count = parseInt(badge.textContent) || 0;
                if (count > 1) {
                    badge.textContent = count - 1;
                } else {
                    badge.style.display = 'none';
                }
            }

            // Mark as read (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});

            // Navigate if link exists
            if (link) {
                closeAllDropdowns();
                window.location.href = link;
            } else {
                closeAllDropdowns();
                loadNotifications();
            }
        }

        function dismissNotification(id) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItem = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (notifItem) {
                notifItem.remove();

                // Update badge immediately
                const badge = document.getElementById('notif-badge');
                if (badge && badge.style.display !== 'none') {
                    const count = parseInt(badge.textContent) || 0;
                    if (count > 1) {
                        badge.textContent = count - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function clearAllNotifications() {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Clear UI immediately
            const notifList = document.getElementById('notif-list');
            if (notifList) {
                notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>{{ __('notifications.no_notifications') }}</p></div>';
            }
            document.getElementById('notif-badge').style.display = 'none';

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ({{ auth()->check() ? 'true' : 'false' }}) {
                window.currentUserId = {{ auth()->id() }};
            }
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllDropdowns(); });

            // Auto-detect Arabic text and apply RTL direction
            applyRTLToArabicText();
        });

        // User Menu Language Dropdown Functions
        function toggleUserLanguageDropdown() {
            const dropdown = document.getElementById('user-language-dropdown');
            const arrow = document.getElementById('user-lang-arrow');
            const toggle = document.querySelector('#userMenu .language-option');

            if (!dropdown) return;

            const isRTL = document.documentElement.dir === 'rtl';
            const isVisible = dropdown.style.display === 'block';

            if (isVisible) {
                dropdown.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            } else {
                // Position dropdown based on language direction
                dropdown.style.display = 'block';
                if (isRTL) {
                    // Arabic: align to right
                    dropdown.style.right = '0';
                    dropdown.style.left = 'auto';
                } else {
                    // English: align to left
                    dropdown.style.left = '0';
                    dropdown.style.right = 'auto';
                }
                if (arrow) arrow.style.transform = 'rotate(180deg)';
                if (toggle) toggle.setAttribute('aria-expanded', 'true');
            }
        }

        function switchUserLanguage(locale) {
            // Show loading indicator
            const loading = document.getElementById('language-loading');
            if (loading) {
                loading.style.display = 'flex';
            }

            // Close dropdown
            toggleUserLanguageDropdown();

            // Navigate to language switch route with current URL as return
            const currentPath = window.location.pathname + window.location.search;
            window.location.href = '/lang/' + locale + '?return=' + encodeURIComponent(currentPath);
        }

        // Close user language dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userLangSwitcher = userMenu?.querySelector('.language-switcher');

            if (userLangSwitcher && !userLangSwitcher.contains(event.target)) {
                const dropdown = document.getElementById('user-language-dropdown');
                const arrow = document.getElementById('user-lang-arrow');
                const toggle = document.querySelector('#userMenu .language-option');

                if (dropdown && dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                }
            }
        });

        /**
         * Detect Arabic/Persian/Hebrew text and apply RTL direction
         */
        function applyRTLToArabicText() {
            // Arabic Unicode range: \u0600-\u06FF, Arabic Supplement: \u0750-\u077F
            // Persian/Arabic Extended: \u08A0-\u08FF
            // Hebrew: \u0590-\u05FF
            const arabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\u0590-\u05FF]/;
            
            // Apply to post content
            document.querySelectorAll('.post-content p, .comment-content p, .message-content .text').forEach(el => {
                const text = el.textContent || el.innerText || '';
                if (arabicPattern.test(text)) {
                    el.setAttribute('dir', 'rtl');
                    el.style.direction = 'rtl';
                    el.style.textAlign = 'right';
                }
            });
        }
    </script>

    {{-- Push Notification Settings Modal --}}
    @auth
        @include('partials.push-notification-settings')
    @endauth
</body>
</html>
