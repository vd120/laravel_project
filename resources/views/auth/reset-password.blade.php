<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.reset_password_title') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth-reset-password.css') }}">
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
            <a href="{{ route('login') }}" class="back-btn">{{ __('auth.back') }}</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">{{ __('auth.reset_password_title') }}</h1>
        <p class="login-sub">{{ __('auth.reset_password_subtitle') }}</p>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input type="email" name="email" id="email"
                       value="{{ $email ?? old('email') }}"
                       placeholder="{{ __('auth.email_placeholder') }}"
                       required autofocus autocomplete="email" readonly>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">{{ __('auth.new_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password"
                           placeholder="{{ __('auth.new_password_placeholder') }}"
                           required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','password-eye')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label"></div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('auth.confirm_new_password') }}</label>
                <div class="password-wrap">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           placeholder="{{ __('auth.confirm_new_password_placeholder') }}"
                           required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','confirm-eye')">
                        <i class="fas fa-eye" id="confirm-eye"></i>
                    </button>
                </div>
                <div class="field-status" id="match-status"></div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-lock"></i> {{ __('auth.reset_password_button') }}
            </button>
        </form>

        <div class="card-footer">
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt"></i> {{ __('auth.back_to_login') }}
            </a>
        </div>
    </div>
</div>

<script>
    window.authTranslations = {
        passwords_mismatch: "{{ __('messages.passwords_mismatch') }}",
        weak_password: "{{ __('messages.weak_password') }}"
    };
</script>
@vite(['resources/js/legacy/ui-utils.js', 'resources/js/legacy/auth-reset-password.js'])

</body>
</html>
