@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="auth-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h1 class="title">Verify Your Email</h1>
        <p class="subtitle" id="instruction-text">Please enter the verification code sent to your email address to verify your account.</p>

        <!-- Send Code Section -->
        <div class="send-code-section" id="sendCodeSection" @if(session('message') && (str_contains(session('message'), 'sent') || str_contains(session('message'), 'code'))) style="display:none" @endif>
            <form method="POST" action="{{ route('verification.send') }}" id="sendCodeForm">
                @csrf
                <button type="submit" class="submit" id="sendCodeBtn">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>
        </div>

        <!-- Verification Code Form -->
        <form class="verification-code-form @if(session('message') && (str_contains(session('message'), 'sent') || str_contains(session('message'), 'code'))) active @endif" method="POST" action="{{ route('verification.verify-code') }}" id="verifyForm">
            @csrf
            <div class="code-inputs">
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required autofocus>
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                <input type="text" name="code[]" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
            </div>

            <input type="hidden" name="code" id="fullCode">

            <button type="submit" class="submit">
                <i class="fas fa-check-circle"></i> Verify Email
            </button>
        </form>

        <div class="resend-section @if(session('message') && (str_contains(session('message'), 'sent') || str_contains(session('message'), 'code'))) active @endif" id="resendSection">
            <p>Didn't receive the code?</p>
            <form method="POST" action="{{ route('verification.send') }}" style="display: inline;">
                @csrf
                <button type="submit" class="resend-btn" id="resendBtn">Resend Code</button>
            </form>
            <div class="timer">Resend available in <span id="countdown">60</span>s</div>
        </div>

        <p class="footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </p>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
    </div>
</div>

<script>
    // Toast notification functions
    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            left: 20px;
            z-index: 9999;
            padding: 16px 20px;
            border-radius: 10px;
            background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#8b5cf6'};
            color: white;
            font-weight: 600;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
            font-family: 'Courier New', Courier, monospace;
            text-align: center;
            max-width: 400px;
            margin: 0 auto;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Show toast on page load if there's a session message
    @if(session('message'))
        showToast('{{ session('message') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    @if($errors->has('code'))
        showToast('{{ $errors->first('code') }}', 'error');
    @endif

    // Check if user is already verified (passed from backend)
    const userAlreadyVerified = @if(auth()->check() && auth()->user()->hasVerifiedEmail()) true @else false @endif;

    // Handle send code form submission
    document.getElementById('sendCodeForm')?.addEventListener('submit', (e) => {
        if (userAlreadyVerified) {
            e.preventDefault();
            showToast('Your account is already verified!', 'info');
            return false;
        }
    });

    // Auto-focus for code inputs
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');

    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Submit form with combined code - validate empty inputs
    document.getElementById('verifyForm').addEventListener('submit', (e) => {
        // Get all input values
        const inputValues = Array.from(inputs).map(input => input.value);
        const code = inputValues.join('');
        
        // Check if any input is empty
        const hasEmptyInput = inputValues.some(val => val === '' || val === undefined || val === null);
        
        // Check if code is empty or incomplete
        if (hasEmptyInput || code.length < 6) {
            e.preventDefault();
            showToast('Please enter the complete 6-digit verification code', 'error');
            return false;
        }
        
        // Check if all characters are digits
        if (!/^\d{6}$/.test(code)) {
            e.preventDefault();
            showToast('Verification code must contain only numbers', 'error');
            return false;
        }
        
        fullCodeInput.value = code;
    });

    // Start countdown if resend section is visible
    @if(session('message') && (str_contains(session('message'), 'sent') || str_contains(session('message'), 'code')))
        startCountdown();
    @endif

    // Countdown timer for resend
    function startCountdown() {
        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');
        
        countdownEl.textContent = seconds;
        resendBtn.disabled = true;

        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
            }
        }, 1000);
    }
</script>

