<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.create_account') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        /* Base Styles */
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

        /* Language Switcher - Always LTR */
        .language-switcher,
        .language-switcher *,
        .language-toggle,
        .language-dropdown,
        .language-option {
            direction: ltr !important;
            text-align: left !important;
        }

        /* Action buttons - match landing page header */
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

        /* GLASS CARD — exactly like login */
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
            margin-bottom:40px;
        }

        /* fields — same style as login */
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

        /* password wrap (eye) */
        .password-wrap{position:relative}
        .password-wrap input{padding-right:45px}
        .toggle-pw{
            position:absolute;
            right:15px;
            top:50%;
            transform:translateY(-50%);
            background:none;
            border:none;
            color:var(--text-muted);
            cursor:pointer;
            font-size:16px;
        }
        .toggle-pw:hover{color:var(--text)}

        /* field errors & status */
        .field-error{
            font-size:13px;
            color:var(--error);
            margin-top:6px;
        }
        .field-status{
            font-size:12px;
            margin-top:6px;
        }
        .field-status.checking { color: var(--warning); }
        .field-status.available { color: var(--success); }
        .field-status.taken, .field-status.invalid { color: var(--error); }
        .field-status.warning { color: var(--warning); }
        .field-status.matching { color: var(--success); }
        .field-status.not-matching { color: var(--error); }

        /* strength meter – matches glass aesthetic */
        .strength-track {
            height: 4px;
            background: rgba(255,255,255,0.1);
            margin-top: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%; width: 0;
            transition: width 0.2s;
        }
        .strength-fill.weak { width: 25%; background: var(--error); }
        .strength-fill.medium { width: 50%; background: var(--warning); }
        .strength-fill.strong { width: 75%; background: var(--success); }
        .strength-fill.very-strong { width: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); }

        .strength-label {
            font-size: 11px;
            margin-top: 4px;
            color: var(--text-muted);
        }
        .strength-label.weak { color: var(--error); }
        .strength-label.medium { color: var(--warning); }
        .strength-label.strong { color: var(--success); }
        .strength-label.very-strong { color: var(--primary); }

        /* terms row */
        .terms-row {
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 30px;
            font-size: 13px; color: var(--text-muted);
        }
        .terms-row input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px; height: 16px;
            margin-top: 2px;
            flex-shrink: 0;
            cursor: pointer;
        }
        .terms-row label { cursor: pointer; line-height: 1.5; }
        .terms-row a { color: var(--primary); text-decoration: none; }
        .terms-row a:hover { text-decoration: underline; }

        /* alert box (for errors) */
        .alert-error {
            padding: 14px 18px;
            border:1px solid rgba(239, 68, 68, 0.3);
            background:rgba(239, 68, 68, 0.1);
            border-radius:14px;
            margin-bottom:25px;
            font-size:13px;
            color:#ef4444;
        }

        /* buttons — identical to login */
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

        .divider{
            text-align:center;
            font-size:13px;
            color:var(--text-muted);
            margin:25px 0;
            position:relative;
        }
        .divider::before,
        .divider::after{
            content:"";
            position:absolute;
            top:50%;
            width:35%;
            height:1px;
            background:var(--border);
        }
        .divider::before{left:0}
        .divider::after{right:0}

        /* Google button */
        .btn-google{
            display:flex;
            justify-content:center;
            align-items:center;
            gap:10px;
            padding:14px;
            border-radius:var(--radius-full);
            border:1px solid var(--border);
            background:transparent;
            color:var(--text);
            text-decoration:none;
            transition:0.3s;
        }
        .btn-google:hover{
            background:rgba(255,255,255,0.05);
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
            /* Mobile Header - Always LTR */
            nav,
            .nav-container,
            .nav-container * {
                direction: ltr !important;
                text-align: left !important;
            }
            .login-card{padding:35px 25px}
            .login-title{font-size:30px}
            /* Mobile Header */
            .nav-container {
                padding: 8px 16px;
                gap: 8px;
            }
            .nav-brand {
                font-size: 16px;
            }
            /* Hide language text on mobile, show only icon */
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
            #themeToggle {
                width: 36px;
                height: 36px;
            }
            .back-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            .back-btn span {
                display: none;
            }
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

