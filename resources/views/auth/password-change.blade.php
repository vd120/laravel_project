@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="password-page">
    <div class="password-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="card-title">Change Password</h1>
            <p class="card-subtitle">Update your account password to keep your account secure</p>
        </div>

        @if(session('success'))
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.change') }}">
            @csrf

            <!-- Hidden username field for accessibility -->
            <input type="text" name="username" value="{{ auth()->user()->email }}" autocomplete="username" style="display: none;" aria-hidden="true">

            <div class="form-field">
                <label for="current_password">Current Password</label>
                <div class="input-group">
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required autocomplete="current-password">
                    <button type="button" class="toggle-btn" onclick="togglePassword('current_password', 'current-password-eye')">
                        <i class="fas fa-eye" id="current-password-eye"></i>
                    </button>
                </div>
                @error('current_password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-field">
                <label for="password">New Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Enter new password" required autocomplete="new-password" oninput="checkPasswordStrength()">
                    <button type="button" class="toggle-btn" onclick="togglePassword('password', 'password-eye')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </button>
                </div>
                <span class="field-hint">Minimum 8 characters</span>
                
                <!-- Password Strength Indicator -->
                <div class="strength-container" id="strength-container" style="display: none;">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <span class="strength-text" id="strength-text"></span>
                </div>
                
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password" oninput="checkPasswordMatch()">
                    <button type="button" class="toggle-btn" onclick="togglePassword('password_confirmation', 'password-confirm-eye')">
                        <i class="fas fa-eye" id="password-confirm-eye"></i>
                    </button>
                </div>
                
                <!-- Password Match Indicator -->
                <div class="match-indicator" id="match-indicator" style="display: none;">
                    <span id="match-icon"></span>
                    <span id="match-text"></span>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-shield-alt"></i>
                Update Password
            </button>
        </form>

        <div class="card-footer">
            <a href="{{ route('users.show', auth()->user()) }}">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const container = document.getElementById('strength-container');
    const fill = document.getElementById('strength-fill');
    const text = document.getElementById('strength-text');
    
    if (password.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    const levels = [
        { label: 'Very Weak', color: 'var(--accent)' },
        { label: 'Weak', color: '#f97316' },
        { label: 'Medium', color: 'var(--warning)' },
        { label: 'Strong', color: 'var(--success)' },
        { label: 'Very Strong', color: '#10b981' }
    ];
    
    const level = levels[Math.min(strength, 4)];
    const percentage = (strength / 5) * 100;
    
    fill.style.width = percentage + '%';
    fill.style.backgroundColor = level.color;
    text.textContent = level.label;
    text.style.color = level.color;
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const indicator = document.getElementById('match-indicator');
    const icon = document.getElementById('match-icon');
    const text = document.getElementById('match-text');
    
    if (confirmation.length === 0) {
        indicator.style.display = 'none';
        return;
    }
    
    indicator.style.display = 'flex';
    
    if (password === confirmation) {
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        text.textContent = 'Passwords match';
        icon.style.color = 'var(--success)';
        text.style.color = 'var(--success)';
    } else {
        icon.innerHTML = '<i class="fas fa-times-circle"></i>';
        text.textContent = 'Passwords do not match';
        icon.style.color = 'var(--accent)';
        text.style.color = 'var(--accent)';
    }
}
</script>

<style>
.password-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 40px 20px;
}

.password-card {
    width: 100%;
    max-width: 420px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
}

.card-header {
    text-align: center;
    margin-bottom: 28px;
}

.card-icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.card-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 8px 0;
}

.card-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.success-alert {
    text-align: center;
    padding: 12px 16px;
    background: rgba(34, 197, 94, 0.1);
    color: var(--success);
    border-radius: var(--radius);
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.form-field {
    margin-bottom: 20px;
}

.form-field label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group input {
    width: 100%;
    padding: 12px 44px 12px 16px;
    font-size: 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text);
    transition: all var(--transition);
}

.input-group input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.toggle-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    transition: color var(--transition);
}

.toggle-btn:hover {
    color: var(--text);
}

.field-hint {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
}

.field-error {
    display: block;
    font-size: 12px;
    color: var(--accent);
    margin-top: 6px;
}

/* Password Strength */
.strength-container {
    margin-top: 10px;
}

.strength-bar {
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
}

.strength-fill {
    height: 100%;
    width: 0;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-text {
    display: block;
    font-size: 12px;
    margin-top: 4px;
    font-weight: 600;
}

/* Match Indicator */
.match-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    font-size: 12px;
    font-weight: 600;
}

.match-indicator i {
    font-size: 14px;
}

.submit-btn {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.card-footer {
    text-align: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.card-footer a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14px;
    transition: color var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.card-footer a:hover {
    color: var(--primary);
}

@media (max-width: 480px) {
    .password-page {
        padding: 20px 16px;
    }
    
    .password-card {
        padding: 24px 20px;
    }
    
    .card-title {
        font-size: 20px;
    }
}
</style>
@endsection