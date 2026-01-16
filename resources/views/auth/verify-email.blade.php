@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2 class="login-title">Verify Your Email Address</h2>

        <div class="verification-content" style="text-align: center; padding: 20px;">
            <div style="margin-bottom: 20px;">
                <i class="fas fa-envelope-open-text" style="font-size: 64px; color: var(--twitter-blue); margin-bottom: 20px;"></i>
            </div>

            <h3 style="margin-bottom: 15px; color: var(--twitter-dark);">Enter Verification Code</h3>

            <p style="color: var(--twitter-gray); margin-bottom: 20px; line-height: 1.6;">
                We've sent a 6-digit verification code to <strong>{{ auth()->user()->email }}</strong>.
                Please enter the code below to verify your account.
            </p>

            <form method="POST" action="{{ route('verification.verify-code') }}" style="max-width: 300px; margin: 0 auto;">
                @csrf

                <div style="margin-bottom: 20px;">
                    <label for="code" style="display: block; margin-bottom: 8px; color: var(--twitter-dark); font-weight: 600;">Verification Code</label>
                    <input type="text" id="code" name="code" maxlength="6"
                           style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; font-size: 18px; text-align: center; letter-spacing: 2px; font-weight: bold; transition: border-color 0.3s ease;"
                           placeholder="000000" required
                           onfocus="this.style.borderColor='var(--twitter-blue)';"
                           onblur="this.style.borderColor='var(--border-color)';">
                    @error('code') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <form method="POST" action="{{ route('verification.send') }}" style="display: inline-block;">
                    @csrf
                    <button type="submit" style="
                        background: rgba(29, 161, 242, 0.1);
                        color: var(--twitter-blue);
                        border: 1px solid rgba(29, 161, 242, 0.3);
                        padding: 10px 20px;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                    " onmouseover="this.style.background='rgba(29, 161, 242, 0.2)'; this.style.borderColor='var(--twitter-blue)';" onmouseout="this.style.background='rgba(29, 161, 242, 0.1)'; this.style.borderColor='rgba(29, 161, 242, 0.3)';">
                        <i class="fas fa-envelope" style="margin-right: 6px;"></i>
                        Resend Code
                    </button>
                </form>
            </div>

            <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.2); border-radius: 8px; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; color: var(--twitter-dark); font-size: 14px;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: #ffc107;"></i>
                    <strong>Important:</strong> This code expires in 10 minutes. If you don't verify, you can register again with the same email.
                </p>
            </div>

            @if (session('message'))
                <div style="
                    margin-top: 15px;
                    padding: 12px 16px;
                    background: rgba(40, 167, 69, 0.1);
                    border: 1px solid rgba(40, 167, 69, 0.2);
                    border-radius: 6px;
                    color: #28a745;
                    font-weight: 500;
                ">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                    {{ session('message') }}
                </div>
            @endif

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <p style="color: var(--twitter-gray); font-size: 14px; margin-bottom: 10px;">
                    Wrong email address?
                </p>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="
                        background: transparent;
                        color: var(--error-color);
                        border: 1px solid var(--error-color);
                        padding: 8px 16px;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    " onmouseover="this.style.background='var(--error-color)'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='var(--error-color)';">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout & Try Again
                    </button>
                </form>
            </div>
        </div>
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

.auth-card {
    background: var(--card-bg);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    border: 2px solid var(--border-color);
    width: 100%;
    max-width: 500px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
}

.login-title {
    text-align: center;
    margin-bottom: 30px;
    color: var(--twitter-dark);
    font-weight: 300;
    font-size: 32px;
    letter-spacing: 2px;
    text-transform: uppercase;
    position: relative;
    z-index: 10;
}

.verification-content h3 {
    color: var(--twitter-dark);
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 15px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .auth-container {
        padding: 15px;
        min-height: calc(100vh - 40px);
    }

    .auth-card {
        padding: 25px;
        max-width: 100%;
    }

    .login-title {
        font-size: 24px;
        margin-bottom: 25px;
    }

    .verification-content h3 {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .auth-card {
        padding: 20px;
    }

    .login-title {
        font-size: 20px;
        letter-spacing: 1px;
    }
}

#code {
    font-family: 'Courier New', monospace;
}

/* Auto-fill animation for code input */
@keyframes codeInput {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');

    if (codeInput) {
        // Auto-focus the input field
        codeInput.focus();

        // Only allow numeric input
        codeInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');

            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                // Add a small delay for better UX
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });

        // Prevent pasting non-numeric content
        codeInput.addEventListener('paste', function(e) {
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^\d+$/.test(paste)) {
                e.preventDefault();
            }
        });

        // Handle backspace and navigation keys properly
        codeInput.addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, escape, enter, and arrow keys
            if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(e.keyCode) ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                (e.ctrlKey && [65, 67, 86, 88, 90].includes(e.keyCode))) {
                return;
            }

            // Prevent input of non-numeric characters
            if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection
