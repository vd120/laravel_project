@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2 style="text-align: center; margin-bottom: 30px; color: var(--twitter-dark);">Register</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="username" style="color: var(--twitter-dark); font-weight: 500;">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required style="width: 100%; padding: 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                <div id="username-status" style="margin-top: 5px; font-size: 14px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;"></div>
                @error('username') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label for="email" style="color: var(--twitter-dark); font-weight: 500;">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="width: 100%; padding: 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                @error('email') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password" style="color: var(--twitter-dark); font-weight: 500;">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" onclick="togglePassword('password')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--twitter-gray); cursor: pointer; transition: color 0.2s ease;" onmouseover="this.style.color='var(--twitter-blue)';" onmouseout="this.style.color='var(--twitter-gray)';">
                        <i class="fas fa-eye" id="password-eye-icon"></i>
                    </button>
                </div>
                <div id="password-strength" style="height: 6px; background: var(--border-color); border-radius: 3px; margin-top: 8px; transition: all 0.3s;"></div>
                <div id="password-strength-text" style="margin-top: 8px; font-size: 14px;"></div>
                @error('password') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="password_confirmation" style="color: var(--twitter-dark); font-weight: 500;">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" name="password_confirmation" id="password_confirmation" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" onclick="togglePassword('password_confirmation')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--twitter-gray); cursor: pointer; transition: color 0.2s ease;" onmouseover="this.style.color='var(--twitter-blue)';" onmouseout="this.style.color='var(--twitter-gray)';">
                        <i class="fas fa-eye" id="confirm-password-eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" style="width: 100%; padding: 14px; background: var(--twitter-blue); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);" onmouseover="this.style.background='#1A91DA'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(29, 161, 242, 0.4)';" onmouseout="this.style.background='var(--twitter-blue)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(29, 161, 242, 0.3)';">Register</button>
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
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    border: 2px solid var(--border-color);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}

/* Password strength indicator */
#password-strength {
    height: 6px;
    border-radius: 3px;
    margin-top: 8px;
    transition: all 0.3s ease;
    background: #eee;
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

    if (username.length < 3) {
        statusDiv.textContent = 'Username must be at least 3 characters';
        statusDiv.className = 'warning';
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
});
</script>

@endsection
