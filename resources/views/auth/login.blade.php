@extends('layouts.app')

@section('content')
@if(session('suspended'))
<script>
    window.location.href = '{{ route("auth.suspended") }}';
</script>
@endif

<div class="auth-container">
    <div class="auth-card">
        <h2 class="login-title">Login</h2>
        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf
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
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                @error('password') <div class="error-message">{{ $message }}</div> @enderror
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1">
                    <span class="checkmark"></span>
                    <span class="checkbox-text">Keep me signed in</span>
                </label>
                <input type="hidden" name="remember" value="0">
            </div>

            <button type="submit" class="login-btn">
                <span class="btn-text">Sign In</span>
                <div class="btn-loader" style="display: none;">
                    <div class="spinner"></div>
                </div>
            </button>
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
.auth-card input[type="password"] {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.auth-card input[type="email"]:focus,
.auth-card input[type="password"]:focus {
    transform: translateY(-2px) scale(1.01);
    box-shadow:
        0 0 0 3px rgba(29, 161, 242, 0.1),
        0 4px 12px rgba(29, 161, 242, 0.15),
        inset 0 1px 0 rgba(255,255,255,0.1);
}

/* Powerful button effects */
.auth-card button[type="submit"] {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #1A91DA 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.auth-card button[type="submit"]:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow:
        0 8px 25px rgba(29, 161, 242, 0.4),
        0 0 40px rgba(29, 161, 242, 0.2),
        inset 0 1px 0 rgba(255,255,255,0.2);
}

.auth-card button[type="submit"]:active {
    transform: translateY(-1px) scale(0.98);
}

/* Add shimmer effect to button */
.auth-card button[type="submit"]::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
}

.auth-card button[type="submit"]:hover::before {
    left: 100%;
}

/* Enhanced checkbox styling */
.auth-card input[type="checkbox"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    border-radius: 4px;
    background: var(--input-bg);
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-right: 12px;
}

.auth-card input[type="checkbox"]:checked {
    background: var(--twitter-blue);
    border-color: var(--twitter-blue);
    box-shadow: 0 0 15px rgba(29, 161, 242, 0.3);
    transform: scale(1.1);
}

.auth-card input[type="checkbox"]:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
    font-weight: bold;
    text-shadow: 0 0 5px rgba(0,0,0,0.5);
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
    content: 'LOGIN';
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
    width: 120px;
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
    color: var(--twitter-dark);
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper input {
    width: 100%;
    padding: 16px 50px 16px 50px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: linear-gradient(145deg, var(--input-bg) 0%, rgba(255,255,255,0.02) 100%);
    color: var(--twitter-dark);
    font-size: 16px;
    font-weight: 400;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.input-wrapper input:focus {
    outline: none;
    border-color: var(--twitter-blue);
    background: linear-gradient(145deg, var(--input-bg) 0%, rgba(29, 161, 242, 0.02) 100%);
    box-shadow:
        0 0 0 4px rgba(29, 161, 242, 0.1),
        0 8px 20px rgba(29, 161, 242, 0.15),
        inset 0 1px 0 rgba(255,255,255,0.1);
    transform: translateY(-2px) scale(1.01);
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
    transform: translateY(-4px) scale(1.02);
    box-shadow:
        0 12px 30px rgba(29, 161, 242, 0.4),
        0 0 60px rgba(29, 161, 242, 0.2),
        0 0 100px rgba(29, 161, 242, 0.1),
        inset 0 2px 0 rgba(255,255,255,0.3);
}

.login-btn:active {
    transform: translateY(-2px) scale(0.98);
    box-shadow:
        0 4px 15px rgba(29, 161, 242, 0.5),
        0 0 30px rgba(29, 161, 242, 0.3),
        inset 0 2px 6px rgba(0,0,0,0.1);
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

/* Performance optimizations for mobile */
@media (max-width: 767px) {
    .login-btn {
        /* Reduce expensive effects on mobile */
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .input-wrapper input {
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .error-message {
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }
}

/* Make password toggle buttons completely static */
.auth-card button[type="button"] {
    position: absolute !important;
    right: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: none !important;
    border: none !important;
    color: var(--twitter-gray) !important;
    cursor: pointer !important;
    padding: 8px !important;
    margin: 0 !important;
    width: auto !important;
    height: auto !important;
    outline: none !important;
    box-shadow: none !important;
    transition: none !important;
}

.auth-card button[type="button"]:focus,
.auth-card button[type="button"]:active,
.auth-card button[type="button"]:hover {
    position: absolute !important;
    right: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: none !important;
    border: none !important;
    color: var(--twitter-gray) !important;
    cursor: pointer !important;
    padding: 8px !important;
    margin: 0 !important;
    width: auto !important;
    height: auto !important;
    outline: none !important;
    box-shadow: none !important;
    transition: none !important;
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
