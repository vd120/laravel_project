/* Register Page JavaScript */

(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// password toggle (works for both fields)
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
            'weak': window.chatTranslations?.password_strength_weak || window.authTranslations?.password_strength_weak || 'Weak',
            'medium': window.chatTranslations?.password_strength_medium || window.authTranslations?.password_strength_medium || 'Medium',
            'strong': window.chatTranslations?.password_strength_strong || window.authTranslations?.password_strength_strong || 'Strong',
            'very-strong': window.chatTranslations?.password_strength_very_strong || window.authTranslations?.password_strength_very_strong || 'Very Strong'
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
        div.textContent = window.chatTranslations?.passwords_match || window.authTranslations?.passwords_match || 'Passwords match';
        div.className = 'field-status matching';
    } else {
        div.textContent = window.chatTranslations?.passwords_do_not_match || window.authTranslations?.passwords_do_not_match || 'Passwords do not match';
        div.className = 'field-status not-matching';
    }
}

(function() {
    const confirmInput = document.getElementById('password_confirmation');
    if (confirmInput) {
        confirmInput.addEventListener('input', checkMatch);
    }
})();

// Username availability check
(function() {
    const usernameInput = document.getElementById('username');
    const statusDiv = document.getElementById('username-status');
    let checkTimeout;

    if (!usernameInput || !statusDiv) return;

    usernameInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        const val = this.value.trim();

        if (val.length < 3) {
            statusDiv.textContent = '';
            statusDiv.className = 'field-status';
            return;
        }

        statusDiv.textContent = 'Checking...';
        statusDiv.className = 'field-status checking';

        checkTimeout = setTimeout(() => {
            fetch('/api/check-username?username=' + encodeURIComponent(val), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    statusDiv.textContent = window.chatTranslations?.username_available || window.authTranslations?.username_available || 'Username available';
                    statusDiv.className = 'field-status available';
                } else {
                    statusDiv.textContent = window.chatTranslations?.username_taken || window.authTranslations?.username_taken || 'Username taken';
                    statusDiv.className = 'field-status taken';
                }
            })
            .catch(() => {
                statusDiv.textContent = '';
                statusDiv.className = 'field-status';
            });
        }, 500);
    });
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
