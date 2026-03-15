/* Reset Password Page JavaScript */

(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// Toast notification function
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

// password toggle (works for all fields)
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    // trigger match check in case confirmation visibility changed
    if (inputId === 'password' || inputId === 'password_confirmation') checkMatch();
}

// password strength
(function() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        const fill = document.getElementById('strength-fill');
        const lbl = document.getElementById('strength-label');

        if (!val.length) {
            fill.className = 'strength-fill';
            lbl.className = 'strength-label';
            lbl.textContent = '';
            checkMatch();
            return;
        }

        let score = 0;
        if (val.length >= 8) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const level = score <= 2 ? 'weak' : score === 3 ? 'medium' : score === 4 ? 'strong' : 'very-strong';
        const labelMap = {
            'weak': 'Weak',
            'medium': 'Medium',
            'strong': 'Strong',
            'very-strong': 'Very Strong'
        };

        fill.className = 'strength-fill ' + level;
        lbl.className = 'strength-label ' + level;
        lbl.textContent = labelMap[level];
        checkMatch();
    });
})();

// password match
function checkMatch() {
    const pass = document.getElementById('password');
    const conf = document.getElementById('password_confirmation');
    const div = document.getElementById('match-status');

    if (!pass || !conf || !div) return;

    if (!conf.value.length) { div.textContent = ''; div.className = 'field-status'; return; }

    if (pass.value === conf.value) {
        div.textContent = 'Passwords match';
        div.className = 'field-status matching';
    } else {
        div.textContent = 'Passwords do not match';
        div.className = 'field-status not-matching';
    }
}

(function() {
    const confirmInput = document.getElementById('password_confirmation');
    if (confirmInput) {
        confirmInput.addEventListener('input', checkMatch);
    }
})();

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

// Handle form submission with AJAX
(function() {
    const form = document.querySelector('form[method="POST"]');
    if (!form) return;

    // Translations
    const translations = {
        en: {
            passwordsMismatch: 'Passwords do not match',
            weakPassword: 'Password is too weak',
            processing: 'Updating password...'
        },
        ar: {
            passwordsMismatch: 'كلمتا السر غير متطابقتين',
            weakPassword: 'كلمة السر ضعيفة جداً',
            processing: 'جاري تغيير كلمة السر...'
        }
    };

    function getTranslation(key) {
        const lang = document.documentElement.lang || 'en';
        return translations[lang]?.[key] || translations.en[key] || key;
    }

    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password')?.value || '';
        const confirmation = document.getElementById('password_confirmation')?.value || '';

        // Validate passwords match
        if (password !== confirmation) {
            e.preventDefault();
            showToast(getTranslation('passwordsMismatch') || window.authTranslations?.passwords_mismatch || 'Passwords mismatch', 'error');
            return false;
        }

        // Validate password strength
        if (password.length < 8) {
            e.preventDefault();
            showToast(getTranslation('weakPassword') || window.authTranslations?.weak_password || 'Weak password', 'error');
            return false;
        }

        // Show processing state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + getTranslation('processing');
        
        // Let form submit normally to redirect to login with status
    });
})();
