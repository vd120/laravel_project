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
                </div>
                <div id="username-status"></div>
                @error('username') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Enter your email address">
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
    box-shadow:
        0 4px 16px rgba(0,0,0,0.3),
        0 0 0 1px rgba(255,255,255,0.05) inset,
        0 1px 0 rgba(255,255,255,0.1) inset;
    border: 2px solid var(--border-color);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
}

.auth-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--twitter-blue), var(--twitter-blue), transparent);
    opacity: 0.8;
    box-shadow: 0 0 10px var(--twitter-blue);
}

.auth-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), rgba(255,255,255,0.05), transparent);
    opacity: 0.6;
}

/* Add powerful border animation */
.auth-card {
    animation: cardGlow 4s ease-in-out infinite alternate;
}

@keyframes cardGlow {
    0% {
        box-shadow:
            0 4px 16px rgba(0,0,0,0.3),
            0 0 0 1px rgba(255,255,255,0.05) inset,
            0 1px 0 rgba(255,255,255,0.1) inset;
    }
    100% {
        box-shadow:
            0 4px 16px rgba(0,0,0,0.3),
            0 0 20px rgba(29, 161, 242, 0.1),
            0 0 0 1px rgba(255,255,255,0.05) inset,
            0 1px 0 rgba(255,255,255,0.1) inset;
    }
}

