/**
 * Laravel Echo Configuration for Real-Time Features
 * Compatible with Pusher and Laravel Broadcasting
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configure Pusher
window.Pusher = Pusher;

// Pusher configuration
Pusher.logToConsole = process.env.NODE_ENV === 'development';

// Initialize Laravel Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusherapp.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],

    // Authentication
    auth: {
        headers: {
            Authorization: `Bearer ${document.querySelector('meta[name="api-token"]')?.getAttribute('content')}`,
        },
    },

    // CSRF protection for broadcasting auth
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),

    // Disable stats for production
    disableStats: true,

    // Connection timeout
    connectTimeout: 10000,

    // Reconnection settings
    reconnect: {
        maxAttempts: 6,
        delay: 1000,
        delayMultiplier: 1.5,
    },
});

// Connection event handlers
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('🔗 Real-time connection established');
    document.dispatchEvent(new CustomEvent('echo:connected'));
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('❌ Real-time connection lost');
    document.dispatchEvent(new CustomEvent('echo:disconnected'));
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('🔴 Real-time connection error:', error);
    document.dispatchEvent(new CustomEvent('echo:error', { detail: error }));
});

// Export for global access
export default window.Echo;
