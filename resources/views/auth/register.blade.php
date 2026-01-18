@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2 class="login-title">Register</h2>
        <form method="POST" action="{{ route('register') }}" class="login-form">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required placeholder="Choose a username">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div id="username-status"></div>
                @error('username') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Enter your email address">
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                @error('email') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required placeholder="Create a password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye-icon"></i>
                    </button>
                </div>
                <div id="password-strength" class="password-strength-bar"></div>
                <div id="password-strength-text" class="password-strength-text"></div>
                @error('password') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Confirm your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye" id="confirm-password-eye-icon"></i>
                    </button>
                </div>
                @error('password_confirmation') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="login-btn" id="register-btn">
                <span class="btn-text">Create Account</span>
                <div class="btn-loader" style="display: none;">
                    <div class="spinner"></div>
                </div>
                <div class="btn-glow"></div>
                <div class="btn-particles" style="display: none;">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
            </button>
        </form>
        <p style="text-align: center; margin-top: 20px; color: var(--twitter-gray);">Already have an account? <a href="{{ route('login') }}" style="color: var(--twitter-blue); text-decoration: none;" onmouseover="this.style.color='#1A91DA';" onmouseout="this.style.color='var(--twitter-blue)';">Login</a></p>
    </div>
</div>

<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background:
        linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%),
        url('https://zebreus.github.io/all-gnome-backgrounds/images/earth-horizon-1abefd2c263947e408c36d3972da15fca4790951.webp');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

/* Optimize background image for mobile */
@media (max-width: 767px) {
    body {
        background-attachment: scroll !important; /* Better performance on mobile */
        background-size: cover !important;
    }
}

.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    box-sizing: border-box;
    position: relative;
    z-index: 1;
}

/* Laptop styles - simplified like mobile to prevent lagging */
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
        width: 420px;
        max-width: 420px;
        padding: 35px;
        border-radius: 20px;
        background: rgba(0, 0, 0, 0.6) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .auth-card::before,
    .auth-card::after {
        display: none !important;
    }

    .auth-card h2 {
        font-size: 32px;
        margin-bottom: 40px;
        font-weight: 700;
    }

    .auth-card input {
        padding: 16px 50px 16px 50px !important;
        font-size: 16px !important;
        border-radius: 12px !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"] {
        padding: 18px 24px !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        border-radius: 12px !important;
        background: var(--twitter-blue) !important;
        box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"]::before {
        display: none !important;
    }

    .btn-glow,
    .btn-particles {
        display: none !important;
    }

    .password-toggle:hover {
        transform: translateY(-50%) !important;
        background: none !important;
        box-shadow: none !important;
    }

    .input-wrapper input:focus + .input-icon,
    .input-wrapper input:focus ~ .input-icon {
        transform: translateY(-50%) !important;
    }

    .auth-card input:valid:focus,
    .auth-card input:invalid:not(:placeholder-shown):focus {
        animation: none !important;
        box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.3) !important;
    }

    .auth-card input:invalid:not(:placeholder-shown) {
        animation: none !important;
    }

    .error-message {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: none !important;
        transition: none !important;
    }
}

.auth-card {
    background: rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    width: 100%;
    max-width: 420px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
}



/* Ultra-Advanced Button Styling */
.login-btn {
    position: relative;
    width: 100%;
    padding: 20px 32px;
    background: linear-gradient(135deg,
        var(--twitter-blue) 0%,
        #1A91DA 20%,
        var(--twitter-blue) 40%,
        #1A91DA 60%,
        var(--twitter-blue) 80%,
        #1A91DA 100%);
    background-size: 200% 200%;
    color: white;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
        0 8px 32px rgba(29, 161, 242, 0.3),
        0 0 60px rgba(29, 161, 242, 0.1),
        inset 0 2px 0 rgba(255,255,255,0.2),
        inset 0 1px 0 rgba(255,255,255,0.1);
    overflow: hidden;
    z-index: 10;
    animation: btnPulse 3s ease-in-out infinite;
}

.login-btn:hover {
    box-shadow:
        0 8px 25px rgba(29, 161, 242, 0.4),
        0 0 40px rgba(29, 161, 242, 0.2),
        inset 0 2px 0 rgba(255,255,255,0.3);
}



/* Advanced shimmer effect */
.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -150%;
    width: 150%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(255,255,255,0.4) 30%,
        rgba(255,255,255,0.6) 50%,
        rgba(255,255,255,0.4) 70%,
        transparent 100%
    );
    transition: left 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 2;
}

