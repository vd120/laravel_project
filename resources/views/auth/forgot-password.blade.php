<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.forgot_password_title') }} — Nexus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth-forgot-password.css') }}">
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

@vite(['resources/js/legacy/ui-utils.js', 'resources/js/legacy/auth-forgot-password.js'])

</body>
</html>
