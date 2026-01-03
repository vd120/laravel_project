@extends('layouts.app')

@section('content')
@if(session('suspended'))
<script>
    window.location.href = '{{ route("auth.suspended") }}';
</script>
@endif

<div class="auth-container">
    <div class="auth-card">
        <h2 style="text-align: center; margin-bottom: 30px; color: var(--twitter-dark);">Login</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="email" style="color: var(--twitter-dark); font-weight: 500;">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="width: 100%; padding: 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                @error('email') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password" style="color: var(--twitter-dark); font-weight: 500;">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" onclick="togglePassword()" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--twitter-gray); cursor: pointer; transition: color 0.2s ease;" onmouseover="this.style.color='var(--twitter-blue)';" onmouseout="this.style.color='var(--twitter-gray)';">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                @error('password') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <input type="hidden" name="remember" value="0">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--twitter-gray);">
                    <input type="checkbox" name="remember" value="1" style="margin: 0;">
                    Remember me
                </label>
            </div>

            <button type="submit" style="width: 100%; padding: 14px; background: var(--twitter-blue); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);" onmouseover="this.style.background='#1A91DA'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(29, 161, 242, 0.4)';" onmouseout="this.style.background='var(--twitter-blue)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(29, 161, 242, 0.3)';">Login</button>
        </form>
        <p style="text-align: center; margin-top: 20px; color: var(--twitter-gray);">Don't have an account? <a href="{{ route('register') }}" style="color: var(--twitter-blue); text-decoration: none;" onmouseover="this.style.color='#1A91DA';" onmouseout="this.style.color='var(--twitter-blue)';">Register</a></p>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 60px);
    padding: 20px;
    box-sizing: border-box;
}

/* Enhanced centering for laptops */
@media (min-width: 768px) {
    .auth-container {
        min-height: calc(100vh - 120px);
        padding: 40px 30px;
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .auth-card {
        width: 550px;
        max-width: 550px;
        padding: 40px;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    }

    .auth-card h2 {
        font-size: 32px;
        margin-bottom: 40px;
        font-weight: 700;
    }

    .auth-card input {
        padding: 18px 20px !important;
        font-size: 18px !important;
        border-radius: 14px !important;
    }

    .auth-card button[type="submit"] {
        padding: 18px !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        border-radius: 14px !important;
    }
}

.auth-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    border: 2px solid var(--border-color);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}

/* Responsive breakpoints */

/* Ultra-wide Laptops (2560px+) */
@media (min-width: 2560px) {
    .auth-container {
        padding: 60px 40px;
    }

    .auth-card {
        max-width: 600px;
        padding: 50px;
    }
}

/* Large Laptops (1920px - 2559px) */
@media (min-width: 1920px) and (max-width: 2559px) {
    .auth-container {
        padding: 50px 30px;
    }

    .auth-card {
        max-width: 550px;
        padding: 45px;
    }
}

/* Standard Laptops (1440px - 1919px) */
@media (min-width: 1440px) and (max-width: 1919px) {
    .auth-container {
        padding: 40px 25px;
    }

    .auth-card {
        max-width: 500px;
        padding: 40px;
    }
}

/* Compact Laptops (1366px - 1439px) */
@media (min-width: 1366px) and (max-width: 1439px) {
    .auth-container {
        padding: 35px 20px;
    }

    .auth-card {
        max-width: 480px;
        padding: 38px;
    }
}

/* Small Laptops (1200px - 1365px) */
@media (min-width: 1200px) and (max-width: 1365px) {
    .auth-container {
        padding: 30px 20px;
    }

    .auth-card {
        max-width: 450px;
        padding: 35px;
    }
}

/* Netbook/Ultra-portable (992px - 1199px) */
@media (min-width: 992px) and (max-width: 1199px) {
    .auth-container {
        padding: 25px 15px;
    }

    .auth-card {
        max-width: 420px;
        padding: 32px;
    }
}

/* Medium Screens (768px - 991px) */
@media (min-width: 768px) and (max-width: 991px) {
    .auth-container {
        padding: 20px 15px;
    }

    .auth-card {
        max-width: 380px;
        padding: 28px;
    }
}

/* Small-Medium Screens (600px - 767px) */
@media (min-width: 600px) and (max-width: 767px) {
    .auth-container {
        padding: 20px 10px;
    }

    .auth-card {
        max-width: 90%;
        padding: 25px;
    }
}

/* Mobile Large (480px - 599px) */
@media (min-width: 480px) and (max-width: 599px) {
    .auth-container {
        padding: 15px 10px;
        min-height: calc(100vh - 50px);
    }

    .auth-card {
        max-width: 95%;
        padding: 20px;
        border-radius: 12px;
    }

    .auth-card h2 {
        font-size: 24px;
        margin-bottom: 25px;
    }
}

/* Mobile Small (360px - 479px) */
@media (min-width: 360px) and (max-width: 479px) {
    .auth-container {
        padding: 10px 8px;
    }

    .auth-card {
        max-width: 100%;
        padding: 18px;
        border-radius: 10px;
    }

    .auth-card h2 {
        font-size: 22px;
        margin-bottom: 20px;
    }

    .auth-card input {
        padding: 12px 16px !important;
        font-size: 16px !important; /* Prevents zoom on iOS */
    }

    .auth-card button[type="submit"] {
        padding: 12px !important;
        font-size: 16px !important;
    }
}

/* Mobile Extra Small (320px - 359px) */
@media (max-width: 359px) {
    .auth-container {
        padding: 8px 6px;
    }

    .auth-card {
        padding: 16px;
        border-radius: 8px;
    }

    .auth-card h2 {
        font-size: 20px;
        margin-bottom: 18px;
    }

    .auth-card input {
        padding: 10px 14px !important;
        font-size: 16px !important;
    }

    .auth-card button[type="submit"] {
        padding: 10px !important;
        font-size: 16px !important;
    }
}

/* Landscape Orientation Adjustments */
@media (max-height: 500px) and (orientation: landscape) {
    .auth-container {
        min-height: auto;
        padding: 10px;
    }

    .auth-card {
        padding: 15px;
    }

    .auth-card h2 {
        font-size: 20px;
        margin-bottom: 15px;
    }
}

/* Ultra-wide aspect ratios */
@media (min-aspect-ratio: 21/9) {
    .auth-container {
        padding: 40px;
    }

    .auth-card {
        max-width: 500px;
    }
}

/* Square screens (like some tablets) */
@media (aspect-ratio: 1/1) {
    .auth-container {
        padding: 20px;
    }

    .auth-card {
        max-width: 80%;
    }
}

/* Touch-friendly interactions */
@media (hover: none) and (pointer: coarse) {
    .auth-card input:focus {
        transform: none !important;
    }

    .auth-card button:hover {
        transform: none !important;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .auth-card {
        border-width: 3px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .auth-card input,
    .auth-card button {
        transition: none !important;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fas fa-eye';
    }
}
</script>

@endsection