.login-btn:hover::before {
    left: 150%;
}

/* Button glow effect */
.btn-glow {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg,
        var(--twitter-blue),
        #1A91DA,
        var(--twitter-blue),
        #1A91DA);
    border-radius: 18px;
    opacity: 0;
    z-index: -1;
    transition: opacity 0.4s ease;
    filter: blur(8px);
}

.login-btn:hover .btn-glow {
    opacity: 0.8;
    animation: glowRotate 2s linear infinite;
}

/* Particle effects */
.btn-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.btn-particles span {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255,255,255,0.8);
    border-radius: 50%;
    animation: particleFloat 3s ease-in-out infinite;
    opacity: 0;
}

.btn-particles span:nth-child(1) { left: 20%; animation-delay: 0s; }
.btn-particles span:nth-child(2) { left: 40%; animation-delay: 0.5s; }
.btn-particles span:nth-child(3) { left: 60%; animation-delay: 1s; }
.btn-particles span:nth-child(4) { left: 80%; animation-delay: 1.5s; }
.btn-particles span:nth-child(5) { left: 30%; animation-delay: 2s; }

/* Button text and loader */
.btn-text {
    position: relative;
    z-index: 3;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.btn-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn-loader.show {
    opacity: 1;
}

.btn-loader.hide {
    opacity: 0;
}

/* Loading spinner */
.spinner {
    width: 24px;
    height: 24px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top: 3px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Animations */
@keyframes btnPulse {
    0%, 100% {
        box-shadow:
            0 8px 32px rgba(29, 161, 242, 0.3),
            0 0 60px rgba(29, 161, 242, 0.1),
            inset 0 2px 0 rgba(255,255,255,0.2);
    }
    50% {
        box-shadow:
            0 8px 32px rgba(29, 161, 242, 0.4),
            0 0 80px rgba(29, 161, 242, 0.2),
            inset 0 2px 0 rgba(255,255,255,0.25);
    }
}

@keyframes btnGlow {
    0% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.3) contrast(1.2);
    }
    100% {
        filter: brightness(1.1) contrast(1.1);
    }
}

