/* Auth Forgot Password Functions */

(function() {
    'use strict';

    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    window.toggleTheme = function() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        if (!icon) return;
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        localStorage.setItem('theme', newTheme);
    };

    window.toggleLanguageDropdown = function() {
        const dropdown = document.getElementById('language-dropdown');
        const overlay = document.getElementById('language-overlay');
        const arrow = document.getElementById('lang-arrow');
        const toggle = document.querySelector('.language-toggle');

        if (!dropdown) return;

        const isVisible = dropdown.style.display === 'block';

        if (isVisible) {
            dropdown.style.display = 'none';
            if (overlay) overlay.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        } else {
            dropdown.style.display = 'block';
            if (overlay) overlay.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
        }
    };

    window.switchLanguage = function(locale) {
        const loading = document.getElementById('language-loading');
        if (loading) loading.style.display = 'flex';
        toggleLanguageDropdown();
        const currentPath = window.location.pathname + window.location.search;
        window.location.href = '/lang/' + locale + '?return=' + encodeURIComponent(currentPath);
    };

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
})();
