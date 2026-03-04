<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Suspended — Nexus</title>

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
            text-align: center;
        }
        .login-card::before{
            content:"";
            position:absolute;
            top:-40px;
            right:-40px;
            width:140px;
            height:140px;
            background:var(--error);
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
            line-height:1.6;
        }

        /* auth icon */
        .auth-icon{
            width:80px;
            height:80px;
            background:linear-gradient(135deg,var(--error),#f97316);
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 24px;
            font-size:36px;
            color:white;
        }

        /* contact section */
        .contact-section {
            padding: 20px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin: 24px 0;
            text-align: left;
        }
        .contact-section h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .contact-section p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.5;
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

        @media(max-width:480px){
            .login-card{padding:35px 25px}
            .login-title{font-size:30px}
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
            <button type="button" onclick="toggleTheme()" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer; padding: 8px; border-radius: 50%;">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            <a href="{{ route('login') }}" class="nav-link">← Back to Login</a>
        </div>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <div class="auth-icon">
            <i class="fas fa-ban"></i>
        </div>

        <h1 class="login-title">Account Suspended</h1>
        <p class="login-sub">Your account has been temporarily suspended due to a violation of our community guidelines. During this suspension, you cannot access most features of the platform.</p>

        <div class="contact-section">
            <h3><i class="fas fa-envelope"></i> Need Help?</h3>
            <p>If you believe this is a mistake or would like to appeal this decision, please contact our support team for assistance.</p>
        </div>

        <div class="card-footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
    </div>
</div>

<script>
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
