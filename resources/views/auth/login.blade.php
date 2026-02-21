<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111111">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — Nexus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* ============================================
           UNIFIED DESIGN SYSTEM - Nexus
           ============================================ */
        
        :root {
            /* Core Colors */
            --bg: #111111;
            --surface: #1a1a1a;
            --surface-hover: #242424;
            --border: #2a2a2a;
            --text: #f5f5f5;
            --text-muted: #888888;
            
            /* Brand Colors */
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-glow: rgba(59, 130, 246, 0.25);
            --secondary: #8b5cf6;
            --accent: #ef4444;
            --success: #22c55e;
            
            /* Spacing & Radius */
            --radius: 12px;
            --radius-lg: 16px;
            --radius-full: 9999px;
            
            /* Shadows */
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            
            /* Transitions */
            --transition: 200ms ease;
        }

        [data-theme="light"] {
            --bg: #ffffff;
            --surface: #f9fafb;
            --surface-hover: #f3f4f6;
            --border: #e5e7eb;
            --text: #111111;
            --text-muted: #6b7280;
            
            --primary: #3b82f6;
            --primary-hover: #1d4ed8;
            --primary-glow: rgba(59, 130, 246, 0.2);
            --secondary: #7c3aed;
            
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        body {
            font-size: 14px; line-height: 1.6;
            color: var(--text); background: var(--bg);
            min-height: 100vh; overflow-x: hidden;
        }

        /* Navigation */
        .nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .nav-logo {
            font-size: 1.25rem; font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .nav-actions { display: flex; align-items: center; gap: 12px; }

        .theme-btn {
            background: none; border: 1px solid var(--border);
            color: var(--text-muted); padding: 8px 12px;
            font-size: 12px; font-weight: 500; cursor: pointer;
            border-radius: var(--radius); transition: all var(--transition);
        }
        .theme-btn:hover { color: var(--text); border-color: var(--primary); }

        .back-link {
            font-size: 13px; color: var(--text-muted);
            text-decoration: none; transition: color var(--transition);
        }
        .back-link:hover { color: var(--text); }

        /* Main Layout */
        .page-wrap {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 100px 24px 40px;
        }

        /* Login Card */
        .login-card {
            width: 100%; max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 32px;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .card-label {
            font-size: 11px; color: var(--text-muted);
            letter-spacing: 0.1em; text-transform: uppercase;
            margin-bottom: 8px;
        }

        .card-title {
            font-size: 28px; font-weight: 800;
            color: var(--text);
            margin-bottom: 8px; line-height: 1.1;
        }

        .card-subtitle {
            font-size: 14px; color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Form */
        .field { margin-bottom: 20px; }

        .field label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text); margin-bottom: 8px;
        }

        .field input {
            width: 100%; padding: 12px 16px;
            font-size: 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            outline: none; transition: all var(--transition);
        }

        .field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .field input::placeholder { color: var(--text-muted); }

        .password-wrap { position: relative; }

        .password-wrap input { padding-right: 44px; }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            padding: 4px; font-size: 14px;
            transition: color var(--transition);
        }
        .toggle-pw:hover { color: var(--text); }

        .field-error {
            font-size: 12px; color: var(--accent);
            margin-top: 6px;
        }

        /* Remember / Forgot row */
        .extras {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 24px;
            font-size: 13px; color: var(--text-muted);
        }

        .extras label {
            display: flex; align-items: center; gap: 8px;
            cursor: pointer; font-weight: 400;
        }

        .extras input[type="checkbox"] {
            accent-color: var(--primary);
            width: 14px; height: 14px;
        }

        .extras a {
            color: var(--text-muted); text-decoration: none;
            font-size: 12px; transition: color var(--transition);
        }
        .extras a:hover { color: var(--primary); }

        /* Submit Button */
        .btn-submit {
            width: 100%; padding: 14px;
            font-size: 14px; font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none;
            border-radius: var(--radius); cursor: pointer;
            transition: all var(--transition);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-submit:hover {
            opacity: 0.95;
        }

        /* Google Button */
        .btn-google {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; width: 100%; padding: 12px;
            margin-top: 12px;
            font-size: 14px; font-weight: 500;
            color: var(--text); background: transparent;
            border: 1px solid var(--border);
            text-decoration: none; border-radius: var(--radius);
            transition: all var(--transition);
        }

        .btn-google:hover {
            border-color: var(--primary); background: var(--surface-hover);
        }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 20px 0; color: var(--text-muted);
            font-size: 12px;
        }

        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px;
            background: var(--border);
        }

        /* Footer */
        .card-footer {
            margin-top: 24px; padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center; font-size: 14px;
            color: var(--text-muted);
        }

        .card-footer a {
            color: var(--primary); text-decoration: none;
            font-weight: 600; transition: opacity var(--transition);
        }

        .card-footer a:hover { opacity: 0.8; }

        /* Responsive */
        @media (max-width: 480px) {
            .nav { padding: 12px 16px; }
            .page-wrap { padding: 80px 16px 32px; }
            .login-card { padding: 24px; }
            .card-title { font-size: 24px; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="{{ route('home') }}" class="nav-logo">Nexus</a>
        <div class="nav-actions">
            <a href="{{ route('home') }}" class="back-link">← Back</a>
            <button class="theme-btn" id="theme-toggle">
                <i class="fas fa-sun"></i> Theme
            </button>
        </div>
    </nav>

    @if(session('suspended') || session()->has('suspended'))
    <script>window.location.href = '{{ route("auth.suspended") }}';</script>
    @endif

    <div class="page-wrap">
        <div class="login-card">
            <p class="card-label">Sign In</p>
            <h1 class="card-title">Welcome Back</h1>
            <p class="card-subtitle">Sign in to continue your journey</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required autocomplete="username">
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="password"
                            placeholder="••••••••"
                            required autocomplete="current-password">
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
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    Sign In <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="divider">or continue with</div>

            <a href="{{ route('login.google') }}" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>

            <div class="card-footer">
                Don't have an account? <a href="{{ route('register') }}">Sign up</a>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle - consistent with main app
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon();

        function updateThemeIcon() {
            const icon = document.querySelector('#theme-toggle i');
            const theme = html.getAttribute('data-theme');
            icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        document.getElementById('theme-toggle').addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        });

        // Password toggle
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }
    </script>
</body>
</html>