@keyframes glowRotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes particleFloat {
    0% {
        transform: translateY(0) scale(0);
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100px) scale(1);
        opacity: 0;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Success state */
.login-btn.success {
    background: linear-gradient(135deg,
        #00BA7C 0%,
        #00C46A 30%,
        #00BA7C 70%,
        #00C46A 100%);
    box-shadow:
        0 8px 32px rgba(0, 186, 124, 0.4),
        0 0 60px rgba(0, 186, 124, 0.2);
    animation: successPulse 2s ease-in-out infinite;
}

.login-btn.success .btn-text::after {
    content: ' ✓';
    animation: checkmarkSuccess 0.8s ease;
}

@keyframes successPulse {
    0%, 100% {
        box-shadow:
            0 8px 32px rgba(0, 186, 124, 0.4),
            0 0 60px rgba(0, 186, 124, 0.2);
    }
    50% {
        box-shadow:
            0 8px 32px rgba(0, 186, 124, 0.6),
            0 0 80px rgba(0, 186, 124, 0.3);
    }
}

@keyframes checkmarkSuccess {
    0% {
        opacity: 0;
        transform: scale(0) rotate(-180deg);
    }
    50% {
        opacity: 1;
        transform: scale(1.2) rotate(0deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

/* Mobile optimizations */
@media (max-width: 767px) {
    .login-btn {
        padding: 16px 24px;
        font-size: 16px;
        border-radius: 12px;
    }

    .btn-glow {
        border-radius: 14px;
    }
}

/* Powerful link effects */
.auth-card a {
    position: relative;
    transition: all 0.3s ease;
    text-shadow: 0 0 8px rgba(29, 161, 242, 0.3);
}

.auth-card a:hover {
    transform: translateY(-1px);
    text-shadow: 0 0 15px rgba(29, 161, 242, 0.6);
}

.auth-card a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--twitter-blue), #1A91DA);
    transition: width 0.3s ease;
    border-radius: 1px;
}

.auth-card a:hover::after {
    width: 100%;
    box-shadow: 0 0 8px var(--twitter-blue);
}

/* Enhanced password toggle */
.auth-card .password-toggle {
    transition: all 0.3s ease;
    border-radius: 50%;
    padding: 8px;
    margin: -4px;
}

.auth-card .password-toggle:hover {
    background: rgba(29, 161, 242, 0.1);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 0 10px rgba(29, 161, 242, 0.2);
}

/* No animations on larger screens */
@media (min-width: 768px) {
    .auth-card {
        /* No animations */
    }
}

/* Enhanced error message styling */
.auth-card .error-message {
    background: rgba(244, 33, 46, 0.1);
    border: 1px solid rgba(244, 33, 46, 0.3);
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 8px;
    font-size: 13px;
    color: var(--error-color);
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Password strength indicator */
#password-strength {
    height: 6px;
    border-radius: 3px;
    margin-top: 8px;
    transition: all 0.3s ease;
    background: #eee;
    opacity: 0;
    transform: scaleX(0);
    transform-origin: left;
}

#password-strength.weak,
#password-strength.medium,
#password-strength.strong,
#password-strength.very-strong {
    opacity: 1;
    transform: scaleX(1);
}

#password-strength.weak {
    background: #ff4444 !important;
    width: 25% !important;
}

#password-strength.medium {
    background: #ffaa00 !important;
    width: 50% !important;
}

#password-strength.strong {
    background: #00aa00 !important;
    width: 75% !important;
}

#password-strength.very-strong {
    background: #00dd00 !important;
    width: 100% !important;
}

#password-strength-text {
    font-size: 14px;
    margin-top: 8px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

#password-strength-text.weak,
#password-strength-text.very-weak {
    color: #ff4444;
}

#password-strength-text.medium {
    color: #ffaa00;
}

#password-strength-text.strong {
    color: #00aa00;
}

#password-strength-text.very-strong {
    color: #00dd00;
}

#username-status.checking {
    color: #ffa726;
}

#username-status.available {
    color: #28a745;
}

#username-status.taken {
    color: #dc3545;
}

#username-status.invalid {
    color: #dc3545;
}

#username-status.warning {
    color: #856404;
}

#username-status.error {
    color: #6c757d;
}

/* Responsive breakpoints */

