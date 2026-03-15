/* Verify Email Page JavaScript */

(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// userAlreadyVerified is set by Blade template

// Function to show verification form and hide send code section
window.showVerificationForm = function() {
    const sendCodeSection = document.getElementById('sendCodeSection');
    const verifyForm = document.getElementById('verifyForm');
    const resendSection = document.getElementById('resendSection');

    if (sendCodeSection) sendCodeSection.style.display = 'none';
    if (verifyForm) verifyForm.classList.add('active');
    if (resendSection) resendSection.classList.add('active');

    // Focus on first code input
    const firstInput = document.querySelector('.code-input');
    if (firstInput) {
        firstInput.focus();
    }

    // Start countdown
    startCountdown();
}

// Get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('input[name="_token"]')?.value ||
           '';
}

// Handle send code form submission with AJAX
(function() {
    const sendCodeForm = document.getElementById('sendCodeForm');
    if (!sendCodeForm) return;

    sendCodeForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (window.userAlreadyVerified) {
            showToast(window.verifyEmailTranslations?.accountAlreadyVerified || window.chatTranslations?.account_already_verified || window.authTranslations?.account_already_verified || 'Account already verified', 'info');
            return false;
        }

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        const sendingText = window.verifyEmailTranslations?.sending || 'Sending...';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + sendingText;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData,
                credentials: 'same-origin'
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                showVerificationForm();
                showToast(window.verifyEmailTranslations?.verificationCodeSent || window.chatTranslations?.verification_code_sent || window.authTranslations?.verification_code_sent || 'Verification code sent', 'success');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
            }

            // Check if response contains redirect
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (response.ok || response.status === 200) {
                if (data.message) {
                    showToast(data.message, 'success');
                } else {
                    showToast(window.verifyEmailTranslations?.verificationCodeSent || window.chatTranslations?.verification_code_sent || window.authTranslations?.verification_code_sent || 'Verification code sent', 'success');
                }
                showVerificationForm();
            } else {
                showToast(data.message || data.error || window.verifyEmailTranslations?.error || window.chatTranslations?.error || window.authTranslations?.error || 'Error', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            form.submit();
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
})();

// Handle resend form submission with AJAX
(function() {
    const resendForm = document.getElementById('resendForm');
    if (!resendForm) return;

    resendForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        const sendingText = window.verifyEmailTranslations?.sending || 'Sending...';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + sendingText;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData,
                credentials: 'same-origin'
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                showToast(window.verifyEmailTranslations?.verificationCodeSent || window.chatTranslations?.verification_code_sent || window.authTranslations?.verification_code_sent || 'Verification code sent', 'success');
                startCountdown();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
            }

            // Check if response contains redirect
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (response.ok || response.status === 200) {
                if (data.message) {
                    showToast(data.message, 'success');
                } else {
                    showToast(window.verifyEmailTranslations?.verificationCodeSent || window.chatTranslations?.verification_code_sent || window.authTranslations?.verification_code_sent || 'Verification code sent', 'success');
                }
                startCountdown();
            } else {
                showToast(data.message || data.error || window.verifyEmailTranslations?.error || window.chatTranslations?.error || window.authTranslations?.error || 'Error', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            form.submit();
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
})();

// Auto-focus for code inputs
(function() {
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');

    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');

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

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);

            pastedData.split('').forEach((char, i) => {
                if (inputs[i]) {
                    inputs[i].value = char;
                }
            });

            if (pastedData.length > 0) {
                inputs[Math.min(pastedData.length, inputs.length - 1)].focus();
            }
        });
    });

    // Submit form with combined code
    const verifyForm = document.getElementById('verifyForm');
    if (verifyForm) {
        verifyForm.addEventListener('submit', (e) => {
            const inputValues = Array.from(inputs).map(input => input.value);
            const code = inputValues.join('');
            const hasEmptyInput = inputValues.some(val => val === '' || val === undefined || val === null);

            if (hasEmptyInput || code.length < 6) {
                e.preventDefault();
                showToast(window.verifyEmailTranslations?.enter6DigitCode || window.chatTranslations?.enter_6_digit_code || window.authTranslations?.enter_6_digit_code || 'Enter 6 digit code', 'error');
                return false;
            }

            if (!/^\d{6}$/.test(code)) {
                e.preventDefault();
                showToast(window.verifyEmailTranslations?.codeMustBeNumbers || window.chatTranslations?.code_must_be_numbers || window.authTranslations?.code_must_be_numbers || 'Code must be numbers', 'error');
                return false;
            }

            if (fullCodeInput) {
                fullCodeInput.value = code;
            }
        });
    }
})();

