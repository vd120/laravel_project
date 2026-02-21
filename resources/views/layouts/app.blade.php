<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#111111">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nexus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* ============================================
           UNIFIED DESIGN SYSTEM - Nexus
           ============================================ */
        
        :root {
            /* Core Colors */
            --bg: #111111;
            --surface: #1a1a1a;
            --surface-hover: #242424;
            --border: #2a2a2a;
            --text: #f5f5f5;
            --text-muted: #888888;
            
            /* Brand Colors */
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-glow: rgba(59, 130, 246, 0.25);
            --secondary: #8b5cf6;
            --accent: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            
            /* Spacing Scale (4px base) */
            --space-1: 4px;
            --space-2: 8px;
            --space-3: 12px;
            --space-4: 16px;
            --space-5: 20px;
            --space-6: 24px;
            --space-8: 32px;
            --space-10: 40px;
            --space-12: 48px;
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            
            /* Shadows */
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 20px var(--primary-glow);
            
            /* Transitions - Simplified for performance */
            --transition-fast: 50ms ease;
            --transition: 100ms ease;
            --transition-slow: 150ms ease;
            
            /* Z-Index Scale */
            --z-dropdown: 100;
            --z-sticky: 200;
            --z-fixed: 300;
            --z-modal-backdrop: 400;
            --z-modal: 500;
            --z-popover: 600;
            --z-tooltip: 700;
            --z-toast: 800;
            
            /* Legacy Compatibility (deprecated - use core variables above) */
            --twitter-blue: var(--primary);
            --twitter-dark: var(--text);
            --twitter-gray: var(--text-muted);
            --twitter-light: var(--surface);
            --card-bg: var(--surface);
            --border-color: var(--border);
            --input-bg: var(--surface);
            --hover-bg: var(--surface-hover);
            --focus-border: var(--primary);
            --error-color: var(--accent);
            --success-color: var(--success);
        }

        [data-theme="light"] {
            --bg: #ffffff;
            --surface: #f9fafb;
            --surface-hover: #f3f4f6;
            --border: #e5e7eb;
            --text: #111111;
            --text-muted: #6b7280;
            
            --primary: #3b82f6;
            --primary-hover: #1d4ed8;
            --primary-glow: rgba(59, 130, 246, 0.2);
            --secondary: #7c3aed;
            --accent: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --shadow-glow: 0 0 20px rgba(59, 130, 246, 0.15);
            
            /* Legacy Compatibility - Light */
            --twitter-blue: var(--primary);
            --twitter-dark: var(--text);
            --twitter-gray: var(--text-muted);
            --twitter-light: var(--surface);
            --card-bg: #ffffff;
            --border-color: var(--border);
            --input-bg: #ffffff;
            --hover-bg: var(--surface-hover);
            --focus-border: var(--primary);
            --error-color: var(--accent);
            --success-color: var(--success);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        body { font-size: 14px; line-height: 1.6; color: var(--text); background: var(--bg); min-height: 100vh; overflow-x: hidden; }

        .header {
            position: sticky; top: 0; z-index: var(--z-sticky);
            background: var(--surface);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .header-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px;
        }

        .logo {
            font-size: 1.5rem; font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px; color: var(--text-muted);
            text-decoration: none; font-size: 14px; font-weight: 500;
            border-radius: var(--radius); transition: all var(--transition);
        }
        .nav-links a:hover, .nav-links a.active { color: var(--text); background: var(--surface-hover); }

        .user-actions { display: flex; align-items: center; gap: 12px; }

        .icon-btn {
            position: relative; width: 40px; height: 40px; border: none; border-radius: var(--radius);
            background: var(--surface); border: 1px solid var(--border);
            color: var(--text-muted); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; transition: all var(--transition);
        }
        .icon-btn:hover { color: var(--text); background: var(--surface-hover); border-color: var(--primary); }

        .icon-btn .badge {
            position: absolute; top: -4px; right: -4px;
            min-width: 18px; height: 18px; padding: 0 5px;
            background: var(--accent); color: white; font-size: 10px; font-weight: 700;
            border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--bg);
        }

        .user-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; border: 1px solid var(--border);
            border-radius: var(--radius); background: var(--surface);
            color: var(--text); font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all var(--transition);
        }
        .user-btn:hover { border-color: var(--primary); background: var(--surface-hover); }

        .user-avatar {
            width: 24px; height: 24px; border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; color: white;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dropdown-overlay {
            display: none; position: fixed; inset: 0; z-index: 9998;
            background: rgba(0, 0, 0, 0.5);
        }
        .dropdown-overlay.active { display: block; }

        .dropdown-wrapper {
            position: relative;
        }

        .dropdown-menu {
            display: none; position: fixed; 
            min-width: 220px; background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); z-index: 9999;
            padding: 8px;
        }
        .dropdown-menu.show { display: block; animation: dropdownIn 0.2s ease; }
        @keyframes dropdownIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* Notification Panel Styles */
        .notif-panel {
            width: 380px;
            max-width: calc(100vw - 32px);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
        }
        .notif-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
        }
        .notif-header-actions {
            display: flex;
            gap: 4px;
        }
        .notif-action-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 12px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .notif-action-btn:hover {
            background: var(--surface-hover);
            color: var(--text);
        }
        .notif-action-btn.danger:hover {
            color: var(--accent);
            background: rgba(244, 63, 94, 0.1);
        }
        .notif-list {
            max-height: 420px;
            overflow-y: auto;
        }
        .notif-list::-webkit-scrollbar {
            width: 6px;
        }
        .notif-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .notif-list::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
        .notif-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: all var(--transition);
            position: relative;
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-item:hover {
            background: var(--surface-hover);
        }
        .notif-item.unread {
            background: rgba(59, 130, 246, 0.08);
        }
        .notif-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--primary);
            border-radius: 0 3px 3px 0;
        }
        .notif-item-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }
        .notif-icon.follow { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
        .notif-icon.like { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .notif-icon.comment { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .notif-icon.mention { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
        .notif-icon.message { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
        .notif-icon.default { background: rgba(156, 163, 175, 0.15); color: #9ca3af; }
        .notif-text {
            flex: 1;
            min-width: 0;
        }
        .notif-text p {
            margin: 0 0 3px 0;
            font-size: 13px;
            color: var(--text);
            line-height: 1.5;
        }
        .notif-time {
            font-size: 11px;
            color: var(--text-muted);
        }
        .notif-item-actions {
            display: flex;
            gap: 2px;
            opacity: 0;
            transition: opacity var(--transition);
        }
        .notif-item:hover .notif-item-actions {
            opacity: 1;
        }
        .notif-item-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all var(--transition);
            font-size: 11px;
        }
        .notif-item-btn:hover {
            background: var(--surface-hover);
        }
        .notif-item-btn.read:hover {
            color: var(--success);
        }
        .notif-item-btn.delete:hover {
            color: var(--accent);
        }
        .notif-empty {
            padding: 50px 20px;
            text-align: center;
            color: var(--text-muted);
        }
        .notif-empty i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
            opacity: 0.25;
        }
        .notif-empty p {
            margin: 0;
            font-size: 13px;
        }

        .dropdown-menu a, .dropdown-menu button {
            display: flex; align-items: center; gap: 12px; width: 100%;
            padding: 12px 14px; border: none; background: none;
            color: var(--text); font-size: 14px; font-weight: 500;
            text-decoration: none; border-radius: 8px; cursor: pointer;
            transition: all var(--transition);
        }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background: var(--surface-hover); }
        .dropdown-menu a i, .dropdown-menu button i { width: 18px; text-align: center; color: var(--text-muted); font-size: 16px; }
        .dropdown-menu .divider { height: 1px; margin: 8px 0; background: var(--border); }
        .dropdown-menu .danger { color: var(--accent); }
        .dropdown-menu .danger:hover { background: rgba(244, 63, 94, 0.1); }

        .app-layout { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }
        .main-content { max-width: 800px; margin: 0 auto; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; font-size: 14px; font-weight: 600;
            border-radius: var(--radius); border: 1px solid var(--border);
            background: var(--surface); color: var(--text);
            cursor: pointer; text-decoration: none; transition: all var(--transition);
        }
        .btn:hover { background: var(--surface-hover); border-color: var(--primary); }

        .btn-primary {
            background: var(--primary);
            color: #ffffff; border: none;
            font-weight: 700;
        }
        .btn-primary:hover { 
            background: var(--primary-hover);
        }
        
        body.light-theme .btn-primary {
            color: #ffffff;
        }
        body.light-theme .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-ghost { border-color: transparent; background: transparent; }
        .btn-ghost:hover { background: var(--surface); border-color: var(--border); }

        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 20px; backdrop-filter: blur(10px);
        }

        .form-input {
            width: 100%; padding: 12px 16px; font-size: 14px;
            border: 1px solid var(--border); border-radius: var(--radius);
            background: var(--surface); color: var(--text); transition: all var(--transition);
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-input::placeholder { color: var(--text-muted); }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 14px; }

        #toast-container {
            position: fixed; top: 80px; right: 20px; left: 20px;
            max-width: 400px; margin: 0 auto; z-index: 10000;
            display: flex; flex-direction: column; gap: 12px; pointer-events: none;
        }
        .toast {
            padding: 16px 24px;
            background: rgba(17, 17, 17, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 14px; font-weight: 500;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            animation: toastIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            display: flex; align-items: center; gap: 14px; pointer-events: auto;
            position: relative;
            overflow: hidden;
        }
        .toast::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        }
        .toast i:first-child {
            font-size: 18px;
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .toast.success { 
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3), 0 0 0 1px rgba(34, 197, 94, 0.1) inset;
        }
        .toast.success i:first-child {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        .toast.error { 
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3), 0 0 0 1px rgba(239, 68, 68, 0.1) inset;
        }
        .toast.error i:first-child {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        .toast.info {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2), 0 0 0 1px rgba(59, 130, 246, 0.1) inset;
        }
        .toast.info i:first-child {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        body.light-theme .toast {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: #1e293b;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }
        body.light-theme .toast::before {
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent);
        }
        body.light-theme .toast.success {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.2);
        }
        body.light-theme .toast.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }
        body.light-theme .toast.info {
            background: rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.2);
        }
        @keyframes toastIn { 
            from { opacity: 0; transform: translateY(-20px) scale(0.95); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }
        @keyframes toastOut { 
            from { opacity: 1; transform: translateY(0) scale(1); } 
            to { opacity: 0; transform: translateY(-10px) scale(0.95); } 
        }

        .mobile-nav {
            display: none; position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--surface); backdrop-filter: blur(20px);
            border-top: 1px solid var(--border);
            padding: 8px 0 calc(12px + env(safe-area-inset-bottom)); z-index: var(--z-fixed);
        }
        .mobile-nav-inner { display: flex; justify-content: space-around; align-items: center; }
        .mobile-nav a {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 8px 12px; color: var(--text-muted);
            text-decoration: none; font-size: 11px; font-weight: 500;
            border-radius: var(--radius); transition: all var(--transition);
            min-width: 60px;
        }
        .mobile-nav a i { font-size: 22px; margin-bottom: 4px; }
        .mobile-nav a.active, .mobile-nav a:hover { color: var(--primary); }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .mobile-nav { display: block; }
            .app-layout { padding: 20px 16px 100px; }
        }
        @media (max-width: 480px) {
            .header-inner { padding: 0 12px; height: 56px; }
            .logo { font-size: 1.1rem; }
            .user-btn span { display: none; }
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
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
                <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore') ? 'active' : '' }}"><i class="fas fa-compass"></i> Explore</a>
                <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="fas fa-message"></i> Messages</a>
                <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}"><i class="fas fa-sparkles"></i> AI</a>
            </nav>
            @endauth

            <div class="user-actions">
                <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
                    <i class="fas fa-sun" id="theme-icon"></i>
                </button>

                @auth
                <div style="position: relative;">
                    <button class="icon-btn" id="notifBtn" onclick="toggleNotifications(event)">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notif-badge" style="display: none;">0</span>
                    </button>
                </div>

                <div style="position: relative;">
                    <button class="user-btn" id="userBtn" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            @if(auth()->user()->profile && auth()->user()->profile->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--text-muted);"></i>
                    </button>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                @else
                <a href="{{ route('login') }}" class="btn">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="dropdown-overlay" id="dropdownOverlay" onclick="closeAllDropdowns()"></div>

    @auth
    <!-- Notification Dropdown - Simple Modern Design -->
    <div class="dropdown-menu notif-panel" id="notifMenu">
        <div class="notif-header">
            <h3>Notifications</h3>
            <div class="notif-header-actions">
                <button class="notif-action-btn" onclick="markAllRead()" title="Mark all read">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notif-action-btn danger" onclick="clearAllNotifications()" title="Clear all">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="notif-list" id="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
            </div>
        </div>
    </div>

    <!-- User Menu Dropdown - outside header for proper z-index -->
    <div class="dropdown-menu" id="userMenu">
        <a href="{{ route('users.show', auth()->user()) }}"><i class="fas fa-user"></i> Profile</a>
        <a href="{{ route('users.saved-posts') }}"><i class="fas fa-bookmark"></i> Saved</a>
        <a href="{{ route('stories.index') }}"><i class="fas fa-circle-play"></i> Stories</a>
        <a href="{{ route('ai.index') }}"><i class="fas fa-sparkles"></i> AI Assistant</a>
        @if(auth()->user()->is_admin)
        <a href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt"></i> Admin</a>
        @endif
        <div class="divider"></div>
        <a href="{{ route('password.change') }}"><i class="fas fa-key"></i> Password</a>
        <button onclick="logout()" class="danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
    @endauth

    <main class="app-layout">
        <div class="main-content">
            @yield('content')
        </div>
    </main>

    @auth
            <nav class="mobile-nav">
                <div class="mobile-nav-inner">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
                    <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore') ? 'active' : '' }}"><i class="fas fa-compass"></i> Explore</a>
                    <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="fas fa-message"></i> Chat</a>
                    <a href="{{ route('users.show', auth()->user()) }}" class="{{ request()->routeIs('users.show') ? 'active' : '' }}"><i class="fas fa-user"></i> Profile</a>
                </div>
            </nav>
    @endauth

    <div id="toast-container"></div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            localStorage.setItem('theme', newTheme);
        }

        function toggleUserMenu(event) {
            event.stopPropagation();
            event.preventDefault();
            const menu = document.getElementById('userMenu');
            const btn = event.currentTarget;
            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.top = (rect.bottom + 8) + 'px';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
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
                const menuWidth = 380; // Width of notification panel
                const padding = 16;
                
                // Calculate position - align right edge with button
                let top = rect.bottom + 8;
                let right = window.innerWidth - rect.right;
                
                // Make sure it doesn't go off-screen on mobile
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
        }

        function logout() { if (confirm('Sign out?')) document.getElementById('logout-form').submit(); }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { 
                toast.style.animation = 'toastOut 0.3s ease forwards'; 
                setTimeout(() => toast.remove(), 300); 
            }, 3000);
        }

        function loadNotifications() {
            fetch('/api/notifications', { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }})
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                if (data.unread_count > 0) { badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count; badge.style.display = 'flex'; }
                else { badge.style.display = 'none'; }
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>`;
                    return;
                }
                list.innerHTML = data.notifications.map(n => {
                    const iconClass = getNotificationIconClass(n.type);
                    const notifIcon = getNotificationIcon(n.type);
                    const timeAgo = getTimeAgo(n.created_at);
                    return `
                    <div class="notif-item ${n.read_at ? '' : 'unread'}" id="notif-${n.id}">
                        <div class="notif-item-content" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                            <div class="notif-icon ${iconClass}">
                                <i class="fas ${notifIcon}"></i>
                            </div>
                            <div class="notif-text">
                                <p>${n.message}</p>
                                <span class="notif-time">${timeAgo}</span>
                            </div>
                        </div>
                        <div class="notif-item-actions">
                            ${!n.read_at ? `<button class="notif-item-btn read" onclick="event.stopPropagation(); markAsRead(${n.id})" title="Mark read"><i class="fas fa-check"></i></button>` : ''}
                            <button class="notif-item-btn delete" onclick="event.stopPropagation(); dismissNotification(${n.id})" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `}).join('');
            }).catch(() => {});
        }

        function markAsRead(id) {
            fetch('/api/notifications/' + id + '/read', { 
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            })
            .then(() => loadNotifications());
        }

        function markAllRead() {
            fetch('/api/notifications/mark-all-read', { 
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            })
            .then(() => {
                document.getElementById('notif-badge').style.display = 'none';
                loadNotifications();
            });
        }

        function getNotificationIconClass(type) {
            const classes = {
                'follow': 'follow',
                'like': 'like',
                'comment': 'comment',
                'mention': 'mention',
                'message': 'message'
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
            // Mark as read
            fetch('/api/notifications/' + id + '/read', { 
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            // Navigate if link exists
            if (link) {
                window.location.href = link;
            }
            closeAllDropdowns();
            loadNotifications();
        }

        function dismissNotification(id) {
            fetch('/api/notifications/' + id, { 
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            })
            .then(() => {
                const notif = document.getElementById('notif-' + id);
                if (notif) {
                    notif.style.opacity = '0';
                    notif.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        notif.remove();
                        loadNotifications();
                    }, 200);
                }
            });
        }

        function clearAllNotifications() {
            const list = document.getElementById('notif-list');
            const hasNotifications = list.querySelector('.notif-item');
            
            if (!hasNotifications) {
                showToast('No notifications to clear', 'info');
                return;
            }
            
            fetch('/api/notifications', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }})
            .then(() => { 
                document.getElementById('notif-badge').style.display = 'none'; 
                list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ({{ auth()->check() ? 'true' : 'false' }}) { loadNotifications(); setInterval(loadNotifications, 30000); }
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllDropdowns(); });
        });
    </script>
</body>
</html>
