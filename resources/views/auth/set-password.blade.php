<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('auth.set_password_title') }} — Nexus</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/auth-set-password.css') }}">
</head>
<body>

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
    window.authTranslations = {
        password_strength_weak: "{{ __('messages.password_strength_weak') }}",
        password_strength_medium: "{{ __('messages.password_strength_medium') }}",
        password_strength_strong: "{{ __('messages.password_strength_strong') }}",
        password_strength_very_strong: "{{ __('messages.password_strength_very_strong') }}",
        passwords_match: "{{ __('messages.passwords_match') }}",
        passwords_do_not_match: "{{ __('messages.passwords_do_not_match') }}"
    };
</script>
@vite(['resources/js/legacy/auth-set-password.js'])

</body>
</html>
