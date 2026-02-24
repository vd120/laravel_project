<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign In — Nexus</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>
:root{
    --bg-dark:#0d0d0d;
    --bg-glass:rgba(22,22,22,0.7);
    --border:rgba(255,255,255,0.08);
    --text-primary:#ffffff;
    --text-secondary:#98989f;
    --primary:#5e60ce;
    --secondary:#7400b8;
}

*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-dark);
    color:var(--text-primary);
    -webkit-font-smoothing:antialiased;
    min-height:100vh;
}

/* NAV — Same as landing */
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

/* LOGIN CARD — Apple Glass */
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
    background:linear-gradient(135deg,#fff 0%,#a1a1a6 100%);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.login-sub{
    font-size:16px;
    color:#86868b;
    margin-bottom:40px;
}

/* Fields */
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

.field-error{
    font-size:13px;
    color:#ff6b6b;
    margin-top:6px;
}

/* Password toggle */
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
}

/* Extras */
.extras{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    margin-bottom:30px;
    color:#86868b;
}
.extras input{accent-color:var(--primary)}
.extras a{
    color:#86868b;
    text-decoration:none;
}
.extras a:hover{color:#fff}

/* Buttons */
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

/* Google */
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

/* Footer */
.card-footer{
    margin-top:35px;
    text-align:center;
    font-size:14px;
    color:#86868b;
}
.card-footer a{
    color:#fff;
    text-decoration:none;
}
.card-footer a:hover{text-decoration:underline}

/* Responsive */
@media(max-width:480px){
    .login-card{padding:35px 25px}
    .login-title{font-size:30px}
}
</style>
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">Nexus</a>
        <a href="{{ route('home') }}" class="nav-link">← Back</a>
    </div>
</nav>

<div class="page">
    <div class="login-card">
        <h1 class="login-title">Welcome Back</h1>
        <p class="login-sub">Sign in to continue your journey.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label>Password</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password" required autocomplete="current-password">
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
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
                <!-- Removed duplicate hardcoded link that caused double forgot link -->
            </div>

            <button type="submit" class="btn btn-primary">
                Sign In
            </button>
        </form>

        <div class="divider">or continue with</div>

        <a href="{{ route('login.google') }}" class="btn-google">
            <i class="fab fa-google"></i>
            Continue with Google
        </a>

        <div class="card-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    const input=document.getElementById('password');
    const icon=document.getElementById('eye-icon');
    input.type=input.type==='password'?'text':'password';
    icon.className=input.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}
</script>

</body>
</html>