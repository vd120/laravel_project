<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('auth.sign_in') }} — Nexus</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>
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
    --accent: #5e60ce;
    --success: #30d158;
    --warning: #ffd60a;
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

/* LOGIN CARD — Apple Glass */
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

/* Glow Accent */
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

/* Title */
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

/* Fields */
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

.field-error{
    font-size:13px;
    color:#ef4444;
    margin-top:6px;
}

/* Password toggle */
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
}

/* Extras */
.extras{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    margin-bottom:30px;
    color:var(--text-muted);
}
.extras input{accent-color:var(--primary)}
.extras a{
    color:var(--text-muted);
    text-decoration:none;
}
.extras a:hover{color:var(--text)}

/* Buttons */
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

/* Google */
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

/* Footer */
.card-footer{
    margin-top:35px;
    text-align:center;
    font-size:14px;
    color:var(--text-muted);
}
.card-footer a{
    color:var(--text);
    text-decoration:none;
}
.card-footer a:hover{text-decoration:underline}

/* Responsive */
@media(max-width:480px){
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

<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <div style="display: flex; align-items: center; gap: 12px;">
            @include('layouts.language')
            <button type="button" id="themeToggle" onclick="toggleTheme()" title="{{ __('messages.theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('home') }}" class="back-btn">← {{ __('messages.back') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        @if(session('suspended'))
            <div class="field-error" style="margin-bottom: 20px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('auth.account_suspended') }}
            </div>
        @endif

        @if(session('concurrent_login'))
            <div class="field-error" style="margin-bottom: 20px; text-align: center;">
                <i class="fas fa-shield-alt"></i>
                {{ __('auth.concurrent_login') }}
            </div>
        @endif

        @if(session('account_deleted'))
            <div class="field-error" style="margin-bottom: 20px; text-align: center;">
                <i class="fas fa-user-slash"></i>
                {{ __('auth.account_deleted') }}
            </div>
        @endif

        <h1 class="login-title">{{ __('auth.welcome_back') }}</h1>
        <p class="login-sub">{{ __('auth.sign_in_to_continue') }}</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label>{{ __('auth.email_address') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label>{{ __('auth.password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password" required autocomplete="current-password">
                    <button type="button" class="toggle-pw" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="extras">
                <label>
                    <input type="checkbox" name="remember" value="1"> {{ __('auth.remember_me') }}
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}">{{ __('auth.forgot_password') }}</a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">
                {{ __('auth.sign_in_button') }}
            </button>
        </form>

        <div class="divider">{{ __('auth.or_continue_with') }}</div>

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
            {{ __('auth.dont_have_account') }}
            <a href="{{ route('register') }}">{{ __('auth.sign_up') }}</a>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    const input=document.getElementById('password');
    const icon=document.getElementById('eye-icon');
    input.type=input.type==='password'?'text':'password';
    icon.className=input.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}

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