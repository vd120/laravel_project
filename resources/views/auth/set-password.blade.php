<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('auth.set_password_title') }} — Nexus</title>

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

/* Language Switcher - Follows Language Direction */
.language-switcher,
.language-switcher *,
.language-toggle,
.language-dropdown,
.language-option {
    /* Direction follows language - RTL for Arabic, LTR for English */
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

/* FORM */
.field{
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
    color:var(--text);
}

input[type="password"],
input[type="email"],
input[type="text"]{
    width:100%;
    padding:14px 16px;
    font-size:15px;
    background:rgba(255,255,255,0.03);
    border:1px solid var(--border);
    border-radius:var(--radius);
    color:var(--text);
    transition:0.3s;
}

input:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 4px rgba(94, 96, 206, 0.1);
}

.field-error{
    color:var(--error);
    font-size:13px;
    margin-top:6px;
    display:flex;
    align-items:center;
    gap:6px;
}

/* password wrap (for eye icon) */
.password-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.password-wrap input {
    width: 100%;
    padding-right: 45px;
}
.password-wrap .toggle-pw {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 5px;
    font-size: 16px;
    transition: color 0.2s;
}
.password-wrap .toggle-pw:hover {
    color: var(--text);
}

/* field status (for match indicator) */
.field-status {
    font-size: 12px;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
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

/* BUTTONS */
.btn{
    width:100%;
    padding:14px;
    font-size:15px;
    font-weight:600;
    border:none;
    border-radius:var(--radius);
    cursor:pointer;
    transition:0.3s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    margin-top:10px;
}

.btn-primary{
    background:var(--primary);
    color:#ffffff;
}

.btn-primary:hover{
    background:var(--primary-hover);
    transform:translateY(-2px);
    box-shadow:0 10px 30px rgba(94, 96, 206, 0.3);
}

/* ALERTS */
.alert{
    padding:14px 18px;
    border-radius:var(--radius);
    margin-bottom:25px;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:10px;
}

.alert-info{
    background:rgba(94, 96, 206, 0.1);
    border:1px solid rgba(94, 96, 206, 0.2);
    color:var(--primary);
}

.alert-success{
    background:rgba(48, 209, 88, 0.1);
    border:1px solid rgba(48, 209, 88, 0.2);
    color:var(--success);
}

/* Responsive */
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
            <button type="button" id="themeToggle" onclick="toggleTheme()" title="{{ __('messages.theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('login') }}" class="back-btn">← {{ __('auth.back_to_login') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">{{ __('auth.set_password_title') }}</h1>
        <p class="login-sub">{{ __('auth.set_password_desc') }}</p>

        @if(session('message'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span>{{ session('message') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('password.set-password.store') }}">
            @csrf

            <div class="field">
                <label for="password">{{ __('auth.password') }}</label>
                <div class="password-wrap">
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="{{ __('auth.create_password') }}"
                           required
                           autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','eye-icon')">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label"></div>
                @error('password')
                    <div class="field-error">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                <div class="password-wrap">
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           placeholder="{{ __('auth.repeat_password') }}"
                           required
                           autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','conf-eye-icon')">
                        <i class="fas fa-eye" id="conf-eye-icon"></i>
                    </button>
                </div>
                <div class="field-status" id="match-status"></div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-lock"></i> {{ __('auth.set_password_button') }}
            </button>
        </form>
    </div>
</div>

<script>
function toggleTheme(){
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const icon = document.getElementById('theme-icon');
    
    if(currentTheme === 'dark'){
        html.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        icon.className = 'fas fa-sun';
    } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        icon.className = 'fas fa-moon';
    }
}

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
</script>

{{-- Unified Mobile Header Styles --}}
@include('partials.mobile-header-styles')
</body>
</html>
