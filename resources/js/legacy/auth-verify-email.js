/* Auth Verify Email Functions */

(function() {
    'use strict';

    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    window.showVerificationForm = function() {
        const sendCodeSection = document.getElementById('sendCodeSection');
        const verifyForm = document.getElementById('verifyForm');
        const resendSection = document.getElementById('resendSection');

        if (sendCodeSection) sendCodeSection.style.display = 'none';
        if (verifyForm) verifyForm.classList.add('active');
        if (resendSection) resendSection.classList.add('active');

        const firstInput = document.querySelector('.code-input');
        if (firstInput) firstInput.focus();

        startCountdown();
    };

    window.getCsrfToken = function() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
               document.querySelector('input[name="_token"]')?.value || '';
    };

    window.showToast = function(message, type = 'info') {
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
    };

    let countdownInterval = null;
    window.startCountdown = function() {
        if (countdownInterval) clearInterval(countdownInterval);

        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');

        if (!countdownEl || !resendBtn) return;

        countdownEl.textContent = seconds;
        resendBtn.disabled = true;

        countdownInterval = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
            }
        }, 1000);
    };

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
})();