<!-- NAV (identical to login, with theme toggle) -->
<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <div style="display: flex; align-items: center; gap: 12px;">
            @include('partials.language-switcher')
            <button type="button" id="themeToggle" onclick="toggleTheme()" title="{{ __('messages.theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('home') }}" class="back-btn">← {{ __('messages.back') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">{{ __('auth.create_account') }}</h1>
        <p class="login-sub">{{ __('auth.join_us') }}</p>

        <!-- validation errors (same as register page) -->
        @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <!-- REGISTER FORM – fields exactly preserved, only design changed -->
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- full name field -->
            <div class="field">
                <label for="name">{{ __('auth.full_name') }}</label>
                <input type="text" name="name" id="name"
                    value="{{ old('name') }}"
                    placeholder="{{ __('auth.enter_full_name') }}"
                    required autocomplete="name">
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- username field (with status) -->
            <div class="field">
                <label for="username">{{ __('auth.username') }}</label>
                <input type="text" name="username" id="username"
                    value="{{ old('username') }}"
                    placeholder="{{ __('auth.choose_username') }}"
                    required minlength="3" maxlength="50"
                    pattern="[a-zA-Z0-9_\-]+"
                    title="{{ __('auth.username_requirements') }}"
                    autocomplete="username">
                <div class="field-status" id="username-status"></div>
                @error('username')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- email field -->
            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input type="email" name="email" id="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('auth.email_placeholder') }}"
                    required autocomplete="email">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- password field + strength meter -->
            <div class="field">
                <label for="password">{{ __('auth.password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password"
                        placeholder="{{ __('auth.create_password') }}"
                        required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','eye-icon')">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label"></div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- confirm password + match status -->
            <div class="field">
                <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        placeholder="{{ __('auth.repeat_password') }}"
                        required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','conf-eye-icon')">
                        <i class="fas fa-eye" id="conf-eye-icon"></i>
                    </button>
                </div>
                <div class="field-status" id="match-status"></div>
            </div>

            <!-- terms checkbox -->
            <div class="terms-row">
                <input type="checkbox" name="terms" id="terms" value="1" required>
                <label for="terms">{{ __('auth.i_agree') }} <a href="#">{{ __('auth.terms_of_service') }}</a> {{ __('auth.and') }} <a href="#">{{ __('auth.privacy_policy') }}</a></label>
            </div>

            <!-- submit button -->
            <button type="submit" class="btn btn-primary">
                {{ __('auth.create_account_button') }} <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="divider">{{ __('auth.or') }}</div>

        <!-- google button -->
        <a href="{{ route('login.google') }}" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            {{ __('auth.continue_with_google') }}
        </a>

        <div class="card-footer">
            {{ __('auth.already_have_account') }} <a href="{{ route('login') }}">{{ __('auth.sign_in') }}</a>
        </div>
    </div>
</div>

<!-- combined JavaScript: all register features + password toggles (no theme toggle) -->
<script>
    // password toggle (works for both fields)
    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        // trigger match check in case confirmation visibility changed
        if (inputId === 'password' || inputId === 'password_confirmation') checkMatch();
    }

    // password strength
    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        const fill = document.getElementById('strength-fill');
        const lbl = document.getElementById('strength-label');

        if (!val.length) {
            fill.className = 'strength-fill';
            lbl.className = 'strength-label';
            lbl.textContent = '';
            checkMatch();
            return;
        }

        let score = 0;
        if (val.length >= 8) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const level = score <= 2 ? 'weak' : score === 3 ? 'medium' : score === 4 ? 'strong' : 'very-strong';
        const labelMap = {
            'weak': '{{ __('auth.weak') }}',
            'medium': '{{ __('auth.medium') }}',
            'strong': '{{ __('auth.strong') }}',
            'very-strong': '{{ __('auth.very_strong') }}'
        };

        fill.className = 'strength-fill ' + level;
        lbl.className = 'strength-label ' + level;
        lbl.textContent = labelMap[level];
        checkMatch();
    });

    // password match
    function checkMatch() {
        const pass = document.getElementById('password').value;
        const conf = document.getElementById('password_confirmation').value;
        const div = document.getElementById('match-status');

        if (!conf.length) { div.textContent = ''; div.className = 'field-status'; return; }

        if (pass === conf) {
            div.textContent = '{{ __('auth.passwords_match') }}';
            div.className = 'field-status matching';
        } else {
            div.textContent = '{{ __('auth.passwords_not_match') }}';
            div.className = 'field-status not-matching';
        }
    }
    document.getElementById('password_confirmation').addEventListener('input', checkMatch);

    // username availability check (simulated with fetch, keep original behavior)
    let usernameTimer = null;
    document.getElementById('username').addEventListener('input', function() {
        const username = this.value.trim();
        const status = document.getElementById('username-status');
        clearTimeout(usernameTimer);

        if (!username.length) { status.textContent = ''; status.className = 'field-status'; return; }

        if (username.length < 3) {
            status.textContent = '{{ __('auth.min_3_characters') }}';
            status.className = 'field-status warning';
            return;
        }

        status.textContent = '{{ __('auth.checking') }}';
        status.className = 'field-status checking';

        usernameTimer = setTimeout(function() {
            // Using same endpoint as original – adjust if needed
            fetch('/api/check-username/' + encodeURIComponent(username))
                .then(r => r.json())
                .then(data => {
                    if (data.available) {
                        status.textContent = '{{ __('auth.username_available') }}';
                        status.className = 'field-status available';
                    } else {
                        status.textContent = '{{ __('auth.username_taken') }}';
                        status.className = 'field-status taken';
                    }
                })
                .catch(() => { status.textContent = ''; status.className = 'field-status'; });
        }, 500);
    });

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
</script>

</body>
</html>