<style>
.login-page {min-height: calc(100vh - 64px);display: flex;align-items: center;justify-content: center;padding: 20px;background: var(--bg);font-family: 'Courier New', Courier, monospace;}
.login-card {width: 100%;max-width: 400px;background: var(--surface);border: 1px solid var(--border);border-radius: 16px;padding: 32px 28px;font-family: 'Courier New', Courier, monospace;text-align: center;}
.auth-icon {width: 80px;height: 80px;background: linear-gradient(135deg, var(--primary), var(--secondary));border-radius: 16px;display: flex;align-items: center;justify-content: center;margin: 0 auto 24px;font-size: 36px;color: white;}
.title {font-size: 24px;font-weight: 700;color: var(--text);margin: 0 0 12px 0;text-align: center;font-family: 'Courier New', Courier, monospace;}
.subtitle {font-size: 14px;color: var(--text-muted);margin: 0 0 24px 0;text-align: center;font-family: 'Courier New', Courier, monospace;line-height: 1.5;}
.send-code-section {margin-bottom: 24px;}
.code-inputs {display: flex;gap: 8px;justify-content: center;margin-bottom: 24px;flex-wrap: wrap;}
.code-input {width: 48px;height: 56px;font-size: 24px;font-weight: 700;text-align: center;border: 2px solid var(--border);border-radius: 10px;background: var(--bg);color: var(--text);transition: all 0.2s;font-family: 'Courier New', Courier, monospace;}
.code-input:focus {outline: none;border-color: var(--primary);box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);}
.verification-code-form {display: none;}
.verification-code-form.active {display: block;}
.submit {width: 100%;padding: 14px;font-size: 15px;font-weight: 600;color: white;background: linear-gradient(135deg, var(--primary), var(--secondary));border: none;border-radius: 10px;cursor: pointer;transition: 0.2s;}
.submit:hover {transform: translateY(-1px);box-shadow: 0 6px 20px rgba(139,92,246,0.3);}
.resend-section {display: none;margin-top: 24px;padding-top: 20px;border-top: 1px solid var(--border);}
.resend-section.active {display: block;}
.resend-section p {color: var(--text-muted);font-size: 13px;margin: 0 0 12px 0;font-family: 'Courier New', Courier, monospace;}
.resend-btn {background: none;border: none;color: var(--primary);font-size: 13px;font-weight: 600;cursor: pointer;text-decoration: underline;padding: 0;font-family: 'Courier New', Courier, monospace;}
.resend-btn:disabled {color: var(--text-muted);cursor: not-allowed;text-decoration: none;}
.timer {font-size: 12px;color: var(--text-muted);margin-top: 8px;}
.timer span {font-weight: 600;color: var(--primary);}
.footer {text-align: center;margin-top: 24px;padding-top: 20px;border-top: 1px solid var(--border);font-size: 14px;}
.footer a {color: var(--primary);text-decoration: none;font-weight: 600;}
.footer a:hover {text-decoration: underline;}

/* Responsive styles for mobile devices */
@media (max-width: 480px) {
    .login-card {
        padding: 24px 20px;
        margin: 10px;
        max-width: 100%;
    }
    
    .auth-icon {
        width: 70px;
        height: 70px;
        font-size: 32px;
    }
    
    .title {
        font-size: 20px;
    }
    
    .subtitle {
        font-size: 13px;
    }
    
    .code-inputs {
        gap: 6px;
    }
    
    .code-input {
        width: 42px;
        height: 50px;
        font-size: 20px;
    }
    
    .submit {
        padding: 12px;
        font-size: 14px;
    }
    
    .resend-section p {
        font-size: 12px;
    }
    
    .resend-btn {
        font-size: 12px;
    }
    
    .timer {
        font-size: 11px;
    }
    
    .footer {
        font-size: 13px;
    }
}

/* Extra small devices */
@media (max-width: 360px) {
    .login-card {
        padding: 20px 16px;
    }
    
    .code-inputs {
        gap: 4px;
    }
    
    .code-input {
        width: 38px;
        height: 46px;
        font-size: 18px;
        border-radius: 8px;
    }
    
    .auth-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
    
    .title {
        font-size: 18px;
    }
    
    .subtitle {
        font-size: 12px;
    }
}

/* Slide animations for toast */
@keyframes slideIn {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-100%);
        opacity: 0;
    }
}
</style>
@endsection
