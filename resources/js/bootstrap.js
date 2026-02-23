import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Laravel Echo for real-time features
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});

// Connection event handlers
if (window.Echo && window.Echo.connector) {
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('🔗 Real-time connection established');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('❌ Real-time connection lost');
    });

    window.Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('🔴 Real-time connection error:', error);
    });
}