// Countdown timer for resend
let countdownInterval = null;
function startCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }

    let seconds = 60;
    const countdownEl = document.getElementById('countdown');
    const resendBtn = document.getElementById('resendBtn');

    if (!countdownEl || !resendBtn) return;

    countdownEl.textContent = seconds;
    resendBtn.disabled = true;

    countdownInterval = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(countdownInterval);
            resendBtn.disabled = false;
        }
    }, 1000);
}

// Toast notification
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + message + '</span>';

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    localStorage.setItem('theme', newTheme);
}

// Set initial theme icon based on saved theme
(function() {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    const currentTheme = html.getAttribute('data-theme');
    if (currentTheme === 'light') {
        icon.className = 'fas fa-moon';
    } else {
        icon.className = 'fas fa-sun';
    }
})();

// Language switcher functions
function toggleLanguageDropdown() {
    const dropdown = document.getElementById('language-dropdown');
    const overlay = document.getElementById('language-overlay');
    const arrow = document.getElementById('lang-arrow');
    const toggle = document.querySelector('.language-toggle');

    const isVisible = dropdown && dropdown.style.display === 'block';

    if (isVisible) {
        dropdown.style.display = 'none';
        overlay.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
        toggle.setAttribute('aria-expanded', 'false');
    } else {
        dropdown.style.display = 'block';
        overlay.style.display = 'block';
        if (arrow) arrow.style.transform = 'rotate(180deg)';
        toggle.setAttribute('aria-expanded', 'true');
    }
}

function switchLanguage(locale) {
    const loading = document.getElementById('language-loading');
    if (loading) {
        loading.style.display = 'flex';
    }
    toggleLanguageDropdown();
    const currentPath = window.location.pathname + window.location.search;
    window.location.href = '/lang/' + locale + '?return=' + encodeURIComponent(currentPath);
}

document.addEventListener('click', function(event) {
    const switcher = document.querySelector('.language-switcher');
    if (switcher && !switcher.contains(event.target)) {
        const dropdown = document.getElementById('language-dropdown');
        const overlay = document.getElementById('language-overlay');
        const arrow = document.getElementById('lang-arrow');
        const toggle = document.querySelector('.language-toggle');

        if (dropdown) dropdown.style.display = 'none';
        if (overlay) overlay.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }
});

// Theme-aware styling for language switcher
(function() {
    const checkTheme = () => {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        const toggle = document.querySelector('.language-toggle');
        const dropdown = document.getElementById('language-dropdown');

        if (toggle) {
            toggle.style.borderColor = isLight ? 'rgba(0, 0, 0, 0.2)' : 'rgba(255, 255, 255, 0.2)';
            toggle.style.color = isLight ? '#111111' : '#ffffff';
        }

        if (dropdown) {
            dropdown.style.background = isLight ? 'rgba(255, 255, 255, 0.98)' : 'rgba(22, 22, 22, 0.98)';
            dropdown.style.borderColor = isLight ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';
            dropdown.style.boxShadow = isLight ? '0 10px 40px rgba(0, 0, 0, 0.15)' : '0 10px 40px rgba(0, 0, 0, 0.4)';
        }
    };

    checkTheme();

    const observer = new MutationObserver(checkTheme);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
})();
