<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account — Nexus</title>

    <!-- same fonts & icons as login -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        /* === EXACT LOGIN PAGE DESIGN (preserved) === */
        :root{
            --bg-dark:#0d0d0d;
            --bg-glass:rgba(22,22,22,0.7);
            --border:rgba(255,255,255,0.08);
            --text-primary:#ffffff;
            --text-secondary:#98989f;
            --primary:#5e60ce;
            --secondary:#7400b8;
            /* extended for register features – same color language */
            --success: #22c55e;
            --warning: #f59e0b;
            --error: #ff6b6b;
        }

        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter',sans-serif;
            background:var(--bg-dark);
            color:var(--text-primary);
            -webkit-font-smoothing:antialiased;
            min-height:100vh;
        }

        /* NAV — identical to login */
        nav{
            position:fixed;
            top:0;
            width:100%;
            padding:14px 40px;
            backdrop-filter:blur(30px);
            -webkit-backdrop-filter:blur(30px);
            background:rgba(0,0,0,0.15);
            border-bottom:1px solid var(--border);
            display:flex;
            justify-content:center;
            z-index:100;
        }
        .nav-container{
            max-width:980px;
            width:100%;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .nav-brand{
            font-weight:600;
            font-size:18px;
            text-decoration:none;
            color:#f5f5f7;
        }
        .nav-link{
            font-size:13px;
            color:#86868b;
            text-decoration:none;
            transition:0.3s;
        }
        .nav-link:hover{color:#fff}

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
            background:var(--bg-glass);
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
            background:linear-gradient(135deg,#fff 0%,#a1a1a6 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }
        .login-sub{
            font-size:16px;
            color:#86868b;
            margin-bottom:40px;
        }

        /* fields — same style as login */
        .field{margin-bottom:24px}
        .field label{
            display:block;
            font-size:14px;
            margin-bottom:8px;
            color:#f5f5f7;
        }
        .field input{
            width:100%;
            padding:14px 18px;
            border-radius:14px;
            border:1px solid var(--border);
            background:rgba(255,255,255,0.03);
            color:#fff;
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
            color:#86868b;
            cursor:pointer;
            font-size:16px;
        }
        .toggle-pw:hover{color:#fff}

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
            color: var(--text-secondary);
        }
        .strength-label.weak { color: var(--error); }
        .strength-label.medium { color: var(--warning); }
        .strength-label.strong { color: var(--success); }
        .strength-label.very-strong { color: var(--primary); }

        /* terms row */
        .terms-row {
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 30px;
            font-size: 13px; color: #86868b;
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
            border:1px solid rgba(255,107,107,0.3);
            background:rgba(255,107,107,0.1);
            border-radius:14px;
            margin-bottom:25px;
            font-size:13px;
            color:#ff8a8a;
        }

        /* buttons — identical to login */
        .btn{
            width:100%;
            padding:14px;
            border-radius:980px;
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
            color:#86868b;
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
            border-radius:980px;
            border:1px solid var(--border);
            background:transparent;
            color:#fff;
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
            color:#86868b;
        }
        .card-footer a{
            color:#fff;
            text-decoration:none;
            font-weight:500;
        }
        .card-footer a:hover{text-decoration:underline}

        @media(max-width:480px){
            .login-card{padding:35px 25px}
            .login-title{font-size:30px}
        }
    </style>
</head>
<body>

<!-- NAV (identical to login, no theme toggle) -->
<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <a href="{{ route('home') }}" class="nav-link">← Back</a>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <!-- register header (adapted from login style) -->
        <h1 class="login-title">Create Account</h1>
        <p class="login-sub">Join us and start connecting.</p>

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

            <!-- username field (with status) -->
            <div class="field">
                <label for="username">Username</label>
                <input type="text" name="username" id="username"
                    value="{{ old('username') }}"
                    placeholder="Choose a username"
                    required minlength="3"
                    autocomplete="username">
                <div class="field-status" id="username-status"></div>
                @error('username')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- email field -->
            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required autocomplete="email">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <!-- password field + strength meter -->
            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password"
                        placeholder="Create a password"
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
                <label for="password_confirmation">Confirm Password</label>
                <div class="password-wrap">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        placeholder="Repeat your password"
                        required autocomplete="new-password">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','conf-eye-icon')">
                        <i class="fas fa-eye" id="conf-eye-icon"></i>
                    </button>
                </div>
                <div class="field-status" id="match-status"></div>
            </div>

            <!-- terms checkbox (exactly as original) -->
            <div class="terms-row">
                <input type="checkbox" name="terms" id="terms" value="1" required>
                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>

            <!-- submit button (styled like login's primary) -->
            <button type="submit" class="btn btn-primary">
                Create Account <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="divider">or continue with</div>

        <!-- google button (same as login) -->
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
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
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
        const label = { weak: 'Weak', medium: 'Medium', strong: 'Strong', 'very-strong': 'Very Strong' }[level];

        fill.className = 'strength-fill ' + level;
        lbl.className = 'strength-label ' + level;
        lbl.textContent = label;
        checkMatch();
    });

    // password match
    function checkMatch() {
        const pass = document.getElementById('password').value;
        const conf = document.getElementById('password_confirmation').value;
        const div = document.getElementById('match-status');

        if (!conf.length) { div.textContent = ''; div.className = 'field-status'; return; }

        if (pass === conf) {
            div.textContent = '✓ Passwords match';
            div.className = 'field-status matching';
        } else {
            div.textContent = '✗ Passwords do not match';
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
            status.textContent = 'Min. 3 characters required';
            status.className = 'field-status warning';
            return;
        }

        status.textContent = 'Checking…';
        status.className = 'field-status checking';

        usernameTimer = setTimeout(function() {
            // Using same endpoint as original – adjust if needed
            fetch('/api/check-username/' + encodeURIComponent(username))
                .then(r => r.json())
                .then(data => {
                    if (data.available) {
                        status.textContent = '✓ Available';
                        status.className = 'field-status available';
                    } else {
                        status.textContent = '✗ Already taken';
                        status.className = 'field-status taken';
                    }
                })
                .catch(() => { status.textContent = ''; status.className = 'field-status'; });
        }, 500);
    });
</script>

</body>
</html>