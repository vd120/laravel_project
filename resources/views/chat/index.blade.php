@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="chat-page">
    <div class="chat-layout">
        
        {{-- Sidebar / List View --}}
        <aside class="chat-list-panel">
            
            {{-- Header --}}
            <header class="chat-header">
                <h1>Messages</h1>
                <div class="header-actions">
                    <a href="{{ route('groups.create') }}" class="icon-btn" title="Create Group">
                        <i class="fas fa-users"></i>
                    </a>
                    <button class="icon-btn primary" onclick="showUserSearch()" title="New Message">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                </div>
            </header>

            {{-- Search --}}
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." id="conversationFilter" oninput="filterConversations(this.value)">
            </div>

            {{-- Following Stories Row --}}
            <div class="stories-container">
                @php
                    $following = \App\Models\User::whereIn('id', function($query) {
                        $query->select('followed_id')->from('follows')->where('follower_id', auth()->id());
                    })->with('profile')->take(10)->get();
                @endphp
                <div class="stories-scroll">
                    @forelse($following as $user)
                    <div class="story-item" onclick="startChatWithUser({{ $user->id }})">
                        <div class="story-avatar">
                            @if($user->profile && $user->profile->avatar)
                                <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="{{ $user->name }}">
                            @else
                                <div class="avatar-fallback"><i class="fas fa-user"></i></div>
                            @endif
                        </div>
                        <span class="story-name">{{ Str::limit($user->name, 12) }}</span>
                    </div>
                    @empty
                        <div class="empty-stories">Follow people to start chatting</div>
                    @endforelse
                </div>
            </div>

            {{-- Conversations List --}}
            <div class="conversation-list" id="conversationsList">
                @forelse($conversations as $conversation)
                <a href="{{ route('chat.show', $conversation) }}" class="conversation-card {{ $conversation->unread_count > 0 ? 'is-unread' : '' }}" data-name="{{ $conversation->is_group ? $conversation->display_name : ($conversation->other_user->name ?? 'User') }}">
                    
                    <div class="conv-avatar">
                        @if($conversation->is_group)
                            @if($conversation->group && $conversation->group->avatar)
                                <img src="{{ asset('storage/' . $conversation->group->avatar) }}" alt="Group Avatar">
                            @else
                                <div class="avatar-fallback group-icon"><i class="fas fa-users"></i></div>
                            @endif
                        @else
                            @if($conversation->other_user && $conversation->other_user->profile && $conversation->other_user->profile->avatar)
                                <img src="{{ asset('storage/' . $conversation->other_user->profile->avatar) }}" alt="Avatar">
                            @else
                                <div class="avatar-fallback"><i class="fas fa-user"></i></div>
                            @endif
                        @endif
                    </div>

                    <div class="conv-details">
                        <div class="conv-top">
                            <span class="conv-name">
                                @if($conversation->is_group)
                                    {{ $conversation->display_name }}
                                @else
                                    {{ $conversation->other_user->name ?? 'Unknown User' }}
                                @endif
                            </span>
                            <span class="conv-time">
                                @if($conversation->last_message_at)
                                    {{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans(null, true, true) }}
                                @endif
                            </span>
                        </div>
                        <div class="conv-bottom">
                            <p class="conv-preview">
                                @if($conversation->latestMessage)
                                    @if($conversation->latestMessage->sender_id === auth()->id())
                                        <span class="you-prefix">You:</span>
                                    @elseif($conversation->is_group && $conversation->latestMessage->sender)
                                        <span class="sender-prefix">{{ $conversation->latestMessage->sender->name }}:</span>
                                    @endif
                                    {{ Str::limit($conversation->latestMessage->content, 30) }}
                                @else
                                    <span class="new-chat-text">Say hello 👋</span>
                                @endif
                            </p>
                            @if($conversation->unread_count > 0)
                                <span class="unread-pill">{{ $conversation->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="empty-state-list">
                    <i class="fas fa-inbox"></i>
                    <p>No conversations yet</p>
                </div>
                @endforelse
            </div>
        </aside>

        {{-- Main Content / Placeholder (Hidden on Mobile) --}}
        <main class="chat-main-area">
            <div class="welcome-content">
                <div class="welcome-icon"><i class="fas fa-comments"></i></div>
                <h2>Your Messages</h2>
                <p>Send private messages to a friend or group.</p>
                <button class="welcome-action-btn" onclick="showUserSearch()">
                    <i class="fas fa-pen"></i> Start Chatting
                </button>
            </div>
        </main>
    </div>

    {{-- Search Modal --}}
    <div id="userSearchModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-top">
                <h3>New Message</h3>
                <button class="close-btn" onclick="hideUserSearch()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="input-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="userSearch" placeholder="Search people..." class="modal-input">
                </div>
                <div id="userResults" class="results-list"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- Base & Layout --- */
    .chat-page { 
        height: 100vh; 
        background: var(--bg); 
        padding-top: 60px; 
        box-sizing: border-box; 
        overflow: hidden; 
    }
    
    .chat-layout { 
        display: flex; 
        height: 100%; 
        width: 100%; 
        margin: 0 auto;
    }

    /* --- Sidebar (List Panel) --- */
    .chat-list-panel {
        width: 100%;
        background: var(--surface);
        display: flex;
        flex-direction: column;
        border-right: 1px solid transparent;
        flex-shrink: 0;
    }

    /* --- Header --- */
    .chat-header {
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    .chat-header h1 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; }
    .header-actions { display: flex; gap: 8px; }
    .icon-btn {
        width: 36px; height: 36px; border-radius: 50%;
        background: transparent; border: none;
        color: var(--text); display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: background 0.2s; font-size: 16px;
    }
    .icon-btn:hover { background: var(--bg); }
    .icon-btn.primary { background: var(--primary); color: white; }
    .icon-btn.primary:hover { background: var(--secondary); }

    /* --- Search --- */
    .search-container { padding: 12px 16px; }
    .search-container { position: relative; }
    .search-container input {
        width: 100%; background: var(--bg); border: none;
        padding: 12px 16px 12px 42px; border-radius: 12px;
        font-size: 14px; color: var(--text);
    }
    .search-container input:focus { outline: 2px solid var(--primary); outline-offset: -2px; }
    .search-container i { position: absolute; left: 28px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

    /* --- Stories/Following --- */
    .stories-container { padding: 8px 0; border-bottom: 1px solid var(--border); }
    .stories-scroll {
        display: flex; gap: 16px; overflow-x: auto; padding: 0 16px;
        scrollbar-width: none; -ms-overflow-style: none;
    }
    .stories-scroll::-webkit-scrollbar { display: none; }
    .story-item { display: flex; flex-direction: column; align-items: center; cursor: pointer; }
    .story-avatar {
        width: 52px; height: 52px; border-radius: 50%; 
        border: 2px solid var(--primary); padding: 2px; margin-bottom: 4px;
        background: var(--surface);
    }
    .story-avatar img, .avatar-fallback {
        width: 100%; height: 100%; border-radius: 50%; object-fit: cover;
        background: var(--bg); display: flex; align-items: center; justify-content: center;
        color: var(--text-muted); font-size: 18px;
    }
    .story-name { font-size: 11px; color: var(--text-muted); font-weight: 500; }
    
    /* --- Conversation List --- */
    .conversation-list { flex: 1; overflow-y: auto; padding-bottom: 20px; }
    
    .conversation-card {
        display: flex; padding: 14px 16px; align-items: center;
        text-decoration: none; transition: background 0.2s;
        border-bottom: 1px solid var(--border);
    }
    .conversation-card:hover { background: var(--bg); }
    .conversation-card.is-unread { background: rgba(var(--primary-rgb), 0.05); } /* Requires rgb var, fallback below */
    .conversation-card.is-unread .conv-name { font-weight: 700; color: var(--text); }
    
    .conv-avatar { margin-right: 12px; flex-shrink: 0; }
    .conv-avatar img, .conv-avatar .avatar-fallback {
        width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
    }
    .avatar-fallback.group-icon { background: var(--primary); color: white; }

    .conv-details { flex: 1; min-width: 0; }
    .conv-top { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .conv-name { font-size: 15px; font-weight: 600; color: var(--text); }
    .conv-time { font-size: 12px; color: var(--text-muted); }
    
    .conv-bottom { display: flex; justify-content: space-between; align-items: center; }
    .conv-preview { margin: 0; font-size: 13px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
    .you-prefix { color: var(--text); font-weight: 500; }
    .sender-prefix { color: var(--text-muted); font-weight: 500; }
    .new-chat-text { color: var(--primary); font-style: italic; }
    
    .unread-pill { background: var(--primary); color: white; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600; }

    /* --- Main Placeholder (Desktop) --- */
    .chat-main-area {
        display: none; 
        flex: 1; 
        background: var(--bg); 
        align-items: center; 
        justify-content: center; 
        border-left: 1px solid var(--border);
    }
    .welcome-content { text-align: center; padding: 40px; max-width: 350px; }
    .welcome-icon { font-size: 48px; color: var(--primary); margin-bottom: 16px; }
    .welcome-content h2 { margin: 0 0 8px; font-size: 20px; color: var(--text); }
    .welcome-content p { color: var(--text-muted); margin-bottom: 24px; font-size: 14px; }
    .welcome-action-btn {
        background: var(--primary); color: white; border: none;
        padding: 10px 24px; border-radius: 8px; font-weight: 600;
        cursor: pointer; transition: opacity 0.2s;
    }
    .welcome-action-btn:hover { opacity: 0.9; }

    /* --- Modal --- */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
    .modal-box { background: var(--surface); width: 100%; max-width: 400px; border-radius: 16px; overflow: hidden; }
    .modal-top { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
    .modal-top h3 { margin: 0; font-size: 18px; font-weight: 600; color: var(--text); }
    .close-btn { background: none; border: none; font-size: 16px; color: var(--text-muted); cursor: pointer; padding: 4px; }
    .modal-body { padding: 16px; }
    .input-box { position: relative; margin-bottom: 16px; }
    .input-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .modal-input { width: 100%; padding: 12px 12px 12px 36px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); font-size: 14px; color: var(--text); }
    .modal-input:focus { outline: none; border-color: var(--primary); }
    
    .results-list { max-height: 300px; overflow-y: auto; }
    .result-user { display: flex; align-items: center; padding: 10px; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
    .result-user:hover { background: var(--bg); }
    .result-user img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 12px; }
    .result-user .avatar-fallback { width: 40px; height: 40px; margin-right: 12px; }
    .result-name { font-size: 14px; font-weight: 500; color: var(--text); }

    /* --- Tablet & Desktop --- */
    @media (min-width: 768px) {
        .chat-list-panel { width: 340px; border-right-color: var(--border); }
        .chat-main-area { display: flex; }
    }

    @media (min-width: 1024px) {
        .chat-list-panel { width: 400px; }
    }
</style>

<script>
    // Simple escape function for HTML safety
    function e(t) { return t ? (t+'').replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>') : ''; }

    function showUserSearch() { 
        document.getElementById('userSearchModal').style.display = 'flex'; 
        setTimeout(() => document.getElementById('userSearch').focus(), 100); 
    }
    function hideUserSearch() { 
        document.getElementById('userSearchModal').style.display = 'none'; 
    }
    document.getElementById('userSearchModal').addEventListener('click', function(e) { 
        if (e.target === this) hideUserSearch(); 
    });
    
    function filterConversations(query) {
        const items = document.querySelectorAll('.conversation-card'); 
        const q = query.toLowerCase();
        items.forEach(item => { 
            const name = item.getAttribute('data-name').toLowerCase(); 
            item.style.display = name.includes(q) ? 'flex' : 'none'; 
        });
    }
    
    document.getElementById('userSearch').addEventListener('input', function() {
        const query = this.value.trim(); 
        const resultsDiv = document.getElementById('userResults');
        if (query.length < 2) { resultsDiv.innerHTML = ''; return; }
        
        fetch(`/api/search-users?q=${encodeURIComponent(query)}`, { 
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
                        ${u.avatar ? `<img src="/storage/${e(u.avatar)}">` : '<div class="avatar-fallback"><i class="fas fa-user"></i></div>'}
                        <div class="result-name">${e(u.name)}</div>
                    </div>
                `).join(''); 
            } 
        });
    });
    
    function startChat(userId) { window.location.href = `/chat/start/${userId}`; }
    function startChatWithUser(userId) { window.location.href = `/chat/start/${userId}`; }
</script>
@endsection