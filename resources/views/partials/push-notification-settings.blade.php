@php
$currentLocale = app()->getLocale();
@endphp

{{-- Push Notification Settings Modal --}}
<div id="pushSettingsModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) hidePushSettings()">
    <div class="modal-content push-settings-modal">
        <div class="modal-header">
            <h3>{{ __('notifications.enable_push') }}</h3>
            <button class="modal-close" onclick="hidePushSettings()">×</button>
        </div>
        <div class="modal-body">
            <div class="push-settings-content">
                <div class="push-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <p class="push-description">{{ __('notifications.enable_push_desc') }}</p>

                <div id="pushPermissionStatus" class="permission-status"></div>

                <div class="push-settings-form" id="pushSettingsForm" style="display: none;">
                    <h4>{{ __('notifications.settings') }}</h4>

                    <div class="setting-item">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pushLikes" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">{{ __('notifications.liked_your_post') }}</span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pushComments" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">{{ __('notifications.commented_on_your_post') }}</span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pushFollows" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">{{ __('notifications.new_follower') }}</span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pushMessages" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">{{ __('notifications.sent_you_message') }}</span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pushMentions" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">{{ __('notifications.mentioned_you') }}</span>
                        </label>
                    </div>

                    <div class="push-actions">
                        <button class="btn btn-primary" onclick="savePushSettings()">
                            <i class="fas fa-save"></i> {{ __('messages.save') }}
                        </button>
                        <button class="btn btn-danger" onclick="disablePushNotifications()">
                            <i class="fas fa-bell-slash"></i> Disable Notifications
                        </button>
                    </div>
                </div>

                <div id="pushNotEnabled" class="push-not-enabled">
                    <button class="btn btn-primary btn-lg" onclick="enablePushNotifications()">
                        <i class="fas fa-bell"></i> {{ __('notifications.enable_push') }}
                    </button>
                </div>

                <div id="pushEnabled" class="push-enabled" style="display: none;">
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ __('messages.push_subscription_created') }}</span>
                    </div>
                    <button class="btn btn-secondary" onclick="showPushSettingsForm()">
                        <i class="fas fa-cog"></i> {{ __('notifications.settings') }}
                    </button>
                    <button class="btn btn-test" onclick="testPushNotification()">
                        <i class="fas fa-flask"></i> {{ __('messages.test_notification_sent') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Push Notification Modal Styles */
#pushSettingsModal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

#pushSettingsModal.modal-visible {
    display: flex;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.push-settings-modal {
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 16px;
    background: var(--bg, #1a1a2e);
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    position: relative;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.push-settings-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    position: sticky;
    top: 0;
    background: var(--bg, #1a1a2e);
    z-index: 1;
}

.push-settings-modal .modal-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text, #fff);
    margin: 0;
}

.push-settings-modal .modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: var(--text-muted, #888);
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.push-settings-modal .modal-close:hover {
    background: rgba(255,255,255,0.1);
    color: var(--text, #fff);
}

.push-settings-modal .modal-body {
    padding: 24px;
}

.push-settings-content {
    text-align: center;
}

.push-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--primary, #6366f1), #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
}

.push-description {
    color: var(--text-muted, #888);
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 24px;
}

.permission-status {
    margin-bottom: 20px;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    display: none;
}

.permission-status.info {
    display: block;
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.permission-status.error {
    display: block;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.push-settings-form h4 {
    text-align: left;
    font-size: 16px;
    font-weight: 600;
    color: var(--text, #fff);
    margin: 0 0 16px;
}

.setting-item {
    text-align: left;
    margin-bottom: 12px;
}

.toggle-switch {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.toggle-switch input {
    display: none;
}

.toggle-slider {
    width: 44px;
    height: 24px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    position: relative;
    transition: all 0.3s;
    flex-shrink: 0;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: all 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--primary, #6366f1);
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.toggle-label {
    color: var(--text, #fff);
    font-size: 14px;
    flex: 1;
}

.push-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    justify-content: center;
}

.push-actions .btn {
    flex: 1;
    max-width: 200px;
}

.push-not-enabled, .push-enabled {
    margin-top: 20px;
}

.push-not-enabled .btn-lg {
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
}

.success-message {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 15px;
    font-weight: 500;
}

.success-message i {
    font-size: 20px;
}

.btn-test {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
    border: 1px solid rgba(139, 92, 246, 0.3);
}

.btn-test:hover {
    background: rgba(139, 92, 246, 0.2);
}

/* RTL Support */
[dir="rtl"] .setting-item {
    text-align: right;
}

[dir="rtl"] .toggle-switch {
    flex-direction: row-reverse;
}

[dir="rtl"] .toggle-slider::before {
    left: auto;
    right: 3px;
}

[dir="rtl"] .toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(-20px);
}

/* Mobile Responsive */
@media (max-width: 640px) {
    .push-settings-modal {
        margin: 20px;
        max-width: calc(100% - 40px);
    }

    .push-actions {
        flex-direction: column;
    }

    .push-actions .btn {
        max-width: 100%;
    }
}
</style>

<script>
// Push Notification UI Functions
function showPushSettings() {
    const modal = document.getElementById('pushSettingsModal');
    modal.style.display = 'flex';
    // Trigger animation
    setTimeout(() => modal.classList.add('modal-visible'), 10);
    checkPushStatus();
}

function hidePushSettings() {
    const modal = document.getElementById('pushSettingsModal');
    modal.classList.remove('modal-visible');
    setTimeout(() => modal.style.display = 'none', 200);
}

async function checkPushStatus() {
    const statusDiv = document.getElementById('pushPermissionStatus');
    const form = document.getElementById('pushSettingsForm');
    const notEnabled = document.getElementById('pushNotEnabled');
    const enabled = document.getElementById('pushEnabled');

    if (!window.pushManager) {
        statusDiv.className = 'permission-status error';
        statusDiv.textContent = '{{ __('messages.notSupported') }}';
        return;
    }

    const isSupported = window.pushManager.isSupported;
    const isEnabled = window.pushManager.isEnabled();

    if (!isSupported) {
        statusDiv.className = 'permission-status error';
        statusDiv.textContent = window.pushManager.t('notSupported');
        notEnabled.style.display = 'none';
        return;
    }

    if (isEnabled) {
        form.style.display = 'block';
        notEnabled.style.display = 'none';
        enabled.style.display = 'block';

        // Load current settings
        try {
            const response = await fetch('/api/push/settings', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const data = await response.json();

            if (data.success && data.settings) {
                document.getElementById('pushLikes').checked = data.settings.likes !== false;
                document.getElementById('pushComments').checked = data.settings.comments !== false;
                document.getElementById('pushFollows').checked = data.settings.follows !== false;
                document.getElementById('pushMessages').checked = data.settings.messages !== false;
                document.getElementById('pushMentions').checked = data.settings.mentions !== false;
            }
        } catch (error) {
            console.error('[Push] Error loading settings:', error);
        }
    } else {
        form.style.display = 'none';
        notEnabled.style.display = 'block';
        enabled.style.display = 'none';
    }
}

async function enablePushNotifications() {
    const result = await window.pushManager.requestPermission();
    if (result) {
        checkPushStatus();
    }
}

async function savePushSettings() {
    const settings = {
        likes: document.getElementById('pushLikes').checked,
        comments: document.getElementById('pushComments').checked,
        follows: document.getElementById('pushFollows').checked,
        messages: document.getElementById('pushMessages').checked,
        mentions: document.getElementById('pushMentions').checked,
    };

    try {
        const response = await window.pushManager.updateSettings(settings);
        if (response.success) {
            window.pushManager.showToast('{{ __('messages.push_settings_updated') }}', 'success');
            hidePushSettings();
        }
    } catch (error) {
        window.pushManager.showToast(error.message || '{{ __('messages.error') }}', 'error');
    }
}

async function disablePushNotifications() {
    if (!confirm('Disable push notifications?')) {
        return;
    }

    try {
        await window.pushManager.unsubscribe();
        checkPushStatus();
        // Toast already shown by unsubscribe()
    } catch (error) {
        window.pushManager.showToast(error.message || 'Error disabling notifications', 'error');
    }
}

function showPushSettingsForm() {
    document.getElementById('pushSettingsForm').style.display = 'block';
    document.getElementById('pushEnabled').style.display = 'none';
}

async function testPushNotification() {
    try {
        const response = await fetch('/api/push/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (data.success) {
            window.pushManager.showToast(data.message, 'success');
        } else {
            window.pushManager.showToast(data.message || '{{ __('messages.error') }}', 'error');
        }
    } catch (error) {
        window.pushManager.showToast('{{ __('messages.error') }}', 'error');
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hidePushSettings();
    }
});
</script>
