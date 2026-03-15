/* UI Utilities - Toast and Common Functions */

(function() {
    'use strict';

    // Toast notification
    window.showToast = function(message, type = 'info', duration = 3000) {
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
        }, duration);
    };

    // Theme toggle
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

    // Get CSRF token
    window.getCsrfToken = function() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    };
})();