/* Ultra-wide Laptops (2560px+) - simplified like mobile */
@media (min-width: 2560px) {
    .auth-container {
        padding: 40px 30px;
    }

    .auth-card {
        max-width: 420px;
        padding: 35px;
        background: rgba(0, 0, 0, 0.6) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .auth-card::before,
    .auth-card::after {
        display: none !important;
    }

    .auth-card h2 {
        text-shadow: 0 0 5px var(--twitter-blue) !important;
    }

    .auth-card h2::before,
    .auth-card h2::after {
        display: none !important;
    }

    .auth-card input {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"] {
        background: var(--twitter-blue) !important;
        box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"]::before {
        display: none !important;
    }

    .btn-glow,
    .btn-particles {
        display: none !important;
    }

    .error-message {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: none !important;
        transition: none !important;
    }
}

/* Large Laptops (1920px - 2559px) - simplified like mobile */
@media (min-width: 1920px) and (max-width: 2559px) {
    .auth-container {
        padding: 40px 30px;
    }

    .auth-card {
        max-width: 420px;
        padding: 35px;
        background: rgba(0, 0, 0, 0.6) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .auth-card::before,
    .auth-card::after {
        display: none !important;
    }

    .auth-card h2 {
        text-shadow: 0 0 5px var(--twitter-blue) !important;
    }

    .auth-card h2::before,
    .auth-card h2::after {
        display: none !important;
    }

    .auth-card input {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"] {
        background: var(--twitter-blue) !important;
        box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"]::before {
        display: none !important;
    }

    .btn-glow,
    .btn-particles {
        display: none !important;
    }

    .error-message {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: none !important;
        transition: none !important;
    }
}

/* Standard Laptops (1440px - 1919px) - simplified like mobile */
@media (min-width: 1440px) and (max-width: 1919px) {
    .auth-container {
        padding: 40px 30px;
    }

    .auth-card {
        max-width: 420px;
        padding: 35px;
        background: rgba(0, 0, 0, 0.6) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .auth-card::before,
    .auth-card::after {
        display: none !important;
    }

    .auth-card h2 {
        text-shadow: 0 0 5px var(--twitter-blue) !important;
    }

    .auth-card h2::before,
    .auth-card h2::after {
        display: none !important;
    }

    .auth-card input {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"] {
        background: var(--twitter-blue) !important;
        box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"]::before {
        display: none !important;
    }

    .btn-glow,
    .btn-particles {
        display: none !important;
    }

    .error-message {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: none !important;
        transition: none !important;
    }
}

/* Compact Laptops (1366px - 1439px) - simplified like mobile */
@media (min-width: 1366px) and (max-width: 1439px) {
    .auth-container {
        padding: 40px 30px;
    }

    .auth-card {
        max-width: 420px;
        padding: 35px;
        background: rgba(0, 0, 0, 0.6) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .auth-card::before,
    .auth-card::after {
        display: none !important;
    }

    .auth-card h2 {
        text-shadow: 0 0 5px var(--twitter-blue) !important;
    }

    .auth-card h2::before,
    .auth-card h2::after {
        display: none !important;
    }

    .auth-card input {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"] {
        background: var(--twitter-blue) !important;
        box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
        transition: none !important;
        animation: none !important;
    }

    .auth-card button[type="submit"]::before {
        display: none !important;
    }

    .btn-glow,
    .btn-particles {
        display: none !important;
    }

    .error-message {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: none !important;
        transition: none !important;
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

/* Powerful Neon Title Effect */
.login-title {
    text-align: center;
    margin-bottom: 40px;
    color: var(--twitter-dark);
    font-weight: 300;
    font-size: 48px;
    letter-spacing: 4px;
    text-transform: uppercase;
    position: relative;
    z-index: 10;
    /* Simple glow effect like mobile */
    text-shadow: 0 0 10px var(--twitter-blue);
}





/* Form Styling Overhaul */
.login-form {
    width: 100%;
}

.form-group {
    margin-bottom: 24px;
    position: relative;
}

.form-group label {
    display: block;
    color: #e5e7eb;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
    transition: all 0.3s ease;
    text-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper input {
    width: 100%;
    padding: 16px 50px 16px 50px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.4);
    color: #ffffff;
    font-size: 16px;
    font-weight: 400;
    transition: all 0.3s ease;
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset,
        inset 0 2px 0 rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.input-wrapper input:focus {
    outline: none;
    border-color: rgba(147, 197, 253, 0.8);
    box-shadow:
        0 0 0 3px rgba(59, 130, 246, 0.2),
        0 4px 12px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset,
        inset 0 2px 0 rgba(255, 255, 255, 0.05);
}

.input-wrapper input::placeholder {
    color: var(--twitter-gray);
    opacity: 0.7;
    font-style: italic;
    transition: opacity 0.3s ease;
}

.input-wrapper input:focus::placeholder {
    opacity: 0.4;
}

/* Input Icons */
.input-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--twitter-gray);
    font-size: 16px;
    transition: all 0.3s ease;
    z-index: 2;
}

.input-wrapper input:focus + .input-icon,
.input-wrapper input:focus ~ .input-icon {
    color: var(--twitter-blue);
    transform: translateY(-50%) scale(1.1);
}

/* Password Toggle Enhancement */
.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--twitter-gray);
    cursor: pointer;
    font-size: 16px;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
    z-index: 2;
}

.password-toggle:hover {
    color: var(--twitter-blue);
    background: rgba(29, 161, 242, 0.1);
    transform: translateY(-50%) scale(1.2);
    box-shadow: 0 0 15px rgba(29, 161, 242, 0.2);
}

/* Form Options */
.form-options {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;
    font-size: 14px;
    color: var(--twitter-gray);
    transition: color 0.3s ease;
}

.checkbox-label:hover {
    color: var(--twitter-dark);
}

.checkbox-label input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: relative;
    height: 20px;
    width: 20px;
    background: var(--input-bg);
    border: 2px solid var(--border-color);
    border-radius: 4px;
    margin-right: 12px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.checkbox-label:hover .checkmark {
    border-color: var(--twitter-blue);
    box-shadow: 0 0 8px rgba(29, 161, 242, 0.2);
}

.checkbox-label input:checked ~ .checkmark {
    background: var(--twitter-blue);
    border-color: var(--twitter-blue);
    box-shadow: 0 0 15px rgba(29, 161, 242, 0.3);
    transform: scale(1.1);
}

.checkmark::after {
    content: '';
    position: absolute;
    display: none;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    transition: all 0.2s ease;
}

.checkbox-label input:checked ~ .checkmark::after {
    display: block;
    animation: checkmarkDraw 0.3s ease;
}

@keyframes checkmarkDraw {
    0% {
        opacity: 0;
        transform: rotate(45deg) scale(0);
    }
    50% {
        opacity: 1;
        transform: rotate(45deg) scale(1.2);
    }
    100% {
        opacity: 1;
        transform: rotate(45deg) scale(1);
    }
}

.checkbox-text {
    font-weight: 500;
    user-select: none;
}

/* Advanced Button Styling */
.login-btn {
    width: 100%;
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #1A91DA 30%, var(--twitter-blue) 70%, #1A91DA 100%);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
        0 6px 20px rgba(29, 161, 242, 0.3),
        0 0 40px rgba(29, 161, 242, 0.1),
        inset 0 1px 0 rgba(255,255,255,0.2);
    position: relative;
    overflow: hidden;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -150%;
    width: 150%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(255,255,255,0.4) 30%,
        rgba(255,255,255,0.6) 50%,
        rgba(255,255,255,0.4) 70%,
        transparent 100%
    );
    transition: left 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
}

.login-btn:hover::before {
    left: 150%;
}

.login-btn:hover {
    box-shadow:
        0 8px 25px rgba(29, 161, 242, 0.4),
        0 0 40px rgba(29, 161, 242, 0.2),
        inset 0 2px 0 rgba(255,255,255,0.3);
}



.btn-text {
    position: relative;
    z-index: 2;
}

.btn-loader {
    position: relative;
    z-index: 2;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced Error Messages */
.error-message {
    background: linear-gradient(135deg, rgba(244, 33, 46, 0.1) 0%, rgba(244, 33, 46, 0.05) 100%);
    border: 1px solid rgba(244, 33, 46, 0.3);
    border-radius: 8px;
    padding: 10px 14px;
    margin-top: 8px;
    font-size: 13px;
    color: #ff6b6b;
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: errorShake 0.5s ease-out;
    position: relative;
    overflow: hidden;
}

.error-message::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #ff4757, #ff3838);
    border-radius: 2px 0 0 2px;
}

@keyframes errorShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Advanced Focus States */
.form-group:focus-within label {
    color: var(--twitter-blue);
    transform: translateY(-2px);
    text-shadow: 0 0 8px rgba(29, 161, 242, 0.3);
}

/* Loading State Enhancement */
.login-form.submitting .btn-text {
    opacity: 0;
}

.login-form.submitting .btn-loader {
    display: block;
}

.login-form.submitting .login-btn {
    pointer-events: none;
    opacity: 0.8;
}

/* Success Animation */
.login-form.success .login-btn {
    background: linear-gradient(135deg, #00BA7C 0%, #00C46A 100%);
    box-shadow:
        0 6px 20px rgba(0, 186, 124, 0.3),
        0 0 40px rgba(0, 186, 124, 0.2);
}

.login-form.success .btn-text::after {
    content: ' ✓';
    animation: successCheck 0.6s ease;
}

@keyframes successCheck {
    0% {
        opacity: 0;
        transform: scale(0) rotate(-180deg);
    }
    50% {
        opacity: 1;
        transform: scale(1.2) rotate(0deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

/* Enhanced Accessibility */
@media (prefers-reduced-motion: reduce) {
    .auth-card input,
    .auth-card button,
    .login-btn,
    .password-toggle,
    .checkmark,
    .error-message {
        transition: none !important;
        animation: none !important;
    }
}

/* High contrast mode enhancements */
@media (prefers-contrast: high) {
    .input-wrapper input {
        border-width: 3px;
    }

    .login-btn {
        border: 2px solid white;
    }

    .checkmark {
        border-width: 3px;
    }
}

/* Advanced responsive enhancements */
@media (min-width: 768px) {
    .form-group {
        margin-bottom: 28px;
    }

    .input-wrapper input {
        padding: 20px 55px 20px 55px;
        font-size: 18px;
    }

.input-icon {
    right: 18px;
    font-size: 18px;
}

    .password-toggle {
        right: 18px;
        font-size: 18px;
        padding: 10px;
    }

    .login-btn {
        padding: 22px 28px;
        font-size: 18px;
        letter-spacing: 1.5px;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .input-wrapper input:focus {
        transform: none;
        box-shadow: 0 0 0 3px var(--twitter-blue);
    }

    .login-btn:hover {
        transform: none;
    }

    .password-toggle:hover {
        transform: translateY(-50%);
        background: none;
    }
}

/* Apply performance optimizations to ALL devices */
.auth-card,
.login-btn,
.input-wrapper input,
.error-message {
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}

.auth-card {
    background: rgba(0, 0, 0, 0.6) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.auth-card::before,
.auth-card::after {
    display: none !important;
}

/* Disable all animations for performance */
.login-btn,
.input-wrapper input,
.password-toggle,
.btn-glow,
.btn-particles,
.error-message,
.login-title {
    animation: none !important;
    transition: none !important;
}

.login-btn:hover {
    transform: none !important;
    box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
}

.input-wrapper input:focus {
    transform: none !important;
    box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.3) !important;
}

.login-title::before,
.login-title::after {
    display: none !important;
}

/* Disable particle effects */
.btn-particles {
    display: none !important;
}

/* Simplify button */
.login-btn {
    background: var(--twitter-blue) !important;
    box-shadow: 0 4px 15px rgba(29, 161, 242, 0.3) !important;
}

.btn-glow {
    display: none !important;
}

/* Disable shimmer effects */
.login-btn::before {
    display: none !important;
}

/* Simplify password toggle */
.password-toggle:hover {
    transform: translateY(-50%) !important;
    background: none !important;
    box-shadow: none !important;
}

/* Disable focus glow effects */
.input-wrapper input:focus + .input-icon,
.input-wrapper input:focus ~ .input-icon {
    transform: translateY(-50%) !important;
}

/* Disable validation animations */
.auth-card input:valid:focus,
.auth-card input:invalid:not(:placeholder-shown):focus {
    animation: none !important;
    box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.3) !important;
}

.auth-card input:invalid:not(:placeholder-shown) {
    animation: none !important;
}

/* Ensure username field icon is visible and positioned correctly */
.input-wrapper:has(#username) .input-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: absolute !important;
    right: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: var(--twitter-gray) !important;
    font-size: 16px !important;
    z-index: 2 !important;
    opacity: 1 !important;
    visibility: visible !important;
    width: 20px !important;
    height: 20px !important;
    pointer-events: none !important;
}

/* Make password toggle buttons completely static and properly contained */
.auth-card button[type="button"] {
    position: absolute !important;
    right: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: none !important;
    border: none !important;
    color: var(--twitter-gray) !important;
    cursor: pointer !important;
    padding: 6px !important;
    margin: 0 !important;
    width: 24px !important;
    height: 24px !important;
    outline: none !important;
    box-shadow: none !important;
    transition: none !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 3 !important;
}

.auth-card button[type="button"]:focus,
.auth-card button[type="button"]:active,
.auth-card button[type="button"]:hover {
    position: absolute !important;
    right: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: none !important;
    border: none !important;
    color: var(--twitter-gray) !important;
    cursor: pointer !important;
    padding: 6px !important;
    margin: 0 !important;
    width: 24px !important;
    height: 24px !important;
    outline: none !important;
    box-shadow: none !important;
    transition: none !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 3 !important;
}

/* Ensure input wrapper properly contains the button */
.input-wrapper {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
}

/* Adjust input padding for proper button containment */
.input-wrapper:has(button[type="button"]) input {
    padding-right: 40px !important;
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
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fas fa-eye';
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthIndicator = document.getElementById('password-strength');
    const strengthText = document.getElementById('password-strength-text');

    // If password field is empty, hide the strength indicator
    if (password.length === 0) {
        strengthIndicator.className = '';
        strengthIndicator.style.width = '0%';
        strengthText.textContent = '';
        return;
    }

    let strength = 0;

    if (password.length >= 8) {
        strength += 1;
    }

    if (/[a-z]/.test(password)) {
        strength += 1;
    }

    if (/[A-Z]/.test(password)) {
        strength += 1;
    }

    if (/\d/.test(password)) {
        strength += 1;
    }

    if (/[^A-Za-z0-9]/.test(password)) {
        strength += 1;
    }

    let strengthClass = '';
    let strengthLabel = '';

    switch(strength) {
        case 0:
        case 1:
            strengthClass = 'weak';
            strengthLabel = 'Very Weak';
            break;
        case 2:
            strengthClass = 'weak';
            strengthLabel = 'Weak';
            break;
        case 3:
            strengthClass = 'medium';
            strengthLabel = 'Medium';
            break;
        case 4:
            strengthClass = 'strong';
            strengthLabel = 'Strong';
            break;
        case 5:
            strengthClass = 'very-strong';
            strengthLabel = 'Very Strong';
            break;
    }

    strengthIndicator.className = strengthClass;
    strengthText.className = strengthClass;
    strengthText.textContent = strengthLabel;
}

let usernameCheckTimeout = null;
let currentCheckRequest = null;

function checkUsernameAvailability(username) {
    const statusDiv = document.getElementById('username-status');

    if (!username) {
        statusDiv.textContent = '';
        statusDiv.className = '';
        return;
    }

    // Define reserved usernames (same list as server-side)
    const reservedUsernames = [
        // Admin and system related
        'admin', 'administrator', 'root', 'system', 'sysadmin',
        'moderator', 'mod', 'staff', 'support', 'help',
        'bot', 'robot', 'api', 'service',

        // Laravel/social platform related
        'laravel', 'social', 'twitter', 'x', 'meta', 'facebook',
        'instagram', 'linkedin', 'youtube', 'tiktok',

        // Common variations
        'admin1', 'admin123', 'administrator1', 'root1',
        'mod1', 'moderator1', 'staff1', 'support1',

        // Application specific
        'app', 'application', 'platform', 'site', 'website',
        'company', 'official', 'team', 'dev', 'developer',

        // Common admin variations
        'superuser', 'superadmin', 'master', 'owner',
        'ceo', 'founder', 'manager', 'director'
    ];

    // Check if username is reserved
    if (reservedUsernames.includes(username.toLowerCase())) {
        statusDiv.textContent = 'This username is reserved and cannot be used';
        statusDiv.className = 'invalid';
        return;
    }

    if (username.length < 3) {
        statusDiv.textContent = 'Username must be at least 3 characters';
        statusDiv.className = 'warning';
        return;
    }

    // Check for invalid characters (client-side validation)
    if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        statusDiv.textContent = 'Username can only contain letters, numbers, underscores, and hyphens';
        statusDiv.className = 'invalid';
        return;
    }

    // Cancel previous request if still pending
    if (currentCheckRequest) {
        currentCheckRequest.abort();
    }

    // Create new AbortController for this request
    const controller = new AbortController();
    currentCheckRequest = controller;

    // Show checking status
    statusDiv.textContent = 'Checking availability...';
    statusDiv.className = 'checking';

    fetch(`/api/check-username/${encodeURIComponent(username)}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        signal: controller.signal
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.available) {
            statusDiv.textContent = 'Username is available';
            statusDiv.className = 'available';
        } else {
            statusDiv.textContent = 'Username is already taken';
            statusDiv.className = 'taken';
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            // Request was cancelled, ignore
            return;
        }

        console.error('Error checking username:', error);
        statusDiv.textContent = 'Error checking username';
        statusDiv.className = 'error';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Detect if device is mobile for performance optimizations
    const isMobile = window.innerWidth <= 767 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    // Password strength checker with mobile optimization
    const passwordInput = document.getElementById('password');
    let passwordStrengthTimeout = null;

    if (passwordInput) {
        // Use different debounce timing for mobile vs desktop
        const debounceDelay = isMobile ? 300 : 100; // Faster on desktop, slower on mobile

        passwordInput.addEventListener('input', function() {
            // Clear previous timeout
            if (passwordStrengthTimeout) {
                clearTimeout(passwordStrengthTimeout);
            }

            // Debounce password strength checking
            passwordStrengthTimeout = setTimeout(() => {
                checkPasswordStrength();
            }, debounceDelay);
        });
    }

    // Username availability checker with mobile optimization
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        // Use different debounce timing for mobile vs desktop
        const usernameDebounceDelay = isMobile ? 1000 : 500; // Slower on mobile to reduce API calls

        usernameInput.addEventListener('input', function() {
            const username = this.value.trim();

            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }

            // Skip API calls for very short usernames on mobile
            if (isMobile && username.length < 3) {
                const statusDiv = document.getElementById('username-status');
                statusDiv.textContent = '';
                statusDiv.className = '';
                return;
            }

            // Debounce the API call
            usernameCheckTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, usernameDebounceDelay);
        });
    }

    // Form submission validation
    const registerForm = document.querySelector('form');
    const registerBtn = document.getElementById('register-btn');

    if (registerForm && registerBtn) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;

            // Check password strength
            let strength = 0;
            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/\d/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;

            // Require at least "Medium" strength (3 criteria met)
            if (strength < 3) {
                e.preventDefault();
                alert('Password is too weak. Please use a stronger password with uppercase, lowercase, numbers, and/or special characters.');
                document.getElementById('password').focus();
                return false;
            }

            // Check password confirmation
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Passwords do not match.');
                document.getElementById('password_confirmation').focus();
                return false;
            }

            // Disable button and show loading
            registerBtn.disabled = true;
            registerBtn.querySelector('.btn-text').textContent = 'Creating Account...';
            registerBtn.querySelector('.btn-loader').style.display = 'block';
        });
    }
});
</script>

@endsection
