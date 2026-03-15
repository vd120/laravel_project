@extends('layouts.app')

@section('title', __('ai.title') . ' - Nexus')

@section('content')
<link rel="stylesheet" href="{{ asset('css/ai-chat.css') }}">

<div class="ai-page">
    <header class="ai-header">
        <a href="{{ route('home') }}" class="back-btn" title="{{ __('home.back') }}">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="ai-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="ai-info">
            <h1>
                {{ __('ai.assistant_name') }}
                <i class="fas fa-check-circle" style="color: var(--wa-blue); font-size: 14px;"></i>
            </h1>
            <div class="ai-status">
                <span class="status-dot"></span>
                {{ __('ai.online') }}
            </div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" onclick="clearChat()" title="{{ __('ai.clear_chat') }}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </header>

    <div class="chat-container" id="chatContainer">
        <!-- Welcome Message -->
        <div class="message ai" id="welcomeMessage">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble">
                <p><strong>🤖 {{ __('ai.welcome_greeting') }}</strong></p>
                <p style="margin-top: 12px; color: var(--wa-text-muted);">{{ __('ai.welcome_instruction') }}</p>

                <div class="quick-actions">
                    <div class="quick-action-row">
                        <button class="quick-btn" onclick="sendQuickMessage('1')">
                            <span class="quick-num">1</span>
                            <span class="quick-label">{{ __('ai.help_menu') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('2')">
                            <span class="quick-num">2</span>
                            <span class="quick-label">{{ __('ai.writing_posts') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('3')">
                            <span class="quick-num">3</span>
                            <span class="quick-label">{{ __('ai.follow_suggestions') }}</span>
                        </button>
                    </div>
                    <div class="quick-action-row">
                        <button class="quick-btn" onclick="sendQuickMessage('4')">
                            <span class="quick-num">4</span>
                            <span class="quick-label">{{ __('ai.trending_topics') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('5')">
                            <span class="quick-num">5</span>
                            <span class="quick-label">{{ __('ai.privacy_guide') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('6')">
                            <span class="quick-num">6</span>
                            <span class="quick-label">{{ __('ai.engagement_tips') }}</span>
                        </button>
                    </div>
                    <div class="quick-action-row">
                        <button class="quick-btn" onclick="sendQuickMessage('7')">
                            <span class="quick-num">7</span>
                            <span class="quick-label">{{ __('ai.stories_guide') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('8')">
                            <span class="quick-num">8</span>
                            <span class="quick-label">{{ __('ai.profile_setup') }}</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('9')">
                            <span class="quick-num">9</span>
                            <span class="quick-label">{{ __('ai.search_discover') }}</span>
                        </button>
                    </div>
                </div>

                <div class="message-time">{{ now()->format('H:i') }}</div>
            </div>
        </div>
    </div>

    <div class="input-area">
        <div class="input-wrapper">
            <input type="text" id="chatInput" placeholder="{{ __('ai.input_placeholder') }}" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[1-9]">
            <button type="button" id="stopBtn" class="stop-btn" style="display:none;" title="{{ __('ai.stop') }}">
                <i class="fas fa-stop"></i>
            </button>
            <button type="button" id="sendBtn" class="send-btn" disabled title="{{ __('ai.send') }}">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/ai-chat.js') }}"></script>
@endsection
