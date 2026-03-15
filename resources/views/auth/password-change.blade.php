<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.change_password_title') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth-password-change.css') }}">
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <div style="display: flex; align-items: center; gap: 12px;">
            @include('partials.language-switcher')
            <button type="button" id="themeToggle" onclick="toggleTheme()" title="{{ __('auth.toggle_theme') }}">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('users.show', auth()->user()) }}" class="back-btn">← {{ __('auth.back_to_profile') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">{{ __('auth.change_password_title') }}</h1>
        <p class="login-sub">{{ __('auth.change_password_subtitle') }}</p>

        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.change') }}">
            @csrf

            <!-- Hidden username field for accessibility -->
            <input type="text" name="username" value="{{ auth()->user()->email }}" autocomplete="username" style="display: none;" aria-hidden="true">

            <div class="field">
                <label for="current_password">{{ __('auth.current_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="current_password" id="current_password" placeholder="{{ __('auth.current_password_placeholder') }}" required autocomplete="current-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('current_password','current-eye')">
                        <i class="fas fa-eye" id="current-eye"></i>
                    </button>
                </div>
                @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="password">{{ __('auth.new_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password" placeholder="{{ __('auth.new_password_placeholder') }}" required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','password-eye')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label"></div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('auth.confirm_new_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('auth.confirm_new_password_placeholder') }}" required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','confirm-eye')">
                        <i class="fas fa-eye" id="confirm-eye"></i>
                    </button>
                </div>
                <div class="field-status" id="match-status"></div>
                @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">
                {{ __('auth.update_password_button') }} <i class="fas fa-shield-alt"></i>
            </button>
        </form>

        <div class="card-footer">
            <a href="{{ route('users.show', auth()->user()) }}">
                <i class="fas fa-user"></i> {{ __('auth.back_to_profile') }}
            </a>
        </div>
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
@vite(['resources/js/legacy/auth-password-change.js'])

</body>
</html>
