<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.account_suspended_title') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth-suspended.css') }}">
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <div style="display: flex; align-items: center; gap: 12px;">
            @include('partials.language-switcher')
            <button type="button" onclick="toggleTheme()" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer; padding: 8px; border-radius: 50%;" title="{{ __('auth.toggle_theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('login') }}" class="nav-link">← {{ __('auth.back_to_login') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <div class="auth-icon">
            <i class="fas fa-ban"></i>
        </div>

        <h1 class="login-title">{{ __('auth.account_suspended_title') }}</h1>
        <p class="login-sub">{{ __('auth.account_suspended_message') }}</p>

        <div class="contact-section">
            <h3><i class="fas fa-envelope"></i> {{ __('auth.need_help') }}</h3>
            <p>{{ __('auth.contact_support_message') }}</p>
        </div>

        <div class="card-footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> {{ __('auth.sign_out') }}
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
    </div>
</div>

<script src="{{ asset('js/auth-suspended.js') }}"></script>

</body>
</html>
