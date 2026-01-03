@extends('layouts.app')

@section('content')
<div style="max-width: 400px; margin: 50px auto; padding: 20px;">
    <div style="background: var(--card-bg); padding: 30px; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.3); border: 2px solid var(--border-color);">
        <h2 style="text-align: center; margin-bottom: 30px; color: var(--twitter-dark);">Change Password</h2>
        <form method="POST" action="{{ route('password.change') }}">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="current_password" style="color: var(--twitter-dark); font-weight: 500;">Current Password</label>
                <div class="password-input-container">
                    <input type="password" name="current_password" id="current_password" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('current_password')" aria-label="Toggle current password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('current_password') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password" style="color: var(--twitter-dark); font-weight: 500;">New Password</label>
                <div class="password-input-container">
                    <input type="password" name="password" id="password" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')" aria-label="Toggle new password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="password-strength" class="password-strength"></div>
                <div id="password-strength-text" style="color: var(--twitter-gray); font-size: 14px; margin-top: 8px;"></div>
                @error('password') <div style="color: var(--error-color); font-size: 14px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="password_confirmation" style="color: var(--twitter-dark); font-weight: 500;">Confirm New Password</label>
                <div class="password-input-container">
                    <input type="password" name="password_confirmation" id="password_confirmation" required style="width: 100%; padding: 14px 50px 14px 18px; border: 2px solid var(--border-color); border-radius: 12px; background: var(--input-bg); color: var(--twitter-dark); font-size: 16px; transition: all 0.3s ease;" onfocus="this.style.borderColor='var(--twitter-blue)'; this.style.boxShadow='0 0 0 4px rgba(29, 161, 242, 0.15)'; this.style.transform='translateY(-1px)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password_confirmation')" aria-label="Toggle confirm password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" style="width: 100%; padding: 14px; background: var(--twitter-blue); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);" onmouseover="this.style.background='#1A91DA'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(29, 161, 242, 0.4)';" onmouseout="this.style.background='var(--twitter-blue)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(29, 161, 242, 0.3)';">Change Password</button>
        </form>
        @if(session('status'))
        <div style="color: var(--success-color); margin-top: 15px; text-align: center; font-weight: 500;">{{ session('status') }}</div>
        @endif
    </div>
</div>
@endsection

<script>
function checkPasswordStrength() {
    console.log('Password strength check triggered');
    const password = document.getElementById('password').value;
    const strengthIndicator = document.getElementById('password-strength');
    const strengthText = document.getElementById('password-strength-text');

    if (!strengthIndicator || !strengthText) {
        console.error('Strength indicator elements not found');
        return;
    }

    let strength = 0;
    let feedback = [];

    if (password.length >= 8) {
        strength += 1;
    } else {
        feedback.push('At least 8 characters');
    }

    if (/[a-z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Lowercase letter');
    }

    if (/[A-Z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Uppercase letter');
    }

    if (/\d/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Number');
    }

    if (/[^A-Za-z0-9]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('Special character');
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

    strengthIndicator.className = 'password-strength ' + strengthClass;
    strengthText.textContent = strengthLabel;

    if (feedback.length > 0 && strength < 4) {
        strengthText.textContent += ' - Add: ' + feedback.slice(0, 2).join(', ');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Attaching password strength checker');
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
        console.log('Password strength checker attached successfully');
    } else {
        console.error('Password input field not found');
    }
});

// Password visibility toggle function
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Show password');
    }
}
</script>

<style>
.password-strength {
    height: 5px;
    margin-top: 5px;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.password-strength.weak {
    background-color: #ff4444;
    width: 25%;
}

.password-strength.medium {
    background-color: #ffaa00;
    width: 50%;
}

.password-strength.strong {
    background-color: #00aa00;
    width: 75%;
}

.password-strength.very-strong {
    background-color: #00dd00;
    width: 100%;
}

#password-strength-text {
    font-size: 12px;
    margin-top: 5px;
    color: #666;
}

/* Password input container styles */
.password-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-container input {
    flex: 1;
    padding-right: 45px; /* Make room for the toggle button */
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: color 0.2s ease, background-color 0.2s ease;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
}

.password-toggle:hover {
    color: #333;
    background-color: rgba(0, 0, 0, 0.05);
}

.password-toggle:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}
</style>
