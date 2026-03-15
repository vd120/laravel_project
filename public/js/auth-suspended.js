/* Auth Suspended Page JavaScript */

(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
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
