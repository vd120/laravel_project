<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.forgot_password_title') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        /* === UNIFIED DESIGN SYSTEM === */
        :root{
            --bg: #0d0d0d;
            --surface: rgba(22, 22, 22, 0.7);
            --surface-hover: #1c1c1e;
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --text-muted: #86868b;
            --primary: #5e60ce;
            --primary-hover: #7400b8;
            --secondary: #4ea8de;
            --success: #30d158;
            --warning: #ffd60a;
            --error: #ef4444;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-full: 9999px;
        }

        [data-theme="light"] {
            --bg: #ffffff;
            --surface: rgba(249, 250, 251, 0.7);
            --surface-hover: #f3f4f6;
            --border: rgba(0, 0, 0, 0.08);
            --text: #111111;
            --text-muted: #6b7280;
        }

        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter',sans-serif;
            background:var(--bg);
            color:var(--text);
            -webkit-font-smoothing:antialiased;
            min-height:100vh;
        }

        /* NAV — identical to login/register */
        nav{
            position:fixed;
            top:0;
            width:100%;
            padding:10px 40px;
            backdrop-filter:blur(30px);
            -webkit-backdrop-filter:blur(30px);
            background:rgba(13, 13, 13, 0.8);
            border-bottom:1px solid rgba(255, 255, 255, 0.1);
            display:flex;
            justify-content:center;
            z-index:300;
        }

        [data-theme="light"] nav{
            background:rgba(255, 255, 255, 0.8);
            border-bottom:1px solid rgba(0, 0, 0, 0.1);
        }

        .nav-container{
            max-width:980px;
            width:100%;
            display:flex;
            justify-content:space-between;
            align-items:center;
            height:48px;
        }
        .nav-brand{
            font-weight:600;
            font-size:18px;
            text-decoration:none;
            color:#ffffff;
        }

        [data-theme="light"] .nav-brand{
            color:#000000;
        }

        .nav-link{
            font-size:13px;
            color:#86868b;
            text-decoration:none;
            transition:0.3s;
        }
        .nav-link:hover{color:#ffffff}

        [data-theme="light"] .nav-link{
            color:#6b7280;
        }
        [data-theme="light"] .nav-link:hover{
            color:#000000;
        }

        /* Language Switcher - Unified Style (same as landing page) */
        .language-switcher {
            position: relative;
            display: inline-block;
        }

        .language-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .language-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        [data-theme="light"] .language-toggle {
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: #111111;
        }

        [data-theme="light"] .language-toggle:hover {
            background: rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 0, 0, 0.15);
        }

        .language-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            min-width: 180px;
            background: rgba(22, 22, 22, 0.98);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow: hidden;
            padding: 8px;
        }

        [data-theme="light"] .language-dropdown {
            background: rgba(249, 250, 251, 0.98);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .language-dropdown.show {
            display: block !important;
        }

        .language-header {
            padding: 8px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 4px;
        }

        [data-theme="light"] .language-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .language-header span {
            font-size: 12px;
            font-weight: 600;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .language-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            margin-bottom: 4px;
            cursor: pointer;
        }

        [data-theme="light"] .language-option {
            color: #111111;
        }

        .language-option:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .language-option:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .language-option.active {
            background: rgba(94, 96, 206, 0.1);
            color: #5e60ce;
            font-weight: 600;
        }

        .language-option.active:hover {
            background: rgba(94, 96, 206, 0.15);
        }

        /* Mobile - Hide language text, show only icon */
        @media(max-width: 480px) {
            .language-switcher .current-locale,
            .language-switcher .lang-divider,
            .language-switcher .lang-alt {
                display: none;
            }
            .language-switcher {
                padding: 6px 10px !important;
            }
            .language-switcher span:first-child {
                font-size: 16px !important;
            }
            .language-dropdown {
                min-width: 160px;
            }
            .language-option {
                padding: 8px 12px;
                gap: 10px;
            }
            .language-option span:first-child {
                font-size: 16px;
            }
            .language-option div span {
                font-size: 13px;
            }
        }

        /* Language Switcher - Follows Language Direction */
        .language-switcher,
        .language-switcher *,
        .language-toggle,
        .language-dropdown,
        .language-option {
            /* Direction follows language - RTL for Arabic, LTR for English */
        }

        /* Action buttons - match login page header */
        #themeToggle {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
        }
        [data-theme="light"] #themeToggle {
            border-color: rgba(0, 0, 0, 0.2);
            color: #111111;
        }

        .back-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        [data-theme="light"] .back-btn {
            border-color: rgba(0, 0, 0, 0.2);
            color: #111111;
        }
        [data-theme="light"] .back-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* PAGE WRAP */
        .page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:120px 20px 40px;
        }

        /* GLASS CARD — exactly like login/register */
        .login-card{
            width:100%;
            max-width:420px;
            background:var(--surface);
            border:1px solid var(--border);
            backdrop-filter:blur(40px);
            border-radius:28px;
            padding:50px 40px;
            position:relative;
            overflow:hidden;
        }
        .login-card::before{
            content:"";
            position:absolute;
            top:-40px;
            right:-40px;
            width:140px;
            height:140px;
            background:var(--primary);
            filter:blur(80px);
            opacity:0.25;
        }

        /* title & subtitle */
        .login-title{
            font-size:38px;
            font-weight:700;
            margin-bottom:10px;
            letter-spacing:-0.02em;
            background:linear-gradient(135deg,var(--text) 0%,var(--text-muted) 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }
        .login-sub{
            font-size:16px;
            color:var(--text-muted);
            margin-bottom:30px;
            line-height:1.5;
        }

        /* fields — same style as login/register */
        .field{margin-bottom:24px}
        .field label{
            display:block;
            font-size:14px;
            margin-bottom:8px;
            color:var(--text);
        }
        .field input{
            width:100%;
            padding:14px 18px;
            border-radius:14px;
            border:1px solid var(--border);
            background:rgba(255,255,255,0.03);
            color:var(--text);
            font-size:15px;
            outline:none;
            transition:0.3s;
        }
        .field input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(94,96,206,0.2);
        }
        .field input::placeholder { color: #5f5f66; }

        /* field errors */
        .field-error{
            font-size:13px;
            color:var(--error);
            margin-top:6px;
        }

        /* alert boxes */
        .alert-success {
            padding: 14px 18px;
            border:1px solid rgba(48, 209, 88, 0.3);
            background:rgba(48, 209, 88, 0.1);
            border-radius:14px;
            margin-bottom:25px;
            font-size:13px;
            color:var(--success);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error {
            padding: 14px 18px;
            border:1px solid rgba(239, 68, 68, 0.3);
            background:rgba(239, 68, 68, 0.1);
            border-radius:14px;
            margin-bottom:25px;
            font-size:13px;
            color:#ef4444;
        }

        /* buttons — identical to login/register */
        .btn{
            width:100%;
            padding:14px;
            border-radius:var(--radius-full);
            font-size:16px;
            font-weight:500;
            border:none;
            cursor:pointer;
            transition:0.3s;
        }
        .btn-primary{
            background:#fff;
            color:#000;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }
        .btn-primary:hover{
            background:#f5f5f5;
            transform:scale(1.02);
        }

        /* footer */
        .card-footer{
            margin-top:35px;
            text-align:center;
            font-size:14px;
            color:var(--text-muted);
        }
        .card-footer a{
            color:var(--text);
            text-decoration:none;
            font-weight:500;
        }
        .card-footer a:hover{text-decoration:underline}

        @media(max-width:480px){
            .login-card{padding:35px 25px}
            .login-title{font-size:30px}
        }
    </style>
</head>
<body>
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
</script>

<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <div style="display: flex; align-items: center; gap: 12px;">
            @include('partials.language-switcher')
            <button type="button" id="themeToggle" onclick="toggleTheme()" title="{{ __('auth.toggle_theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('login') }}" class="back-btn">← {{ __('auth.back') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">{{ __('auth.forgot_password_title') }}</h1>
        <p class="login-sub">{{ __('auth.forgot_password_subtitle') }}</p>

        @if (session('status'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input type="email" name="email" id="email"
                       value="{{ old('email') }}"
                       placeholder="{{ __('auth.email_placeholder') }}"
                       required autocomplete="email">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> {{ __('auth.send_reset_link') }}
            </button>
        </form>

        <div class="card-footer">
            {{ __('auth.remember_password') }} <a href="{{ route('login') }}">{{ __('auth.sign_in') }}</a>
        </div>
    </div>
</div>

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

    // Set initial theme icon based on saved theme
    (function() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        const currentTheme = html.getAttribute('data-theme');
        if (currentTheme === 'light') {
            icon.className = 'fas fa-moon';
        } else {
            icon.className = 'fas fa-sun';
        }
    })();

    // Language switcher functions
    function toggleLanguageDropdown() {
        const dropdown = document.getElementById('language-dropdown');
        const overlay = document.getElementById('language-overlay');
        const arrow = document.getElementById('lang-arrow');
        const toggle = document.querySelector('.language-toggle');

        const isVisible = dropdown && dropdown.style.display === 'block';

        if (isVisible) {
            dropdown.style.display = 'none';
            overlay.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            dropdown.style.display = 'block';
            overlay.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
            toggle.setAttribute('aria-expanded', 'true');
        }
    }

    function switchLanguage(locale) {
        const loading = document.getElementById('language-loading');
        if (loading) {
            loading.style.display = 'flex';
        }
        toggleLanguageDropdown();
        const currentPath = window.location.pathname + window.location.search;
        window.location.href = '/lang/' + locale + '?return=' + encodeURIComponent(currentPath);
    }

    document.addEventListener('click', function(event) {
        const switcher = document.querySelector('.language-switcher');
        if (switcher && !switcher.contains(event.target)) {
            const dropdown = document.getElementById('language-dropdown');
            const overlay = document.getElementById('language-overlay');
            const arrow = document.getElementById('lang-arrow');
            const toggle = document.querySelector('.language-toggle');

            if (dropdown) dropdown.style.display = 'none';
            if (overlay) overlay.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Theme-aware styling for language switcher
    (function() {
        const checkTheme = () => {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            const toggle = document.querySelector('.language-toggle');
            const dropdown = document.getElementById('language-dropdown');

            if (toggle) {
                toggle.style.borderColor = isLight ? 'rgba(0, 0, 0, 0.2)' : 'rgba(255, 255, 255, 0.2)';
                toggle.style.color = isLight ? '#111111' : '#ffffff';
            }

            if (dropdown) {
                dropdown.style.background = isLight ? 'rgba(255, 255, 255, 0.98)' : 'rgba(22, 22, 22, 0.98)';
                dropdown.style.borderColor = isLight ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';
                dropdown.style.boxShadow = isLight ? '0 10px 40px rgba(0, 0, 0, 0.15)' : '0 10px 40px rgba(0, 0, 0, 0.4)';
            }
        };

        checkTheme();

        const observer = new MutationObserver(checkTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
    })();
</script>

{{-- Unified Mobile Header Styles --}}
@include('partials.mobile-header-styles')
</body>
</html>
