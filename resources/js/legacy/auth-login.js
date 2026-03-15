/* Auth Login - External Config and Functions */

(function() {
    'use strict';

    // Session messages translations
    const sessionMessages = {
        en: {
            'passwords.reset': 'Password updated successfully! You can now log in.',
            'password_reset': 'Password updated successfully! You can now log in.',
            'account_suspended': 'Your account has been suspended.',
            'concurrent_login': 'Security alert: Concurrent login detected.',
            'account_deleted': 'Your account has been deleted.',
            'logged_out': 'You have been logged out.'
        },
        ar: {
            'passwords.reset': 'كلمة السر اتغيرت بنجاح! يمكنك تسجيل الدخول الآن.',
            'password_reset': 'كلمة السر اتغيرت بنجاح! يمكنك تسجيل الدخول الآن.',
            'account_suspended': 'حسابك تم تعليقه.',
            'concurrent_login': 'تنبيه أمني: تم اكتشاف دخول متزامن.',
            'account_deleted': 'حسابك تم حذفه.',
            'logged_out': 'تم تسجيل خروجك.'
        }
    };

    function getSessionMessage(key, lang) {
        if (key && sessionMessages[lang]?.[key]) return sessionMessages[lang][key];
        if (key && sessionMessages.en[key]) return sessionMessages.en[key];

        if (!key) return '';
        const keyLower = key.toLowerCase();

        if (keyLower.includes('كلمة') || keyLower.includes('كلمة السر') || keyLower.includes('اتغيرت')) {
            return sessionMessages[lang]?.['passwords.reset'] || sessionMessages.en['passwords.reset'];
        }
        if (keyLower.includes('password') && keyLower.includes('reset')) {
            return sessionMessages[lang]?.['passwords.reset'] || sessionMessages.en['passwords.reset'];
        }
        if (keyLower.includes('password') && keyLower.includes('update')) {
            return sessionMessages[lang]?.['passwords.reset'] || sessionMessages.en['passwords.reset'];
        }
        if (keyLower.includes('suspended')) {
            return sessionMessages[lang]?.['account_suspended'] || sessionMessages.en['account_suspended'];
        }
        if (keyLower.includes('concurrent')) {
            return sessionMessages[lang]?.['concurrent_login'] || sessionMessages.en['concurrent_login'];
        }
        if (keyLower.includes('deleted')) {
            return sessionMessages[lang]?.['account_deleted'] || sessionMessages.en['account_deleted'];
        }
        if (keyLower.includes('logged out') || keyLower.includes('logout')) {
            return sessionMessages[lang]?.['logged_out'] || sessionMessages.en['logged_out'];
        }

        return key;
    }

    // Theme initialization
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    window.togglePassword = function() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (!input || !icon) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    };

    window.showSessionToast = function(type, message) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    };

    // Read config from data attributes and show messages
    document.addEventListener('DOMContentLoaded', function() {
        const configEl = document.getElementById('login-config');
        const userLang = document.documentElement.lang || 'en';
        
        if (configEl) {
            const status = configEl.getAttribute('data-status');
            const error = configEl.getAttribute('data-error');
            const concurrent = configEl.getAttribute('data-concurrent');
            const deleted = configEl.getAttribute('data-deleted');
            const suspended = configEl.getAttribute('data-suspended');

            if (status) {
                const message = getSessionMessage(status, userLang);
                if (message) showSessionToast('success', message);
            }
            if (error) {
                const errorMessage = getSessionMessage(error, userLang);
                if (errorMessage) showSessionToast('error', errorMessage);
            }
            if (concurrent) {
                const concurrentMsg = getSessionMessage('concurrent_login', userLang);
                showSessionToast('error', concurrentMsg);
            }
            if (deleted) {
                const deletedMsg = getSessionMessage('account_deleted', userLang);
                showSessionToast('error', deletedMsg);
            }
            if (suspended) {
                const suspendedMsg = getSessionMessage('account_suspended', userLang);
                showSessionToast('error', suspendedMsg);
            }
        }
    });
})();
