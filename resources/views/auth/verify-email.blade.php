<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.verify_email_title') }} — Nexus</title>

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

        /* Language Switcher - Always LTR */
        .language-switcher,
        .language-switcher *,
        .language-toggle,
        .language-dropdown,
        .language-option {
            direction: ltr !important;
            text-align: left !important;
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

        /* auth icon */
        .auth-icon{
            width:80px;
            height:80px;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 24px;
            font-size:36px;
            color:white;
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

        /* code inputs */
        .code-inputs {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 24px 0;
            flex-wrap: nowrap;
            direction: ltr !important;
        }
        .code-input {
            width: 48px;
            height: 56px;
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            border: 2px solid var(--border);
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
            color: var(--text);
            transition: all 0.2s;
            direction: ltr !important;
            text-align: center !important;
        }
        .code-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(94,96,206,0.2);
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
        .btn-verify{
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        .btn-verify:hover{
            transform:translateY(-1px);
            box-shadow: 0 6px 20px rgba(94,96,206,0.3);
        }

        /* resend section */
        .resend-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        .resend-section p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0 0 12px 0;
        }
        .resend-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
        }
        .resend-btn:disabled {
            color: var(--text-muted);
            cursor: not-allowed;
            text-decoration: none;
        }
        .timer {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
        }
        .timer span {
            font-weight: 600;
            color: var(--primary);
        }

        /* footer */
        .card-footer{
            margin-top:24px;
            padding-top:20px;
            border-top:1px solid var(--border);
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

        /* hidden sections */
        .send-code-section { margin-bottom: 24px; }
        .verification-code-form { display: none; }
        .verification-code-form.active { display: block; }
        .resend-section { display: none; }
        .resend-section.active { display: block; }

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
            .code-input{width:42px;height:50px;font-size:20px}
            .auth-icon{width:70px;height:70px;font-size:32px}
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
            @if(auth()->check())
                <a href="{{ route('users.show', auth()->user()) }}" class="back-btn">← {{ __('auth.back_to_profile') }}</a>
            @else
                <a href="{{ route('login') }}" class="back-btn">← {{ __('auth.back') }}</a>
            @endif
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <div class="auth-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h1 class="login-title">{{ __('auth.verify_email_title') }}</h1>
        <p class="login-sub" id="instruction-text">{{ __('auth.verify_email_subtitle') }}</p>

        @if(session('message'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> {{ session('message') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->has('code'))
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first('code') }}
            </div>
        @endif

        <!-- Send Code Section -->
        <div class="send-code-section" id="sendCodeSection">
            <form method="POST" action="{{ route('verification.send') }}" id="sendCodeForm">
                @csrf
                <button type="submit" class="btn btn-primary" id="sendCodeBtn">
                    <i class="fas fa-paper-plane"></i> {{ __('auth.send_verification_code') }}
                </button>
            </form>
        </div>

        <!-- Verification Code Form -->
        <form class="verification-code-form" method="POST" action="{{ route('verification.verify-code') }}" id="verifyForm">
            @csrf
            <div class="code-inputs" dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required autofocus dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required dir="ltr">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required dir="ltr">
            </div>

            <input type="hidden" name="code" id="fullCode">

            <button type="submit" class="btn btn-verify">
                <i class="fas fa-check-circle"></i> {{ __('auth.verify_email_button') }}
            </button>
        </form>

        <div class="resend-section" id="resendSection">
            <p>{{ __('auth.didnt_receive_code') }}</p>
            <form method="POST" action="{{ route('verification.send') }}" id="resendForm" style="display: inline;">
                @csrf
                <button type="submit" class="resend-btn" id="resendBtn">{{ __('auth.resend_code') }}</button>
            </form>
            <div class="timer">{{ __('auth.resend_available_in') }} <span id="countdown">60</span>s</div>
        </div>

        <div class="card-footer">
            @if(auth()->check())
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> {{ __('auth.sign_out') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            @else
                <a href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt"></i> {{ __('auth.back_to_login') }}
                </a>
            @endif
        </div>
    </div>
</div>

<script>
    // Check if user is already verified
    const userAlreadyVerified = @if(auth()->check() && auth()->user()->hasVerifiedEmail()) true @else false @endif;

    // Show verification form if there's a message about code being sent
    @if(session('message') && (str_contains(session('message'), 'sent') || str_contains(session('message'), 'code')))
        document.addEventListener('DOMContentLoaded', function() {
            showVerificationForm();
        });
    @endif

    // Show verification form if there was an error with the code
    @if($errors->has('code'))
        document.addEventListener('DOMContentLoaded', function() {
            showVerificationForm();
        });
    @endif

    // Function to show verification form and hide send code section
    function showVerificationForm() {
        document.getElementById('sendCodeSection').style.display = 'none';
        document.getElementById('verifyForm').classList.add('active');
        document.getElementById('resendSection').classList.add('active');

        // Focus on first code input
        const firstInput = document.querySelector('.code-input');
        if (firstInput) {
            firstInput.focus();
        }

        // Start countdown
        startCountdown();
    }

    // Get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
               document.querySelector('input[name="_token"]')?.value ||
               '';
    }

    // Handle send code form submission with AJAX
    document.getElementById('sendCodeForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (userAlreadyVerified) {
            // Check if user needs to set password
            const needsPassword = {{ auth()->check() && auth()->user()->password === null ? 'true' : 'false' }};
            if (needsPassword) {
                // Redirect to set password page
                window.location.href = '{{ route('password.set-password') }}';
                return;
            }
            showToast('{{ __('auth.account_already_verified') }}', 'info');
            return false;
        }

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('auth.sending') }}...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData,
                credentials: 'same-origin'
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // If user is already verified and needs password, redirect
                if (userAlreadyVerified) {
                    const needsPassword = {{ auth()->check() && auth()->user()->password === null ? 'true' : 'false' }};
                    if (needsPassword) {
                        window.location.href = '{{ route('password.set-password') }}';
                        return;
                    }
                }
                showVerificationForm();
                showToast('{{ __('auth.verification_code_sent') }}', 'success');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
            }

            // Check if response contains redirect
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (response.ok || response.status === 200) {
                if (data.message) {
                    showToast(data.message, 'success');
                }
                showVerificationForm();
            } else {
                showToast(data.message || data.error || '{{ __('auth.error') }}', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            form.submit();
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });

    // Handle resend form submission with AJAX
    document.getElementById('resendForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        // Check if user is already verified and needs password
        if (userAlreadyVerified) {
            const needsPassword = {{ auth()->check() && auth()->user()->password === null ? 'true' : 'false' }};
            if (needsPassword) {
                window.location.href = '{{ route('password.set-password') }}';
                return;
            }
        }

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('auth.sending') }}...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData,
                credentials: 'same-origin'
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // If user is already verified and needs password, redirect
                if (userAlreadyVerified) {
                    const needsPassword = {{ auth()->check() && auth()->user()->password === null ? 'true' : 'false' }};
                    if (needsPassword) {
                        window.location.href = '{{ route('password.set-password') }}';
                        return;
                    }
                }
                showToast('{{ __('auth.verification_code_sent') }}', 'success');
                startCountdown();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
            }

            // Check if response contains redirect
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (response.ok || response.status === 200) {
                if (data.message) {
                    showToast(data.message, 'success');
                }
                startCountdown();
            } else {
                showToast(data.message || data.error || '{{ __('auth.error') }}', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            form.submit();
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });

    // Auto-focus for code inputs
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');

    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');

            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);

            pastedData.split('').forEach((char, i) => {
                if (inputs[i]) {
                    inputs[i].value = char;
                }
            });

            if (pastedData.length > 0) {
                inputs[Math.min(pastedData.length, inputs.length - 1)].focus();
            }
        });
    });

    // Submit form with combined code
    document.getElementById('verifyForm').addEventListener('submit', (e) => {
        const inputValues = Array.from(inputs).map(input => input.value);
        const code = inputValues.join('');
        const hasEmptyInput = inputValues.some(val => val === '' || val === undefined || val === null);

        if (hasEmptyInput || code.length < 6) {
            e.preventDefault();
            showToast('{{ __('auth.enter_6_digit_code') }}', 'error');
            return false;
        }

        if (!/^\d{6}$/.test(code)) {
            e.preventDefault();
            showToast('{{ __('auth.code_must_be_numbers') }}', 'error');
            return false;
        }

        fullCodeInput.value = code;
    });

    // Countdown timer for resend
    let countdownInterval = null;
    function startCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');

        countdownEl.textContent = seconds;
        resendBtn.disabled = true;

        countdownInterval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
            }
        }, 1000);
    }

    // Toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            left: 20px;
            z-index: 9999;
            padding: 16px 20px;
            border-radius: 10px;
            background: ${type === 'success' ? '#30d158' : type === 'error' ? '#ef4444' : '#5e60ce'};
            color: white;
            font-weight: 600;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
            font-family: 'Inter', sans-serif;
            text-align: center;
            max-width: 400px;
            margin: 0 auto;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
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

<style>
@keyframes slideIn {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-100%); opacity: 0; }
}
</style>

</body>
</html>