/* Enhanced form elements with powerful effects */
.auth-card input[type="email"],
.auth-card input[type="password"],
.auth-card input[type="text"] {
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: linear-gradient(145deg, var(--input-bg) 0%, rgba(255,255,255,0.02) 100%);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.auth-card input[type="email"]:focus,
.auth-card input[type="password"]:focus,
.auth-card input[type="text"]:focus {
    transform: translateY(-3px) scale(1.02);
    border-color: var(--twitter-blue);
    background: linear-gradient(145deg, var(--input-bg) 0%, rgba(29, 161, 242, 0.03) 100%);
    box-shadow:
        0 0 0 4px rgba(29, 161, 242, 0.15),
        0 6px 20px rgba(29, 161, 242, 0.2),
        0 0 40px rgba(29, 161, 242, 0.1),
        inset 0 2px 0 rgba(255,255,255,0.15),
        inset 0 1px 0 rgba(255,255,255,0.1);
    animation: inputGlow 0.3s ease-out;
}

@keyframes inputGlow {
    0% {
        box-shadow:
            0 0 0 0 rgba(29, 161, 242, 0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
    }
    100% {
        box-shadow:
            0 0 0 4px rgba(29, 161, 242, 0.15),
            0 6px 20px rgba(29, 161, 242, 0.2),
            0 0 40px rgba(29, 161, 242, 0.1),
            inset 0 2px 0 rgba(255,255,255,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
    }
}

/* Advanced input validation states */
.auth-card input:valid {
    border-color: rgba(0, 186, 124, 0.5);
}

.auth-card input:valid:focus {
    border-color: #00BA7C;
    box-shadow:
        0 0 0 4px rgba(0, 186, 124, 0.15),
        0 6px 20px rgba(0, 186, 124, 0.2),
        inset 0 2px 0 rgba(255,255,255,0.15);
}

.auth-card input:invalid:not(:placeholder-shown) {
    border-color: rgba(244, 33, 46, 0.5);
    animation: inputError 0.3s ease-out;
}

.auth-card input:invalid:not(:placeholder-shown):focus {
    border-color: #F4212E;
    box-shadow:
        0 0 0 4px rgba(244, 33, 46, 0.15),
        0 6px 20px rgba(244, 33, 46, 0.2),
        inset 0 2px 0 rgba(255,255,255,0.15);
}

@keyframes inputError {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
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
    transform: translateY(-6px) scale(1.03);
    box-shadow:
        0 16px 40px rgba(29, 161, 242, 0.5),
        0 0 80px rgba(29, 161, 242, 0.3),
        0 0 120px rgba(29, 161, 242, 0.1),
        inset 0 3px 0 rgba(255,255,255,0.3),
        inset 0 1px 0 rgba(255,255,255,0.2);
    background-position: right center;
    animation: btnGlow 0.6s ease-out;
}

.login-btn:active {
    transform: translateY(-3px) scale(0.97);
    transition: all 0.1s ease;
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

/* Add floating animation to the card on larger screens */
@media (min-width: 768px) {
    .auth-card {
        animation: cardGlow 4s ease-in-out infinite alternate, cardFloat 6s ease-in-out infinite;
    }

    @keyframes cardFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
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
    /* Powerful neon glow effects */
    text-shadow:
        0 0 5px var(--twitter-blue),
        0 0 10px var(--twitter-blue),
        0 0 15px var(--twitter-blue),
        0 0 20px var(--twitter-blue),
        0 0 35px var(--twitter-blue),
        0 0 40px var(--twitter-blue),
        0 0 50px var(--twitter-blue),
        0 0 75px var(--twitter-blue);
    animation: neonFlicker 2s ease-in-out infinite alternate, neonGlow 4s ease-in-out infinite;
}

.login-title::before {
    content: 'REGISTER';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    color: var(--twitter-blue);
    z-index: -1;
    opacity: 0.8;
    animation: neonPulse 3s ease-in-out infinite;
}

.login-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 140px;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--twitter-blue), var(--neon-lime-bright), var(--twitter-blue), transparent);
    border-radius: 2px;
    box-shadow:
        0 0 10px var(--twitter-blue),
        0 0 20px var(--neon-lime-bright),
        0 0 30px var(--twitter-blue);
    animation: underlineGlow 2.5s ease-in-out infinite alternate;
}

@keyframes neonFlicker {
    0%, 100% {
        opacity: 1;
        text-shadow:
            0 0 5px var(--twitter-blue),
            0 0 10px var(--twitter-blue),
            0 0 15px var(--twitter-blue),
            0 0 20px var(--twitter-blue),
            0 0 35px var(--twitter-blue),
            0 0 40px var(--twitter-blue),
            0 0 50px var(--twitter-blue),
            0 0 75px var(--twitter-blue);
    }
    2%, 4%, 6%, 8%, 10%, 12%, 14%, 16%, 18% {
        opacity: 0.3;
        text-shadow:
            0 0 1px var(--twitter-blue),
            0 0 2px var(--twitter-blue);
    }
    3%, 7%, 11%, 15%, 19% {
        opacity: 0.6;
        text-shadow:
            0 0 2px var(--twitter-blue),
            0 0 4px var(--twitter-blue),
            0 0 6px var(--twitter-blue);
    }
    5%, 9%, 13%, 17% {
        opacity: 0.8;
        text-shadow:
            0 0 3px var(--twitter-blue),
            0 0 6px var(--twitter-blue),
            0 0 9px var(--twitter-blue),
            0 0 12px var(--twitter-blue);
    }
    20%, 40%, 60%, 80% {
        opacity: 0.9;
        text-shadow:
            0 0 4px var(--twitter-blue),
            0 0 8px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 16px var(--twitter-blue),
            0 0 20px var(--twitter-blue);
    }
    25%, 35%, 45%, 55%, 65%, 75%, 85%, 95% {
        opacity: 0.95;
        text-shadow:
            0 0 3px var(--twitter-blue),
            0 0 6px var(--twitter-blue),
            0 0 9px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 18px var(--twitter-blue),
            0 0 24px var(--twitter-blue);
    }
    30%, 50%, 70%, 90% {
        opacity: 1;
        text-shadow:
            0 0 4px var(--twitter-blue),
            0 0 8px var(--twitter-blue),
            0 0 12px var(--twitter-blue),
            0 0 16px var(--twitter-blue),
            0 0 24px var(--twitter-blue),
            0 0 32px var(--twitter-blue),
            0 0 40px var(--twitter-blue);
    }
}

@keyframes neonGlow {
    0% {
        filter: brightness(1) contrast(1.2);
    }
    50% {
        filter: brightness(1.1) contrast(1.3);
    }
    100% {
        filter: brightness(1) contrast(1.2);
    }
}

@keyframes neonPulse {
    0%, 100% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.02);
    }
}

@keyframes underlineGlow {
    0% {
        opacity: 1;
        box-shadow:
            0 0 10px var(--twitter-blue),
            0 0 20px var(--neon-lime-bright);
    }
    100% {
        opacity: 0.8;
        box-shadow:
            0 0 15px var(--twitter-blue),
            0 0 30px var(--neon-lime-bright),
            0 0 45px var(--twitter-blue);
    }
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
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }

    // Username availability checker
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const username = this.value.trim();

            // Clear previous timeout
            if (usernameCheckTimeout) {
                clearTimeout(usernameCheckTimeout);
            }

            // Debounce the API call
            usernameCheckTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, 500);
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
